<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ItBoardCrController extends Controller
{
    private array $statusMap = [
        'open' => ['label' => 'Backlog', 'api' => 'open', 'tone' => 'amber'],
        'vendor_development' => ['label' => 'On Progress', 'api' => 'vendor_development', 'tone' => 'indigo'],
        'uat' => ['label' => 'Testing UAT', 'api' => 'uat', 'tone' => 'cyan'],
        'go_live' => ['label' => 'Go Live', 'api' => 'go_live', 'tone' => 'emerald'],
        'closed' => ['label' => 'Done', 'api' => 'closed', 'tone' => 'slate'],
    ];

    public function index()
    {
        if (auth()->user()->role !== 'IT') {
            abort(403);
        }

        return view('it.board_cr', [
            'statusMap' => $this->statusMap,
        ]);
    }

    public function dashboard()
    {
        return $this->apiRequest('GET', $this->dashboardUrl());
    }

    public function show(string $externCr)
    {
        return $this->apiRequest('GET', $this->detailUrl($externCr));
    }

    public function updateStatus(Request $request, string $externCr)
    {
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', array_keys($this->statusMap))],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        return $this->apiRequest('PATCH', $this->statusUrl($externCr), [
            'status' => $data['status'],
            'note' => $data['note'] ?? null,
        ]);
    }

    private function apiRequest(string $method, string $url, array $payload = [])
    {
        $baseUrl = config('services.extern_cr.base_url');
        $apiKey = config('services.extern_cr.api_key');

        if (! $baseUrl || ! $apiKey) {
            return response()->json([
                'ok' => false,
                'message' => 'Konfigurasi API CR eksternal belum lengkap.',
            ], 503);
        }

        try {
            $request = Http::baseUrl($baseUrl)
                ->timeout((int) config('services.extern_cr.timeout', 20))
                ->acceptJson()
                ->withHeaders([
                    'X-Extern-Cr-Api-Key' => $apiKey,
                ]);

            if (! config('services.extern_cr.verify_ssl', true)) {
                $request = $request->withOptions(['verify' => false]);
            }

            $response = match (strtoupper($method)) {
                'GET' => $request->get($url),
                'PATCH' => $request->patch($url, $payload),
                default => $request->send($method, $url, ['json' => $payload]),
            };

            if ($response->successful()) {
                return response()->json($response->json(), $response->status());
            }

            Log::warning('Board CR API request failed', [
                'method' => $method,
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json($response->json() ?: [
                'ok' => false,
                'message' => 'Gagal menghubungi API CR eksternal.',
            ], $response->status());
        } catch (\Throwable $e) {
            Log::error('Board CR API exception', [
                'method' => $method,
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Terjadi gangguan saat menghubungi API CR eksternal.',
            ], 503);
        }
    }

    private function dashboardUrl(): string
    {
        return (string) config('services.extern_cr.dashboard_path', '/api/cr-eksternal/dashboard');
    }

    private function detailUrl(string $externCr): string
    {
        return rtrim((string) config('services.extern_cr.detail_path', '/api/cr-eksternal'), '/') . '/' . rawurlencode($externCr);
    }

    private function statusUrl(string $externCr): string
    {
        return $this->detailUrl($externCr) . '/status';
    }
}
