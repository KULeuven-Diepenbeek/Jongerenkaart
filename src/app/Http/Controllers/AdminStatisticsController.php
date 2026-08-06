<?php

namespace App\Http\Controllers;

use App\Models\TrafficSnapshot;
use App\Services\TrafficDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminStatisticsController extends Controller
{
    public function index(): View
    {
        return view('admin.statistics', [
            'snapshot' => TrafficSnapshot::query()
                ->where('key', TrafficSnapshot::DASHBOARD_KEY)
                ->first(),
        ]);
    }

    public function refresh(Request $request, TrafficDashboardService $dashboard): RedirectResponse
    {
        $dashboard->refresh((string) $request->user()->getAuthIdentifier());

        return back()->with('status', 'Dashboard bijgewerkt met de laatst geregistreerde lokale statistieken.');
    }
}
