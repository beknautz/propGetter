<?php
/**
 * PropIntel CRM - Dashboard Controller
 */

class DashboardController
{
    public static function index(): void
    {
        Auth::require();

        $stats         = Lead::dashboardStats();
        $dueTasks      = Task::getDueSoon(Auth::id(), 10);
        $upcomingTasks = Task::getUpcoming(Auth::id(), 10);

        View::render('dashboard/index', [
            'pageTitle'     => 'Dashboard',
            'stats'         => $stats,
            'dueTasks'      => $dueTasks,
            'upcomingTasks' => $upcomingTasks,
        ]);
    }
}
