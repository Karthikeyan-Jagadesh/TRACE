<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware
{
    public static function handle(Request $request): void
    {
        if (!Session::isLoggedIn()) {
            Session::flash('error', 'Please sign in to access this page.');
            Response::redirect('/login?return=' . urlencode($request->uri()));
        }
    }
}
