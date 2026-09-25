<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SyncProductEnrichmentJob;
use App\Models\Product;
use App\Services\RookPipeline\RookPipelineClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;
use Tests\TestCase;

class SyncProductEnrichmentJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_exits_when_product_does_not_exist(): void
    {
        $mockClient = $this->mock(RookPipelineClient::class);
        $mockClient->shouldNotReceive('getJob');

        $job = new SyncProductEnrichmentJob(999);
        $job->handle($mockClient);
    }

    public function test_job_exits_when_product_has_no_rook_job_id(): void
    {
        $product = Product::factory()->create([
            'rook_job_id' => null,
        ]);

        $mockClient = $this->mock(RookPipelineClient::class);
        $mockClient->shouldNotReceive('getJob');

        $job = new SyncProductEnrichmentJob($product->id);
        $job->handle($mockClient);
    }

    public function test_job_updates_status_to_failed_when_rook_job_fails(): void
    {
        $product = Product::factory()->create([
            'rook_job_id' => 'job-failed-123',
            'rook_status' => 'processing',
        ]);

        $mockClient = $this->mock(RookPipelineClient::class, function (MockInterface $mock) {
            $mock->shouldReceive('getJob')
                ->with('job-failed-123')
                ->once()
                ->andReturn([
                    'status' => 'failed',
                ]);
        });

        $job = new SyncProductEnrichmentJob($product->id);
        $job->handle($mockClient);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'rook_status' => 'failed',
        ]);
    }

    public function test_job_re_dispatches_when_rook_job_is_still_processing(): void
    {
        Queue::fake();

        $product = Product::factory()->create([
            'rook_job_id' => 'job-pending-123',
            'rook_status' => 'processing',
        ]);

        $mockClient = $this->mock(RookPipelineClient::class, function (MockInterface $mock) {
            $mock->shouldReceive('getJob')
                ->with('job-pending-123')
                ->once()
                ->andReturn([
                    'status' => 'processing',
                    'current_step' => 'extracting_metadata',
                ]);
        });

        $job = new SyncProductEnrichmentJob($product->id);
        $job->handle($mockClient);

        Queue::assertPushed(SyncProductEnrichmentJob::class);
    }

    public function test_job_enriches_product_when_rook_job_is_completed(): void
    {
        $product = Product::factory()->create([
            'rook_job_id' => 'job-complete-123',
            'rook_status' => 'processing',
        ]);

        $mockClient = $this->mock(RookPipelineClient::class, function (MockInterface $mock) {
            $mock->shouldReceive('getJob')
                ->with('job-complete-123')
                ->once()
                ->andReturn([
                    'status' => 'completed',
                    'result' => [
                        'enrichment' => [
                            'seo' => [
                                'meta_title' => 'Montre Connectée SEO',
                                'meta_description' => 'Description SEO pour la montre connectée',
                                'keywords' => ['montre', 'tech', 'connectée'],
                            ],
                            'content' => [
                                'long_description' => 'Description détaillée de la montre connectée...',
                                'benefits' => ['Autonomie 7 jours', 'Etanche 50m'],
                                'usage' => "Allumer l'appareil\nConnecter en Bluetooth",
                            ],
                            'specifications' => [
                                'ecran' => 'OLED 1.4 pouce',
                                'poids' => '45g',
                            ],
                        ],
                    ],
                ]);
        });

        $job = new SyncProductEnrichmentJob($product->id);
        $job->handle($mockClient);

        $product->refresh();

        $this->assertEquals('completed', $product->rook_status);
        $this->assertEquals('Montre Connectée SEO', $product->seo_title);
        $this->assertEquals('Description SEO pour la montre connectée', $product->meta_description);
        $this->assertEquals('Description détaillée de la montre connectée...', $product->long_description);
        $this->assertEquals(['Autonomie 7 jours', 'Etanche 50m'], $product->benefits);
        $this->assertEquals(['ecran' => 'OLED 1.4 pouce', 'poids' => '45g'], $product->specifications);
        $this->assertEquals(["Allumer l'appareil", "Connecter en Bluetooth"], $product->usage_tips);
        $this->assertEquals(['montre', 'tech', 'connectée'], $product->seo_tags);
    }
}

