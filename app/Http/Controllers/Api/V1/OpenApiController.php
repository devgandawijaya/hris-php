<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Integration\OpenApiService;
use Illuminate\Http\JsonResponse;

class OpenApiController extends Controller
{
    public function __construct(
        protected OpenApiService $openApiService
    ) {}

    /**
     * Return OpenAPI 3.1.0 specification JSON.
     */
    public function spec(): JsonResponse
    {
        return response()->json(
            $this->openApiService->getSpec(),
            200,
            ['Content-Type' => 'application/json; charset=utf-8'],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }
}
