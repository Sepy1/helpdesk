<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\AppSetting;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AssistantController extends Controller
{
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

        if (! AppSetting::getBool('ai_chat_enabled', true)) {
            return response()->json([
                'reply' => 'AI chat sedang dinonaktifkan oleh administrator.',
                'source' => 'disabled',
            ], 200);
        }

        if (empty($apiKey)) {
            return response()->json([
                'reply' => 'API key AI belum terpasang. Hubungi admin untuk konfigurasi.',
                'source' => 'fallback',
            ], 200);
        }

        $user = $request->user();
        $role = $user?->role ?? 'USER';
        $name = $user?->name ?? 'User';
        $ticketContext = $this->buildTicketContext($user, (string) $validated['message']);

        $messages = [
            [
                'role' => 'system',
                'content' => "Anda adalah AI Assistant untuk aplikasi Helpdesk internal. "
                    . "Fokus memberi panduan operasional penggunaan aplikasi (buat tiket, lihat tiket, update status, komentar, statistik, parameter, profil). "
                    . "Jawab singkat, jelas, dalam Bahasa Indonesia. Jangan membuat data fiktif dan jangan membahas hal di luar aplikasi. "
                    . "Jika pertanyaan terkait data tiket, gunakan hanya DATA_TIKET_REALTIME yang diberikan. "
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
            $response = Http::withToken($apiKey)
                ->timeout(45)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'temperature' => 0.3,
                    'messages' => $messages,
                ]);

            if (! $response->successful()) {
                Log::warning('Assistant chat failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json([
                    'reply' => 'AI sedang tidak tersedia. Coba beberapa saat lagi.',
                    'source' => 'fallback',
                ], 200);
            }

            $reply = trim((string) data_get($response->json(), 'choices.0.message.content', ''));
            $reply = $this->normalizeReplyFormatting($reply);

            if ($reply === '') {
                return response()->json([
                    'reply' => 'Saya belum bisa menjawab saat ini. Coba pertanyaan lain.',
                    'source' => 'fallback',
                ], 200);
            }

            return response()->json([
                'reply' => $reply,
                'source' => 'ai',
            ], 200);
        } catch (\Throwable $e) {
            Log::warning('Assistant chat exception', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'reply' => 'Terjadi gangguan saat menghubungi AI. Coba lagi sebentar.',
                'source' => 'fallback',
            ], 200);
        }
    }

    private function buildTicketContext($user, string $message): array
    {
        $baseQuery = $this->ticketQueryForUser($user);
        $analysisLimit = 200;

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
                ->with(['user:id,name', 'it:id,name', 'vendor:id,name', 'category:id,name', 'subcategory:id,name', 'rootCauseDetail:id,label'])
                ->latest('updated_at')
                ->limit(6)
                ->get()
        );

        $ticketNumbers = $this->extractTicketNumbers($message);
        $matchedTickets = collect();
        if (! empty($ticketNumbers)) {
            $matchedTickets = $this->formatTickets(
                (clone $baseQuery)
                    ->with(['user:id,name', 'it:id,name', 'vendor:id,name', 'category:id,name', 'subcategory:id,name', 'rootCauseDetail:id,label'])
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

        $fullTickets = $this->formatTickets(
            (clone $baseQuery)
                ->with(['user:id,name', 'it:id,name', 'vendor:id,name', 'category:id,name', 'subcategory:id,name', 'rootCauseDetail:id,label'])
                ->withCount('comments')
                ->latest('updated_at')
                ->limit($analysisLimit)
                ->get()
        );

        $totalScopedTickets = (clone $baseQuery)->count();

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
                'items' => $fullTickets,
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
}

