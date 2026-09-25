<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\RookPipeline\RookPipelineClient;
use Illuminate\Support\Facades\Log;

class SyncProductEnrichmentJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $productId
    ) {}

    public function handle(
        RookPipelineClient $rook
    ): void {
        $product = Product::find($this->productId);

        if (!$product) {
            Log::warning('Produit introuvable', [
                'product_id' => $this->productId,
            ]);

            return;
        }

        if (!$product->rook_job_id) {
            Log::warning('Rook job ID manquant', [
                'product_id' => $this->productId,
            ]);

            return;
        }

        Log::info('Vérification RookPipeline', [
            'product_id' => $product->id,
            'rook_job_id' => $product->rook_job_id,
        ]);

        $job = $rook->getJob(
            $product->rook_job_id
        );

        $status = $job['status'] ?? null;

        Log::info('Statut RookPipeline', [
            'product_id' => $product->id,
            'rook_job_id' => $product->rook_job_id,
            'status' => $status,
            'current_step' => $job['current_step'] ?? null,
        ]);

        /*
         * Le job n'est pas encore terminé.
         */
        if ($status !== 'completed') {

            if ($status === 'failed') {
                $product->update([
                    'rook_status' => 'failed',
                ]);

                Log::error('RookPipeline job failed', [
                    'product_id' => $product->id,
                    'rook_job_id' => $product->rook_job_id,
                    'job' => $job,
                ]);

                return;
            }

            self::dispatch(
                $this->productId
            )->delay(now()->addSeconds(10));

            return;
        }

        /*
         * Récupération du résultat.
         */
        $enrichment = $job['result']['enrichment'] ?? [];

        if (empty($enrichment)) {
            Log::error('Résultat enrichment absent', [
                'product_id' => $product->id,
                'rook_job_id' => $product->rook_job_id,
                'job' => $job,
            ]);

            return;
        }

        /*
         * Mise à jour du produit.
         */
        $product->update([
            'seo_title' => $enrichment['seo']['meta_title'] ?? null,

            'meta_description' =>
                $enrichment['seo']['meta_description'] ?? null,

            'long_description' =>
                $enrichment['content']['long_description'] ?? null,

            'benefits' =>
                $enrichment['content']['benefits'] ?? [],

            'specifications' =>
                $enrichment['specifications'] ?? [],

            'usage_tips' =>
                !empty($enrichment['content']['usage'])
                    ? preg_split(
                        '/\r\n|\r|\n/',
                        $enrichment['content']['usage']
                    )
                    : [],

            'seo_tags' =>
                $enrichment['seo']['keywords'] ?? [],

            'rook_status' => 'completed',
        ]);

        Log::info('Produit enrichi avec succès', [
            'product_id' => $product->id,
            'rook_job_id' => $product->rook_job_id,
        ]);
    }
}