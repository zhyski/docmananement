<?php

namespace App\Repositories\Contracts;

interface DashboardRepositoryInterface
{
    public function getReminders($month,$year);
}
