<?php
namespace App\Core;

class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        if (!headers_sent()) {
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_samesite', 'Lax');
            session_start();
        }
        self::$started = true;

        if (!isset($_SESSION['_flash'])) {
            $_SESSION['_flash'] = [];
        }
    }

    public static function regenerate(): void
    {
        self::start();
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true);
        }
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, string $message): void
    {
        self::start();
        $_SESSION['_flash'][$key] = $message;
    }

    public static function getFlash(string $key): ?string
    {
        self::start();
        if (isset($_SESSION['_flash'][$key])) {
            $msg = $_SESSION['_flash'][$key];
            unset($_SESSION['_flash'][$key]);
            return $msg;
        }
        return null;
    }

    public static function getAllFlashes(): array
    {
        self::start();
        $flashes = $_SESSION['_flash'] ?? [];
        $_SESSION['_flash'] = [];
        return $flashes;
    }

    public static function csrfToken(): string
    {
        self::start();
        if (!isset($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    public static function validateCsrf(?string $token): bool
    {
        self::start();
        $stored = $_SESSION['_csrf_token'] ?? '';
        if (empty($token) || empty($stored)) {
            return false;
        }
        return hash_equals($stored, $token);
    }

    public static function setAuthUser(array $user): void
    {
        self::start();
        self::regenerate();
        unset($user['password_hash']);
        $_SESSION['user'] = $user;
    }

    public static function getAuthUser(): ?array
    {
        self::start();
        return $_SESSION['user'] ?? null;
    }

    public static function getUserId(): ?int
    {
        $user = self::getAuthUser();
        return $user ? (int)$user['id'] : null;
    }

    public static function isLoggedIn(): bool
    {
        return self::getAuthUser() !== null;
    }

    public static function isAdmin(): bool
    {
        $user = self::getAuthUser();
        return $user && ($user['role'] ?? '') === 'admin';
    }

    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
        self::$started = false;
    }

    public static function getTheme(): string
    {
        self::start();
        return $_SESSION['theme'] ?? 'dark';
    }

    public static function setTheme(string $theme): void
    {
        self::start();
        if (in_array($theme, ['dark', 'light'], true)) {
            $_SESSION['theme'] = $theme;
        }
    }
}
