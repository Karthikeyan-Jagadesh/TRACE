<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\AuditLogRepository;
use App\Repositories\ClaimRepository;
use App\Repositories\ReportRepository;
use App\Repositories\UserRepository;

class AdminController
{
    public function index(Request $request): void
    {
        $stats = ReportRepository::getStats();
        $totalUsers = UserRepository::countAll();
        $pendingClaims = ClaimRepository::getAll(5);
        $recentAudit = AuditLogRepository::getRecent(6);

        View::render('admin.index', [
            'pageTitle' => 'Admin Intelligence Oversight &mdash; TRACE',
            'stats' => $stats,
            'totalUsers' => $totalUsers,
            'pendingClaims' => $pendingClaims,
            'recentAudit' => $recentAudit,
        ]);
    }

    public function reports(Request $request): void
    {
        $page = max(1, (int)$request->input('page', 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $filters = [
            'status' => $request->input('status', ''),
            'type' => $request->input('type', ''),
            'q' => $request->input('q', '')
        ];

        $reports = ReportRepository::search($filters, $limit, $offset);
        $total = ReportRepository::countSearch($filters);
        $totalPages = max(1, (int)ceil($total / $limit));

        View::render('admin.reports', [
            'pageTitle' => 'Reports Moderation &mdash; TRACE Admin',
            'reports' => $reports,
            'filters' => $filters,
            'total' => $total,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    public function updateReportStatus(Request $request, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $status = (string)$request->input('status', '');
        $allowed = ['active', 'under_review', 'claimed', 'recovered', 'expired', 'closed'];

        if (!in_array($status, $allowed, true)) {
            Session::flash('error', 'Invalid status provided.');
            Response::back();
        }

        ReportRepository::updateStatus($id, $status);
        AuditLogRepository::log(
            Session::getUserId(),
            'admin_report_status_change',
            'reports',
            $id,
            "Admin updated report status to {$status}"
        );

        Session::flash('success', "Report status updated to {$status}.");
        Response::back();
    }

    public function claims(Request $request): void
    {
        $page = max(1, (int)$request->input('page', 1));
        $limit = 25;
        $offset = ($page - 1) * $limit;

        $claims = ClaimRepository::getAll($limit, $offset);

        View::render('admin.claims', [
            'pageTitle' => 'Claims Oversight &mdash; TRACE Admin',
            'claims' => $claims,
            'currentPage' => $page,
        ]);
    }

    public function users(Request $request): void
    {
        $page = max(1, (int)$request->input('page', 1));
        $limit = 25;
        $offset = ($page - 1) * $limit;

        $users = UserRepository::getAll($limit, $offset);
        $total = UserRepository::countAll();
        $totalPages = max(1, (int)ceil($total / $limit));

        View::render('admin.users', [
            'pageTitle' => 'User Management &mdash; TRACE Admin',
            'users' => $users,
            'total' => $total,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    public function updateUserStatus(Request $request, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $status = (string)$request->input('status', 'active');

        if (!in_array($status, ['active', 'suspended'], true)) {
            Response::back();
        }

        // Prevent admin from suspending themselves
        if ($id === Session::getUserId()) {
            Session::flash('error', 'You cannot suspend your own administrative account.');
            Response::back();
        }

        UserRepository::setStatus($id, $status);
        AuditLogRepository::log(
            Session::getUserId(),
            'admin_user_status_change',
            'users',
            $id,
            "Admin set user status to {$status}"
        );

        Session::flash('success', "User account status updated to {$status}.");
        Response::back();
    }

    public function auditLogs(Request $request): void
    {
        $logs = AuditLogRepository::getRecent(100);

        View::render('admin.audit', [
            'pageTitle' => 'System Audit Logs &mdash; TRACE Admin',
            'logs' => $logs,
        ]);
    }
}
