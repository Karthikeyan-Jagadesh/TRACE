<?php
namespace App\Core;

use App\Helpers\Helper;
use App\Repositories\NotificationRepository;

class View
{
    public static function render(string $viewPath, array $data = [], ?string $layout = 'layouts/main'): void
    {
        $viewsDir = dirname(__DIR__, 2) . '/resources/views/';
        $viewFile = $viewsDir . str_replace('.', '/', $viewPath) . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View file not found: $viewFile");
        }

        // Global view data
        $authUser = Session::getAuthUser();
        $isLoggedIn = Session::isLoggedIn();
        $isAdmin = Session::isAdmin();
        $flashes = Session::getAllFlashes();
        $theme = Session::getTheme();
        $csrfToken = Session::csrfToken();

        $unreadCount = 0;
        if ($isLoggedIn && class_exists(NotificationRepository::class)) {
            try {
                $unreadCount = NotificationRepository::countUnread($authUser['id']);
            } catch (\Throwable $e) {
                $unreadCount = 0;
            }
        }

        $allData = array_merge([
            'authUser' => $authUser,
            'isLoggedIn' => $isLoggedIn,
            'isAdmin' => $isAdmin,
            'flashes' => $flashes,
            'theme' => $theme,
            'csrfToken' => $csrfToken,
            'unreadCount' => $unreadCount,
            'pageTitle' => 'TRACE — Lost & Found Intelligence Network'
        ], $data);

        extract($allData);

        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        if ($layout !== null) {
            $layoutFile = $viewsDir . str_replace('.', '/', $layout) . '.php';
            if (!file_exists($layoutFile)) {
                throw new \RuntimeException("Layout file not found: $layoutFile");
            }
            ob_start();
            include $layoutFile;
            $content = ob_get_clean();
        }

        Response::html($content);
    }
}
