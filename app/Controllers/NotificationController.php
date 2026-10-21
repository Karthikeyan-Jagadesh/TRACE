<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\NotificationRepository;

class NotificationController
{
    public function index(Request $request): void
    {
        $userId = Session::getUserId();
        $notifications = NotificationRepository::getByUser($userId, 40);

        View::render('notifications.index', [
            'pageTitle' => 'Notifications &mdash; TRACE',
            'notifications' => $notifications,
        ]);
    }

    public function markRead(Request $request, array $params): void
    {
        $userId = Session::getUserId();
        $id = (int)($params['id'] ?? 0);
        NotificationRepository::markAsRead($id, $userId);

        if ($request->isAjax()) {
            Response::json(['success' => true]);
        } else {
            Response::back();
        }
    }

    public function markAllRead(Request $request): void
    {
        $userId = Session::getUserId();
        NotificationRepository::markAllAsRead($userId);

        if ($request->isAjax()) {
            Response::json(['success' => true]);
        } else {
            Session::flash('success', 'All notifications marked as read.');
            Response::back();
        }
    }
}
