<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\DashboardRepositoryInterface;

class DashboardController extends Controller
{
    private $dashboardRepository;

    public function __construct(DashboardRepositoryInterface $dashboardRepository)
    {
        $this->dashboardRepository = $dashboardRepository;
    }

    public function getReminders($month, $year)
    {
        $modal = $this->dashboardRepository->getReminders($month, $year);
        return response()->json($modal);
    }
}
