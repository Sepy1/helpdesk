<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\AppSetting;
use App\Models\AiChatMessage;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AssistantController extends Controller
{
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['messages' => []], 200);
        }

        $messages = AiChatMessage::query()
            ->where('user_id', $user->id)
            ->orderBy('created_at')
            ->orderBy('id')
            ->limit(100)
            ->get(['role', 'content', 'source', 'created_at'])
            ->map(fn (AiChatMessage $msg) => [
                'role' => $msg->role,
                'text' => $msg->content,
                'source' => $msg->source,
                'created_at' => optional($msg->created_at)->toDateTimeString(),
            ])
            ->values();

        return response()->json(['messages' => $messages], 200);
    }

    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1500'],
            'history' => ['nullable', 'array', 'max:12'],
            'history.*.role' => ['required_with:history', 'in:user,assistant'],
            'history.*.text' => ['required_with:history', 'string', 'max:1500'],
        ]);

        $apiKey = config('services.openai.api_key');
        $model = config('services.openai.model', 'gpt-4o-mini');
        $maxOutputTokens = (int) config('services.openai.max_output_tokens', 4000);
        $user = $request->user();
        $messageText = (string) $validated['message'];
        $requestedBranch = $this->extractBranchFilter($messageText);
        $requestedPeriod = $this->extractPeriodFilter($messageText);
        $isBranchPeriodQuery = ! empty($requestedBranch) || ! empty($requestedPeriod);

        if (! AppSetting::getBool('ai_chat_enabled', true)) {
            return $this->chatResponse($user, $messageText, 'AI chat sedang dinonaktifkan oleh administrator.', 'disabled');
        }

        if ($user && $user->ai_chat_enabled === false) {
            return $this->chatResponse($user, $messageText, 'Akses AI chat untuk akun Anda sedang dinonaktifkan oleh administrator.', 'disabled');
        }

        if (empty($apiKey)) {
            return $this->chatResponse($user, $messageText, 'API key AI belum terpasang. Hubungi admin untuk konfigurasi.', 'fallback');
        }

        $role = $user?->role ?? 'USER';
        $name = $user?->name ?? 'User';
        $defaultAnalysisLimit = (int) config('services.openai.analysis_ticket_limit', 1000);
        $defaultBranchLimit = (int) config('services.openai.branch_query_ticket_limit', 1000);
        $ticketContext = $this->buildTicketContext($user, $messageText, $defaultAnalysisLimit, $defaultBranchLimit, ! $isBranchPeriodQuery);

        $messages = [
            [
                'role' => 'system',
                'content' => "Anda adalah AI Assistant untuk aplikasi Helpdesk internal. "
                    . "Fokus memberi panduan operasional penggunaan aplikasi (buat tiket, lihat tiket, update status, komentar, statistik, parameter, profil). "
                    . "Jawab singkat, jelas, dalam Bahasa Indonesia. Jangan membuat data fiktif dan jangan membahas hal di luar aplikasi. "
                    . "Jika pertanyaan terkait data tiket, gunakan hanya DATA_TIKET_REALTIME yang diberikan. "
                    . "Untuk analisa cabang/kantor, gunakan field kode_cabang_pembuat dan nama_cabang_pembuat dari tiket. "
                    . "Jika tersedia branch_period_query pada data, prioritaskan itu untuk menjawab pertanyaan terkait cabang/periode. "
                    . "Gaya bahasa harus rapi, profesional, dan natural (bukan sekadar menyalin field mentah). "
                    . "Saat menjelaskan tiket spesifik, tulis narasi ringkas 1-2 paragraf lalu ringkasan poin seperlunya; hindari markdown berlebihan. "
                    . "Jika menuliskan daftar, gunakan poin per baris diawali '- ' (dash), bukan nomor 1/2/3 dalam satu paragraf.",
            ],
            [
                'role' => 'system',
                'content' => "Konteks pengguna: nama {$name}, role {$role}. Sesuaikan jawaban dengan role ini.",
            ],
            [
                'role' => 'system',
                'content' => 'DATA_TIKET_REALTIME: ' . json_encode($ticketContext, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ],
        ];

        foreach (array_slice($validated['history'] ?? [], -8) as $item) {
            $messages[] = [
                'role' => $item['role'],
                'content' => $item['text'],
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $validated['message'],
        ];

        try {
            $response = $this->requestOpenAi($apiKey, $model, $messages, $maxOutputTokens);

            // Retry once with compact context when production data is too large.
            if ($this->isContextTooLargeError($response)) {
                $compactAnalysisLimit = max(80, min(150, $defaultAnalysisLimit));
                $compactBranchLimit = max(80, min(150, $defaultBranchLimit));
                $compactContext = $this->buildTicketContext($user, $messageText, $compactAnalysisLimit, $compactBranchLimit, false);

                $messages[2]['content'] = 'DATA_TIKET_REALTIME: ' . json_encode($compactContext, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $response = $this->requestOpenAi($apiKey, $model, $messages, $maxOutputTokens);
            }

            if (! $response->successful()) {
                Log::warning('Assistant chat failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                if ($isBranchPeriodQuery) {
                    return $this->chatResponse(
                        $user,
                        $messageText,
                        $this->buildBranchPeriodLocalReply($ticketContext),
                        'local_fallback'
                    );
                }

                return $this->chatResponse($user, $messageText, 'AI sedang tidak tersedia. Coba beberapa saat lagi.', 'fallback');
            }

            $reply = trim((string) data_get($response->json(), 'choices.0.message.content', ''));
            $reply = $this->normalizeReplyFormatting($reply);

            if ($reply === '') {
                return $this->chatResponse($user, $messageText, 'Saya belum bisa menjawab saat ini. Coba pertanyaan lain.', 'fallback');
            }

            return $this->chatResponse($user, $messageText, $reply, 'ai');
        } catch (\Throwable $e) {
            Log::warning('Assistant chat exception', [
                'error' => $e->getMessage(),
            ]);

            if ($isBranchPeriodQuery) {
                return $this->chatResponse(
                    $user,
                    $messageText,
                    $this->buildBranchPeriodLocalReply($ticketContext),
                    'local_fallback'
                );
            }

            return $this->chatResponse($user, $messageText, 'Terjadi gangguan saat menghubungi AI. Coba lagi sebentar.', 'fallback');
        }
    }

    private function chatResponse($user, string $userMessage, string $reply, string $source): JsonResponse
    {
        if ($user) {
            $this->persistChatTurn((int) $user->id, $userMessage, $reply, $source);
        }

        return response()->json([
            'reply' => $reply,
            'source' => $source,
        ], 200);
    }

    private function persistChatTurn(int $userId, string $userMessage, string $reply, string $source): void
    {
        try {
            AiChatMessage::create([
                'user_id' => $userId,
                'role' => 'user',
                'content' => $userMessage,
            ]);

            AiChatMessage::create([
                'user_id' => $userId,
                'role' => 'assistant',
                'content' => $reply,
                'source' => $source,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to persist AI chat messages', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function requestOpenAi(string $apiKey, string $model, array $messages, int $maxOutputTokens)
    {
        return Http::withToken($apiKey)
            ->timeout(45)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'temperature' => 0.3,
                'max_tokens' => max(500, $maxOutputTokens),
                'messages' => $messages,
            ]);
    }

    private function isContextTooLargeError($response): bool
    {
        if (! $response || $response->status() !== 400) {
            return false;
        }

        $body = strtolower((string) $response->body());
        return str_contains($body, 'maximum context length')
            || str_contains($body, 'context_length_exceeded')
            || str_contains($body, 'too many tokens');
    }

    private function buildTicketContext($user, string $message, int $analysisLimit = 1000, int $branchQueryLimit = 1000, bool $includeFullDataset = true): array
    {
        $baseQuery = $this->ticketQueryForUser($user);
        $branchFilter = $this->extractBranchFilter($message);
        $periodFilter = $this->extractPeriodFilter($message);

        $statusCounts = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'status' => (string) ($row->status ?? '-'),
                'total' => (int) ($row->total ?? 0),
            ])->values();

        $categoryCounts = (clone $baseQuery)
            ->selectRaw('kategori, COUNT(*) as total')
            ->whereNotNull('kategori')
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn ($row) => [
                'kategori' => (string) ($row->kategori ?? '-'),
                'total' => (int) ($row->total ?? 0),
            ])->values();

        $recentTickets = $this->formatTickets(
            (clone $baseQuery)
                ->with(['user:id,name,kode_kantor', 'user.kodeKantor:kode,nama_kantor', 'it:id,name', 'vendor:id,name', 'category:id,name', 'subcategory:id,name', 'rootCauseDetail:id,label'])
                ->latest('updated_at')
                ->limit(6)
                ->get()
        );

        $ticketNumbers = $this->extractTicketNumbers($message);
        $matchedTickets = collect();
        if (! empty($ticketNumbers)) {
            $matchedTickets = $this->formatTickets(
                (clone $baseQuery)
                    ->with(['user:id,name,kode_kantor', 'user.kodeKantor:kode,nama_kantor', 'it:id,name', 'vendor:id,name', 'category:id,name', 'subcategory:id,name', 'rootCauseDetail:id,label'])
                    ->where(function ($q) use ($ticketNumbers) {
                        foreach ($ticketNumbers as $number) {
                            $q->orWhere('nomor_tiket', 'like', '%' . $number . '%');
                        }
                    })
                    ->latest('updated_at')
                    ->limit(5)
                    ->get()
            );
        }

        $fullTickets = collect();
        if ($includeFullDataset) {
            $fullTickets = $this->formatTickets(
                (clone $baseQuery)
                    ->with(['user:id,name,kode_kantor', 'user.kodeKantor:kode,nama_kantor', 'it:id,name', 'vendor:id,name', 'category:id,name', 'subcategory:id,name', 'rootCauseDetail:id,label'])
                    ->withCount('comments')
                    ->latest('updated_at')
                    ->limit(max(100, $analysisLimit))
                    ->get()
            );
        }

        $totalScopedTickets = (clone $baseQuery)->count();
        $branchPeriodMatches = collect();

        if (! empty($branchFilter) || ! empty($periodFilter)) {
            $branchPeriodQuery = (clone $baseQuery)
                ->with(['user:id,name,kode_kantor', 'user.kodeKantor:kode,nama_kantor', 'it:id,name', 'vendor:id,name', 'category:id,name', 'subcategory:id,name', 'rootCauseDetail:id,label'])
                ->withCount('comments');

            if (! empty($branchFilter['kode'])) {
                $kode = (string) $branchFilter['kode'];
                $branchPeriodQuery->where(function ($outer) use ($kode) {
                    $outer->whereHas('user', function ($q) use ($kode) {
                        $q->where('kode_kantor', $kode);
                    });

                    if ($this->ticketsHasCabangColumn()) {
                        $outer->orWhere('cabang', 'like', '%' . $kode . '%');
                    }
                });
            }

            if (! empty($branchFilter['nama'])) {
                $nama = (string) $branchFilter['nama'];
                $branchPeriodQuery->where(function ($outer) use ($nama) {
                    $outer->whereHas('user.kodeKantor', function ($q) use ($nama) {
                        $q->where('nama_kantor', 'like', '%' . $nama . '%');
                    });

                    // Fallback for environments where master kode_kantor is incomplete.
                    $outer->orWhereHas('user', function ($q) use ($nama) {
                        $q->where('kode_kantor', 'like', '%' . $nama . '%');
                    });

                    if ($this->ticketsHasCabangColumn()) {
                        $outer->orWhere('cabang', 'like', '%' . $nama . '%');
                    }
                });
            }

            if (! empty($periodFilter['start']) && ! empty($periodFilter['end'])) {
                $branchPeriodQuery->whereBetween('created_at', [$periodFilter['start'], $periodFilter['end']]);
            }

            $branchPeriodMatches = $this->formatTickets(
                $branchPeriodQuery
                    ->latest('created_at')
                    ->limit(max(100, $branchQueryLimit))
                    ->get()
            );
        }

        return [
            'generated_at' => now()->toDateTimeString(),
            'scope' => $this->scopeLabelForUser($user),
            'summary' => [
                'total_tickets' => $totalScopedTickets,
                'open_tickets' => (clone $baseQuery)->where('status', '!=', 'CLOSED')->count(),
                'tickets_last_30_days' => (clone $baseQuery)->where('created_at', '>=', Carbon::now()->subDays(30))->count(),
            ],
            'status_counts' => $statusCounts,
            'category_counts' => $categoryCounts,
            'recent_tickets' => $recentTickets,
            'all_tickets_for_analysis' => [
                'total_available' => $totalScopedTickets,
                'total_sent_to_ai' => $fullTickets->count(),
                'truncated' => $totalScopedTickets > $fullTickets->count(),
                'omitted_for_targeted_query' => ! $includeFullDataset,
                'items' => $fullTickets,
            ],
            'branch_period_query' => [
                'requested_branch' => $branchFilter,
                'requested_period' => [
                    'label' => $periodFilter['label'] ?? null,
                    'start' => isset($periodFilter['start']) ? $periodFilter['start']->toDateTimeString() : null,
                    'end' => isset($periodFilter['end']) ? $periodFilter['end']->toDateTimeString() : null,
                ],
                'matches_count' => $branchPeriodMatches->count(),
                'items' => $branchPeriodMatches,
            ],
            'matched_tickets_from_question' => $matchedTickets,
            'requested_ticket_numbers' => $ticketNumbers,
        ];
    }

    private function ticketQueryForUser($user)
    {
        $query = Ticket::query();
        $role = strtoupper((string) ($user?->role ?? ''));

        if ($role === 'VENDOR') {
            return $query->where('vendor_id', $user->id);
        }

        if ($role === 'IT') {
            return $query;
        }

        return $query->where('user_id', $user?->id ?? 0);
    }

    private function scopeLabelForUser($user): string
    {
        $role = strtoupper((string) ($user?->role ?? ''));

        return match ($role) {
            'IT' => 'Semua tiket',
            'VENDOR' => 'Tiket yang di-assign ke vendor login',
            default => 'Tiket yang dibuat oleh user login',
        };
    }

    private function extractTicketNumbers(string $message): array
    {
        preg_match_all('/(?:TCK|HD|TK)-?\d{4,}(?:-\d+)?/i', $message, $matches);
        $numbers = collect($matches[0] ?? [])
            ->map(fn ($value) => strtoupper(trim((string) $value)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $numbers;
    }

    private function formatTickets(Collection $tickets): Collection
    {
        return $tickets->map(function (Ticket $ticket) {
            return [
                'id' => (int) $ticket->id,
                'nomor_tiket' => (string) ($ticket->nomor_tiket ?? '-'),
                'status' => (string) ($ticket->status ?? '-'),
                'kategori' => (string) ($ticket->kategori ?? '-'),
                'kategori_master' => (string) ($ticket->category->name ?? '-'),
                'subkategori_master' => (string) ($ticket->subcategory->name ?? '-'),
                'deskripsi' => (string) ($ticket->deskripsi ?? '-'),
                'dibuat_oleh' => (string) ($ticket->user->name ?? '-'),
                'kode_cabang_pembuat' => (string) ($ticket->user->kode_kantor ?? '-'),
                'nama_cabang_pembuat' => (string) ($ticket->user?->kodeKantor?->nama_kantor ?? '-'),
                'cabang_tiket' => (string) ($ticket->getAttribute('cabang') ?? '-'),
                'it_handler' => (string) ($ticket->it->name ?? '-'),
                'vendor' => (string) ($ticket->vendor->name ?? '-'),
                'eskalasi' => (string) ($ticket->escalated ?? $ticket->eskalasi ?? '-'),
                'root_cause' => (string) ($ticket->root_cause ?? '-'),
                'detail_root_cause' => (string) ($ticket->rootCauseDetail->label ?? '-'),
                'progress_note' => (string) ($ticket->progress_note ?? '-'),
                'vendor_followup' => (string) ($ticket->vendor_followup ?? '-'),
                'closed_note' => (string) ($ticket->closed_note ?? '-'),
                'comments_count' => (int) ($ticket->comments_count ?? 0),
                'created_at' => optional($ticket->created_at)->toDateTimeString(),
                'taken_at' => optional($ticket->taken_at)->toDateTimeString(),
                'progress_at' => optional($ticket->progress_at)->toDateTimeString(),
                'vendor_followup_at' => optional($ticket->vendor_followup_at)->toDateTimeString(),
                'updated_at' => optional($ticket->updated_at)->toDateTimeString(),
                'closed_at' => optional($ticket->closed_at)->toDateTimeString(),
            ];
        })->values();
    }

    private function normalizeReplyFormatting(string $reply): string
    {
        if ($reply === '') {
            return $reply;
        }

        // Insert line breaks before inline numbered items: "... 1. ... 2. ..."
        $normalized = preg_replace('/\s+(\d+)\.\s+/u', "\n$1. ", $reply) ?? $reply;

        // Convert numbered list to dash bullets for consistent style.
        $normalized = preg_replace('/^\s*\d+\.\s+/m', '- ', $normalized) ?? $normalized;

        // Tidy up excessive blank lines.
        $normalized = preg_replace("/\n{3,}/", "\n\n", $normalized) ?? $normalized;

        return trim($normalized);
    }

    private function extractBranchFilter(string $message): array
    {
        $out = [];

        if (preg_match('/(?:cabang|kode(?:\s*cabang)?|kantor(?:\s*cabang)?)\s*(?:kode\s*)?([0-9]{3})\b/i', $message, $m)) {
            $out['kode'] = trim((string) $m[1]);
        }

        if (preg_match('/(?:kantor\s+cabang|cabang)\s+([a-zA-Z][a-zA-Z\s\-]{2,})/i', $message, $m)) {
            $name = trim((string) $m[1]);
            $name = preg_split('/\b(pada|periode|bulan|tahun|di|untuk|dari)\b/i', $name)[0] ?? $name;
            $name = trim($name);
            if ($name !== '' && ! preg_match('/^\d+$/', $name)) {
                $out['nama'] = $name;
            }
        }

        return $out;
    }

    private function extractPeriodFilter(string $message): array
    {
        $monthMap = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4, 'mei' => 5, 'juni' => 6,
            'juli' => 7, 'agustus' => 8, 'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
            'january' => 1, 'february' => 2, 'march' => 3, 'may' => 5, 'june' => 6, 'july' => 7,
            'august' => 8, 'october' => 10, 'december' => 12,
        ];

        if (! preg_match('/(?:periode|bulan)?\s*(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember|january|february|march|april|may|june|july|august|september|october|november|december)\s*(\d{4})?/i', $message, $m)) {
            return [];
        }

        $monthName = strtolower((string) $m[1]);
        $month = $monthMap[$monthName] ?? null;
        if (! $month) {
            return [];
        }

        $year = isset($m[2]) && $m[2] !== '' ? (int) $m[2] : (int) now()->year;
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        return [
            'label' => ucfirst($monthName) . ' ' . $year,
            'start' => $start,
            'end' => $end,
        ];
    }

    private function buildBranchPeriodLocalReply(array $context): string
    {
        $query = $context['branch_period_query'] ?? [];
        $requestedBranch = $query['requested_branch'] ?? [];
        $requestedPeriod = $query['requested_period']['label'] ?? 'periode yang diminta';
        $items = collect($query['items'] ?? []);
        $count = (int) ($query['matches_count'] ?? $items->count());

        $branchLabel = 'cabang yang diminta';
        if (! empty($requestedBranch['nama'])) {
            $branchLabel = 'cabang ' . $requestedBranch['nama'];
        } elseif (! empty($requestedBranch['kode'])) {
            $branchLabel = 'cabang kode ' . $requestedBranch['kode'];
        }

        if ($count <= 0) {
            return "Tidak ada tiket yang ditemukan untuk {$branchLabel} pada {$requestedPeriod}.";
        }

        $lines = ["Ditemukan {$count} tiket untuk {$branchLabel} pada {$requestedPeriod}:"];
        foreach ($items->take(20) as $t) {
            $nomor = (string) ($t['nomor_tiket'] ?? '-');
            $status = (string) ($t['status'] ?? '-');
            $kategori = (string) ($t['kategori'] ?? '-');
            $createdAt = (string) ($t['created_at'] ?? '-');
            $lines[] = "- {$nomor} | {$status} | {$kategori} | dibuat {$createdAt}";
        }

        if ($count > 20) {
            $lines[] = "- ... dan " . ($count - 20) . " tiket lainnya.";
        }

        return implode("\n", $lines);
    }

    private function ticketsHasCabangColumn(): bool
    {
        static $hasCabang = null;
        if ($hasCabang !== null) {
            return $hasCabang;
        }

        try {
            $hasCabang = Schema::hasColumn('tickets', 'cabang');
        } catch (\Throwable $e) {
            $hasCabang = false;
        }

        return $hasCabang;
    }
}

