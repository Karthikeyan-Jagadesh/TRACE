<?php
/**
 * TRACE — Authentication & User Security Tests
 * Run via: php tests/AuthTest.php
 */

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

use App\Repositories\UserRepository;
use App\Services\AuthService;

class AuthTest
{
    private int $passed = 0;
    private int $failed = 0;

    public function run(): void
    {
        echo "===============================================\n";
        echo " RUNNING AUTHENTICATION & SECURITY TESTS       \n";
        echo "===============================================\n";

        $this->testSuccessfulRegistration();
        $this->testDuplicateEmailRejection();
        $this->testValidLogin();
        $this->testInvalidPasswordLogin();
        $this->testSuspendedUserLogin();
        $this->testPasswordHashSecurity();

        echo "\n-----------------------------------------------\n";
        echo "Tests Passed: {$this->passed} | Failed: {$this->failed}\n";
        if ($this->failed > 0) {
            echo "FAILED: Some authentication tests failed.\n";
            exit(1);
        } else {
            echo "SUCCESS: All authentication tests passed 100%!\n";
        }
    }

    private function assert(bool $condition, string $message): void
    {
        if ($condition) {
            echo "  [PASS] {$message}\n";
            $this->passed++;
        } else {
            echo "  [FAIL] {$message}\n";
            $this->failed++;
        }
    }

    private function testSuccessfulRegistration(): void
    {
        $testEmail = 'unit.test.' . time() . '@campus.edu';
        $result = AuthService::register([
            'name' => 'Unit Test User',
            'email' => $testEmail,
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'organization_id' => 1
        ]);

        $this->assert($result['success'] === true, "User registration succeeds with valid parameters");
        $this->assert(!empty($result['user']['id']), "Created user receives positive database ID");
        $this->assert($result['user']['role'] === 'user', "Default assigned role is 'user'");
    }

    private function testDuplicateEmailRejection(): void
    {
        // Try to register existing seed email
        $result = AuthService::register([
            'name' => 'Duplicate Candidate',
            'email' => 'admin@trace.internal',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $this->assert($result['success'] === false, "Registration rejects duplicate email");
        $this->assert(!empty($result['errors']['email']), "Meaningful error message returned for duplicate email");
    }

    private function testValidLogin(): void
    {
        $result = AuthService::login('admin@trace.internal', 'Password123!');
        $this->assert($result['success'] === true, "Valid credentials authenticate successfully");
        $this->assert($result['user']['role'] === 'admin', "Administrator role recognized on login");
    }

    private function testInvalidPasswordLogin(): void
    {
        $result = AuthService::login('admin@trace.internal', 'WrongPasswordXYZ');
        $this->assert($result['success'] === false, "Incorrect password fails authentication");
        $this->assert(!empty($result['errors']['password']), "Appropriate password error message returned");
    }

    private function testSuspendedUserLogin(): void
    {
        // Create suspended test user
        $email = 'suspended.' . time() . '@test.com';
        $userId = UserRepository::create([
            'name' => 'Suspended Tester',
            'email' => $email,
            'password_hash' => password_hash('Pass12345!', PASSWORD_DEFAULT),
            'role' => 'user',
            'status' => 'suspended'
        ]);

        $result = AuthService::login($email, 'Pass12345!');
        $this->assert($result['success'] === false, "Suspended user cannot authenticate");
        $this->assert(str_contains($result['errors']['email'][0] ?? '', 'suspended'), "Suspension explanation returned");
    }

    private function testPasswordHashSecurity(): void
    {
        $user = UserRepository::findByEmail('admin@trace.internal');
        $this->assert(!empty($user['password_hash']), "User has hashed password in database");
        $this->assert(str_starts_with($user['password_hash'], '$2y$'), "Bcrypt / Default PHP hash algorithm verified");
        $this->assert(!str_contains($user['password_hash'], 'Password123!'), "Plain text password is never stored");
    }
}

$test = new AuthTest();
$test->run();
