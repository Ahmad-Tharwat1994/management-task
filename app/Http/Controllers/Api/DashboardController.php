<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly DashboardService $dashboardService
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $statistics = $this->dashboardService->statistics(
            $request->user()
        );

        return $this->success(
            data: $statistics,
            message: 'Dashboard statistics retrieved successfully.'
        );
    }
}