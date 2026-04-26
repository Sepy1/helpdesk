<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExecutiveSummaryService
{
    public static function generate(array $payload): ?string
    {
        $apiKey = config('services.openai.api_key');
        $model = config('services.openai.model', 'gpt-4o-mini');

        if (empty($apiKey)) {
            Log::warning('Executive summary skipped: OPENAI_API_KEY not set.');
            return null;
        }

        $prompt = <<<PROMPT
buatkan ringkasan eksekutif terhadap data tiket yang dikirim dan jelaskan mitigasi yang perlu dilakukan degan menampilkan statistik jumlah tiket. jangan halu dan pastikan hanya memaiaki data yang dikirim

Gunakan Bahasa Indonesia formal, jelas, dan seluruh output wajib berbentuk narasi paragraf.
Dilarang menggunakan bullet points, numbering, daftar berpoin, atau format list apa pun.
Narasi wajib mencakup:
- gambaran umum performa helpdesk berdasarkan statistik jumlah tiket,
- narasi statistik kategori tiket yang dominan,
- narasi statistik sub kategori tiket yang dominan,
- narasi pola penyebab (jika tersedia di data),
- mitigasi yang konkret dan relevan berdasarkan data.

Jika ada data yang kosong, sebutkan "tidak tersedia" tanpa membuat asumsi.
PROMPT;

        $response = Http::withToken($apiKey)
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'temperature' => 0.2,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Anda adalah analis service desk. Jangan membuat data baru. Hanya gunakan data JSON yang diberikan user.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt . "\n\nDATA TIKET (JSON):\n" . json_encode($payload, JSON_UNESCAPED_UNICODE),
                    ],
                ],
            ]);

        if (! $response->successful()) {
            Log::warning('Executive summary API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $summary = data_get($response->json(), 'choices.0.message.content');

        if (! is_string($summary) || trim($summary) === '') {
            Log::warning('Executive summary API response empty.');
            return null;
        }

        return trim($summary);
    }
}
