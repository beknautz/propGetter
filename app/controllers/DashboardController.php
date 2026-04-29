<?php
/**
 * PropIntel CRM - Dashboard Controller
 */

class DashboardController
{
    public static function index(): void
    {
        Auth::require();

        $stats       = Lead::dashboardStats();
        $dueTasks    = Task::getDueSoon(Auth::id(), 5);
        $upcomingTasks = Task::getUpcoming(Auth::id(), 5);

        View::render('dashboard/index', [
            'pageTitle'     => 'Dashboard',
            'stats'         => $stats,
            'dueTasks'      => $dueTasks,
            'upcomingTasks' => $upcomingTasks,
        ]);
    }
}
