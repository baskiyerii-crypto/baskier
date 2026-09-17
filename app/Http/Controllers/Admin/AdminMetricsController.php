<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PlatformMetricsService;
use Illuminate\Http\Request;

class AdminMetricsController extends Controller
{
    public function index(PlatformMetricsService $metricsService)
    {
        $metrics = $metricsService->getMetrics();

        return view('admin.metrics.index', [
            'metrics' => $metrics,
        ]);
    }
}