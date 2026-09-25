<?php

namespace App\Services\RookPipeline;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class RookPipelineClient
{
    protected string $baseUrl;
    protected string $token;

    public function __construct()
    {
        $baseUrl = config('services.rook_pipeline.base_url');
        $token   = config('services.rook_pipeline.token');

        if (empty($baseUrl)) {
            throw new RuntimeException(
                'ROOK_PIPELINE_BASE_URL non configurée (voir .env)'
            );
        }

        $this->baseUrl = rtrim($baseUrl, '/');
        $this->token   = (string) $token;
    }

    public function health(): array
    {
        $response = Http::timeout(10)->get("{$this->baseUrl}/health");

        if ($response->failed()) {
            throw new RuntimeException(
                "RookPipeline /health a échoué : HTTP {$response->status()}"
            );
        }

        return $this->decodeJson($response, '/health');
    }

    public function createJob(array $payload): array
    {
        $response = Http::withHeaders([
            'Authorization'     => 'Bearer ' . $this->token,
            'X-Correlation-Id'  => uniqid('rook-', true),
            'Idempotency-Key'   => uniqid('job-', true),
            'Accept'            => 'application/json',
        ])
            ->timeout(30)
            ->post("{$this->baseUrl}/internal/v1/product-enrichments", $payload);

        return $this->decodeJson(
            $response,
            '/internal/v1/product-enrichments'
        );
    }

    public function getJob(string $jobId): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept'        => 'application/json',
        ])
            ->timeout(15)
            ->get("{$this->baseUrl}/internal/v1/product-enrichments/jobs/{$jobId}");

        return $this->decodeJson(
            $response,
            "/internal/v1/product-enrichments/jobs/{$jobId}"
        );
    }

    public function generateSeo(string $productName): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept'        => 'application/json',
        ])
            ->timeout(30)
            ->post(
                "{$this->baseUrl}/internal/v1/product-enrichments/generate",
                ['product_name' => $productName]
            );

        return $this->decodeJson(
            $response,
            '/internal/v1/product-enrichments/generate'
        );
    }

    /**
     * Décode la réponse JSON en levant une exception claire en cas d'échec.
     */
    protected function decodeJson($response, string $endpoint): array
    {
        if ($response->failed()) {
            Log::error('RookPipeline HTTP error', [
                'endpoint' => $endpoint,
                'status'   => $response->status(),
                'body'     => substr($response->body(), 0, 500),
            ]);

            throw new RuntimeException(
                "RookPipeline {$endpoint} a échoué (HTTP {$response->status()})"
            );
        }

        $data = $response->json();

        if (!is_array($data)) {
            Log::error('RookPipeline réponse non-JSON', [
                'endpoint' => $endpoint,
                'status'   => $response->status(),
                'body'     => substr($response->body(), 0, 500),
            ]);

            throw new RuntimeException(
                "RookPipeline {$endpoint} a renvoyé une réponse invalide"
            );
        }

        return $data;
    }
}