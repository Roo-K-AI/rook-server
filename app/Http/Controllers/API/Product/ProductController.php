<?php

namespace App\Http\Controllers\API\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\CreateProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\Product\ProductCollection;
use App\Http\Resources\Product\ProductResource;
use App\Jobs\SyncProductEnrichmentJob;
use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\RookPipeline\RookPipelineClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function __construct(
        protected ProductRepositoryInterface $products
    ) {}

    public function index(): ProductCollection
    {
        return new ProductCollection($this->products->all());
    }

    public function store(
        CreateProductRequest $request,
        RookPipelineClient $rook
    ): JsonResponse {
        $dto = $request->toDto();

        // 1. Création locale du produit
        $product = $this->products->create($dto);

        // 2. Appel du pipeline ROOK
        $response = $rook->createJob([
            'schema_version' => 'product-enrichment-request.v1',
            'product_id'     => (string) $product->id,
            'product_name'   => $product->name,
            'locale'         => 'fr-FR',
        ]);

        // 3. Mise à jour du produit avec l'ID du job ROOK
        $product->update([
            'rook_job_id' => $response['job_id'] ?? null,
            'rook_status' => $response['status'] ?? 'accepted',
        ]);

        Log::info('Dispatch SyncProductEnrichmentJob', [
            'product_id'  => $product->id,
            'rook_job_id' => $response['job_id'] ?? null,
        ]);

        // 4. Planification du job de synchronisation
        SyncProductEnrichmentJob::dispatch($product->id)
            ->delay(now()->addSeconds(10));

        // 5. Réponse
        return response()->json([
            'message' => 'Produit créé, enrichissement en cours',
            'product' => new ProductResource($product->fresh()),
            'rook'    => $response,
        ], 201);
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $dto = $request->toDto($product);
        $updated = $this->products->update($product->id, $dto);

        return new ProductResource($updated);
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->products->delete($product->id);

        return response()->json(['message' => 'Produit supprimé']);
    }
}