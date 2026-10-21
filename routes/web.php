<?php

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\ClaimController;
use App\Controllers\DashboardController;
use App\Controllers\HomeController;
use App\Controllers\MatchController;
use App\Controllers\NotificationController;
use App\Controllers\ReportController;
use App\Controllers\SearchController;
use App\Core\Router;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

$router = new Router();

// ==========================================
// 1. PUBLIC ROUTES
// ==========================================
$router->get('/', [HomeController::class, 'index']);
$router->post('/theme/toggle', [HomeController::class, 'toggleTheme']);

$router->get('/search', [SearchController::class, 'index']);
$router->get('/map', [SearchController::class, 'map']);
$router->get('/api/map-data', [SearchController::class, 'mapData']);

$router->get('/reports/{id}', [ReportController::class, 'show']);

// Authentication
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'processLogin'], [CsrfMiddleware::class]);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'processRegister'], [CsrfMiddleware::class]);
$router->post('/logout', [AuthController::class, 'logout'], [CsrfMiddleware::class]);

// ==========================================
// 2. AUTHENTICATED USER ROUTES
// ==========================================
$router->get('/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class]);
$router->get('/dashboard/reports', [DashboardController::class, 'myReports'], [AuthMiddleware::class]);
$router->get('/settings', [DashboardController::class, 'settings'], [AuthMiddleware::class]);
$router->post('/settings/profile', [DashboardController::class, 'updateProfile'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/settings/password', [DashboardController::class, 'updatePassword'], [AuthMiddleware::class, CsrfMiddleware::class]);

// Reporting
$router->get('/reports/create/lost', [ReportController::class, 'createLost'], [AuthMiddleware::class]);
$router->post('/reports/create/lost', [ReportController::class, 'storeLost'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/reports/create/found', [ReportController::class, 'createFound'], [AuthMiddleware::class]);
$router->post('/reports/create/found', [ReportController::class, 'storeFound'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/reports/{id}/confirmation', [ReportController::class, 'confirmation'], [AuthMiddleware::class]);

// Matches
$router->get('/matches', [MatchController::class, 'index'], [AuthMiddleware::class]);
$router->post('/matches/{id}/status', [MatchController::class, 'updateStatus'], [AuthMiddleware::class, CsrfMiddleware::class]);

// Claims
$router->get('/claims', [ClaimController::class, 'index'], [AuthMiddleware::class]);
$router->get('/reports/{report_id}/claim', [ClaimController::class, 'create'], [AuthMiddleware::class]);
$router->post('/claims', [ClaimController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/claims/{id}', [ClaimController::class, 'show'], [AuthMiddleware::class]);
$router->post('/claims/{id}/review', [ClaimController::class, 'review'], [AuthMiddleware::class, CsrfMiddleware::class]);

// Notifications
$router->get('/notifications', [NotificationController::class, 'index'], [AuthMiddleware::class]);
$router->post('/notifications/{id}/read', [NotificationController::class, 'markRead'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/notifications/read-all', [NotificationController::class, 'markAllRead'], [AuthMiddleware::class, CsrfMiddleware::class]);

// ==========================================
// 3. ADMINISTRATOR & MODERATOR ROUTES
// ==========================================
$router->get('/admin', [AdminController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/reports', [AdminController::class, 'reports'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/reports/{id}/status', [AdminController::class, 'updateReportStatus'], [AuthMiddleware::class, AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/claims', [AdminController::class, 'claims'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/users', [AdminController::class, 'users'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/users/{id}/status', [AdminController::class, 'updateUserStatus'], [AuthMiddleware::class, AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/audit', [AdminController::class, 'auditLogs'], [AuthMiddleware::class, AdminMiddleware::class]);

return $router;
