<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class CsrfMiddleware
{
    public static function handle(Request $request): void
    {
        if ($request->isPost()) {
            $token = $request->input('_csrf_token');
            if (!Session::validateCsrf($token)) {
                if ($request->isAjax()) {
                    Response::json(['error' => 'Invalid or expired CSRF security token.'], 403);
                } else {
                    Session::flash('error', 'Your session has expired. Please try again.');
                    Response::back();
                }
            }
        }
    }
}
