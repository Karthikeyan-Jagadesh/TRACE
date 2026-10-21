<?php
namespace App\Services;

use App\Core\Session;
use App\Repositories\AuditLogRepository;
use App\Repositories\UserRepository;
use App\Validators\Validator;

class AuthService
{
    public static function register(array $data): array
    {
        $validator = Validator::make($data, [
            'name' => 'required|min:2|max:100',
            'email' => 'required|email|max:150',
            'password' => 'required|min:8|max:64',
            'password_confirmation' => 'required|matches:password',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors()];
        }

        // Check if email already registered
        $existing = UserRepository::findByEmail($data['email']);
        if ($existing) {
            return [
                'success' => false,
                'errors' => ['email' => ['This email address is already registered on TRACE.']]
            ];
        }

        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

        $userId = UserRepository::create([
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'password_hash' => $passwordHash,
            'role' => 'user',
            'organization_id' => !empty($data['organization_id']) ? (int)$data['organization_id'] : null,
            'status' => 'active'
        ]);

        $user = UserRepository::findById($userId);
        Session::setAuthUser($user);

        AuditLogRepository::log($userId, 'user_register', 'users', $userId, 'New user registered');

        return ['success' => true, 'user' => $user];
    }

    public static function login(string $email, string $password): array
    {
        $validator = Validator::make(['email' => $email, 'password' => $password], [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors()];
        }

        $user = UserRepository::findByEmail($email);
        if (!$user) {
            return [
                'success' => false,
                'errors' => ['email' => ['No account found matching this email address.']]
            ];
        }

        if ($user['status'] === 'suspended') {
            return [
                'success' => false,
                'errors' => ['email' => ['This account has been suspended by administration.']]
            ];
        }

        if (!password_verify($password, $user['password_hash'])) {
            return [
                'success' => false,
                'errors' => ['password' => ['Incorrect password. Please try again.']]
            ];
        }

        Session::setAuthUser($user);

        // Load theme from user preferences
        $prefs = UserRepository::getPreferences($user['id']);
        if (!empty($prefs['theme'])) {
            Session::setTheme($prefs['theme']);
        }

        AuditLogRepository::log($user['id'], 'user_login', 'users', $user['id'], 'User logged in successfully');

        return ['success' => true, 'user' => $user];
    }

    public static function logout(): void
    {
        $userId = Session::getUserId();
        if ($userId) {
            AuditLogRepository::log($userId, 'user_logout', 'users', $userId, 'User logged out');
        }
        Session::destroy();
    }

    public static function updateProfile(int $userId, array $data): array
    {
        $validator = Validator::make($data, [
            'name' => 'required|min:2|max:100',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors()];
        }

        UserRepository::update($userId, [
            'name' => trim($data['name']),
            'organization_id' => !empty($data['organization_id']) ? (int)$data['organization_id'] : null,
        ]);

        if (isset($data['theme'])) {
            UserRepository::savePreferences($userId, [
                'theme' => $data['theme'],
                'email_notifications' => $data['email_notifications'] ?? 1,
                'in_app_notifications' => $data['in_app_notifications'] ?? 1,
            ]);
            Session::setTheme($data['theme']);
        }

        // Update session user
        $updatedUser = UserRepository::findById($userId);
        Session::setAuthUser($updatedUser);

        AuditLogRepository::log($userId, 'profile_update', 'users', $userId, 'User updated profile details');

        return ['success' => true, 'user' => $updatedUser];
    }

    public static function changePassword(int $userId, string $current, string $new, string $confirm): array
    {
        $validator = Validator::make([
            'current' => $current,
            'new' => $new,
            'confirm' => $confirm
        ], [
            'current' => 'required',
            'new' => 'required|min:8|max:64',
            'confirm' => 'required|matches:new'
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors()];
        }

        $user = UserRepository::findById($userId);
        if (!$user || !password_verify($current, $user['password_hash'])) {
            return [
                'success' => false,
                'errors' => ['current' => ['Current password does not match our records.']]
            ];
        }

        UserRepository::updatePassword($userId, password_hash($new, PASSWORD_DEFAULT));
        AuditLogRepository::log($userId, 'password_change', 'users', $userId, 'User changed password');

        return ['success' => true];
    }
}
