<?php

namespace Tests\Feature\Product;

use App\Jobs\SyncProductEnrichmentJob;
use App\Models\Product;
use App\Services\RookPipeline\RookPipelineClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_all_products(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_can_show_single_product(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 99.99,
        ]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonPath('id', $product->id)
            ->assertJsonPath('name', 'Test Product');
    }

    public function test_can_create_product_and_dispatches_enrichment_job(): void
    {
        Queue::fake();

        $this->mock(RookPipelineClient::class, function (MockInterface $mock) {
            $mock->shouldReceive('createJob')
                ->once()
                ->andReturn([
                    'job_id' => 'job-test-123',
                    'status' => 'accepted',
                ]);
        });

        $payload = [
            'name' => 'Montre Connectée',
            'description' => 'Superbe montre connectée',
            'price' => 199.99,
        ];

        $response = $this->postJson('/api/products', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Produit créé, enrichissement en cours')
            ->assertJsonPath('product.name', 'Montre Connectée')
            ->assertJsonPath('product.rook_job_id', 'job-test-123')
            ->assertJsonPath('product.rook_status', 'accepted');

        $this->assertDatabaseHas('products', [
            'name' => 'Montre Connectée',
            'rook_job_id' => 'job-test-123',
            'rook_status' => 'accepted',
        ]);

        Queue::assertPushed(SyncProductEnrichmentJob::class);
    }

    public function test_validation_fails_on_product_creation_without_name(): void
    {
        $response = $this->postJson('/api/products', [
            'description' => 'Produit sans nom',
            'price' => 50.00,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_can_update_product(): void
    {
        $product = Product::factory()->create([
            'name' => 'Original Name',
            'price' => 50.00,
        ]);

        $response = $this->putJson("/api/products/{$product->id}", [
            'name' => 'Updated Name',
            'price' => 75.00,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('name', 'Updated Name');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Produit supprimé');

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }
}

