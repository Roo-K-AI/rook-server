<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class QueueTickController extends Controller
{
    public function tick(Request $request): JsonResponse
    {
        // Sécurité : token dans la query string ou header
        $expected = config('services.queue_tick_token');
        $given = $request->header('X-Queue-Token') ?? $request->query('token');

        if (!$expected || $given !== $expected) {
            abort(403, 'Forbidden');
        }

        // Traite les jobs en attente (max 60s pour éviter le timeout)
        Artisan::call('queue:work', [
            '--stop-when-empty' => true,
            '--tries' => 3,
            '--timeout' => 25,
            '--max-time' => 50,
        ]);

        $output = Artisan::output();

        return response()->json([
            'ok' => true,
            'output' => trim($output),
            'pending' => \DB::table('jobs')->count(),
        ]);
    }
}