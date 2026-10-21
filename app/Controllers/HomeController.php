<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\ReportRepository;
use App\Repositories\UserRepository;

class HomeController
{
    public function index(Request $request): void
    {
        $stats = ReportRepository::getStats();
        $recentReports = ReportRepository::getRecentPublic(6);

        View::render('home.index', [
            'pageTitle' => 'TRACE — Lost & Found Intelligence Network',
            'stats' => $stats,
            'recentReports' => $recentReports,
        ]);
    }

    public function toggleTheme(Request $request): void
    {
        $current = Session::getTheme();
        $newTheme = ($current === 'dark') ? 'light' : 'dark';
        Session::setTheme($newTheme);

        if (Session::isLoggedIn()) {
            $userId = Session::getUserId();
            UserRepository::savePreferences($userId, ['theme' => $newTheme]);
        }

        if ($request->isAjax()) {
            Response::json(['success' => true, 'theme' => $newTheme]);
        } else {
            Response::back();
        }
    }
}
