<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AdminMiddleware
{
    public static function handle(Request $request): void
    {
        if (!Session::isLoggedIn()) {
            Session::flash('error', 'Administrator login required.');
            Response::redirect('/login?return=' . urlencode($request->uri()));
        }

        if (!Session::isAdmin()) {
            Session::flash('error', 'Access restricted. Administrator privileges required.');
            Response::redirect('/dashboard');
        }
    }
}
