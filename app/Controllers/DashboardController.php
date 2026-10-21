<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\ClaimRepository;
use App\Repositories\MatchRepository;
use App\Repositories\ReportRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;

class DashboardController
{
    public function index(Request $request): void
    {
        $userId = Session::getUserId();

        $myReports = ReportRepository::getByUser($userId, 5);
        $totalReports = ReportRepository::countByUser($userId);

        $pendingMatches = MatchRepository::getPendingMatchesForUser($userId, 4);
        $totalMatches = MatchRepository::countPendingMatchesForUser($userId);

        $myClaims = ClaimRepository::getByClaimant($userId);
        $claimsOnMyItems = ClaimRepository::getByFinder($userId);
        $pendingClaimsCount = ClaimRepository::countPendingForUser($userId);

        // Count recovered reports for this user
        $recoveredCount = 0;
        foreach ($myReports as $r) {
            if ($r['status'] === 'recovered') {
                $recoveredCount++;
            }
        }

        View::render('dashboard.index', [
            'pageTitle' => 'Intelligence Dashboard &mdash; TRACE',
            'myReports' => $myReports,
            'totalReports' => $totalReports,
            'pendingMatches' => $pendingMatches,
            'totalMatches' => $totalMatches,
            'myClaims' => $myClaims,
            'claimsOnMyItems' => $claimsOnMyItems,
            'pendingClaimsCount' => $pendingClaimsCount,
            'recoveredCount' => $recoveredCount,
        ]);
    }

    public function myReports(Request $request): void
    {
        $userId = Session::getUserId();
        $page = max(1, (int)$request->input('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $reports = ReportRepository::getByUser($userId, $limit, $offset);
        $total = ReportRepository::countByUser($userId);
        $totalPages = max(1, (int)ceil($total / $limit));

        View::render('dashboard.my_reports', [
            'pageTitle' => 'My Reports &mdash; TRACE',
            'reports' => $reports,
            'total' => $total,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    public function settings(Request $request): void
    {
        $userId = Session::getUserId();
        $user = UserRepository::findById($userId);
        $prefs = UserRepository::getPreferences($userId);
        $organizations = UserRepository::getOrganizations();

        View::render('dashboard.settings', [
            'pageTitle' => 'Account Settings &mdash; TRACE',
            'user' => $user,
            'prefs' => $prefs,
            'organizations' => $organizations,
        ]);
    }

    public function updateProfile(Request $request): void
    {
        $userId = Session::getUserId();
        $data = $request->only(['name', 'organization_id', 'theme', 'email_notifications', 'in_app_notifications']);

        $result = AuthService::updateProfile($userId, $data);
        if (!$result['success']) {
            Session::flash('error', $result['errors']['name'][0] ?? 'Profile update failed.');
        } else {
            Session::flash('success', 'Profile and preferences updated successfully.');
        }
        Response::redirect('/settings');
    }

    public function updatePassword(Request $request): void
    {
        $userId = Session::getUserId();
        $current = (string)$request->input('current_password', '');
        $new = (string)$request->input('new_password', '');
        $confirm = (string)$request->input('new_password_confirmation', '');

        $result = AuthService::changePassword($userId, $current, $new, $confirm);
        if (!$result['success']) {
            $err = $result['errors']['current'][0] ?? $result['errors']['new'][0] ?? $result['errors']['confirm'][0] ?? 'Password update failed.';
            Session::flash('error', $err);
        } else {
            Session::flash('success', 'Password changed successfully.');
        }
        Response::redirect('/settings');
    }
}
