<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\UserRepository;
use App\Services\AuthService;

class AuthController
{
    public function showLogin(Request $request): void
    {
        if (Session::isLoggedIn()) {
            Response::redirect('/dashboard');
        }

        View::render('auth.login', [
            'pageTitle' => 'Sign In &mdash; TRACE',
            'returnUrl' => $request->input('return', '/dashboard')
        ]);
    }

    public function processLogin(Request $request): void
    {
        $email = (string)$request->input('email', '');
        $password = (string)$request->input('password', '');
        $returnUrl = (string)$request->input('return', '/dashboard');

        $result = AuthService::login($email, $password);

        if (!$result['success']) {
            Session::flash('error', $result['errors']['email'][0] ?? $result['errors']['password'][0] ?? 'Login failed.');
            View::render('auth.login', [
                'pageTitle' => 'Sign In &mdash; TRACE',
                'errors' => $result['errors'],
                'oldEmail' => $email,
                'returnUrl' => $returnUrl
            ]);
            return;
        }

        Session::flash('success', 'Welcome back, ' . htmlspecialchars($result['user']['name']) . '.');
        $safeUrl = (!empty($returnUrl) && str_starts_with($returnUrl, '/')) ? $returnUrl : '/dashboard';
        Response::redirect($safeUrl);
    }

    public function showRegister(Request $request): void
    {
        if (Session::isLoggedIn()) {
            Response::redirect('/dashboard');
        }

        $organizations = UserRepository::getOrganizations();

        View::render('auth.register', [
            'pageTitle' => 'Create Account &mdash; TRACE',
            'organizations' => $organizations,
        ]);
    }

    public function processRegister(Request $request): void
    {
        $data = $request->only(['name', 'email', 'password', 'password_confirmation', 'organization_id']);
        $result = AuthService::register($data);

        if (!$result['success']) {
            $organizations = UserRepository::getOrganizations();
            View::render('auth.register', [
                'pageTitle' => 'Create Account &mdash; TRACE',
                'errors' => $result['errors'],
                'old' => $data,
                'organizations' => $organizations
            ]);
            return;
        }

        Session::flash('success', 'Account created successfully. Welcome to TRACE.');
        Response::redirect('/dashboard');
    }

    public function logout(Request $request): void
    {
        AuthService::logout();
        Session::flash('info', 'You have been safely signed out.');
        Response::redirect('/login');
    }
}
