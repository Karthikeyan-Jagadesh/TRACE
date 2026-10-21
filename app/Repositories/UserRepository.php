<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class UserRepository
{
    public static function findById(int $id): ?array
    {
        $stmt = Database::query(
            "SELECT u.*, o.name AS organization_name 
             FROM users u 
             LEFT JOIN organizations o ON u.organization_id = o.id 
             WHERE u.id = :id LIMIT 1",
            [':id' => $id]
        );
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::query(
            "SELECT u.*, o.name AS organization_name 
             FROM users u 
             LEFT JOIN organizations o ON u.organization_id = o.id 
             WHERE LOWER(u.email) = LOWER(:email) LIMIT 1",
            [':email' => trim($email)]
        );
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function create(array $data): int
    {
        $sql = "INSERT INTO users (name, email, password_hash, role, organization_id, status)
                VALUES (:name, :email, :password_hash, :role, :organization_id, :status)";
        
        Database::query($sql, [
            ':name' => $data['name'],
            ':email' => strtolower(trim($data['email'])),
            ':password_hash' => $data['password_hash'],
            ':role' => $data['role'] ?? 'user',
            ':organization_id' => $data['organization_id'] ?? null,
            ':status' => $data['status'] ?? 'active'
        ]);

        $userId = (int)Database::getConnection()->lastInsertId();

        // Create initial default preferences
        Database::query(
            "INSERT INTO user_preferences (user_id, theme, email_notifications, in_app_notifications)
             VALUES (:user_id, 'dark', 1, 1)",
            [':user_id' => $userId]
        );

        return $userId;
    }

    public static function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['name'])) {
            $fields[] = "name = :name";
            $params[':name'] = $data['name'];
        }
        if (isset($data['organization_id'])) {
            $fields[] = "organization_id = :org_id";
            $params[':org_id'] = $data['organization_id'] ?: null;
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        Database::query($sql, $params);
        return true;
    }

    public static function updatePassword(int $id, string $hash): bool
    {
        Database::query("UPDATE users SET password_hash = :hash WHERE id = :id", [
            ':hash' => $hash,
            ':id' => $id
        ]);
        return true;
    }

    public static function getAll(int $limit = 50, int $offset = 0): array
    {
        $stmt = Database::query(
            "SELECT u.id, u.name, u.email, u.role, u.status, u.created_at, o.name AS organization_name,
                    (SELECT COUNT(*) FROM reports r WHERE r.creator_id = u.id) AS report_count,
                    (SELECT COUNT(*) FROM claims c WHERE c.claimant_id = u.id) AS claim_count
             FROM users u
             LEFT JOIN organizations o ON u.organization_id = o.id
             ORDER BY u.created_at DESC LIMIT :limit OFFSET :offset",
            [':limit' => $limit, ':offset' => $offset]
        );
        return $stmt->fetchAll();
    }

    public static function countAll(): int
    {
        $stmt = Database::query("SELECT COUNT(*) AS total FROM users");
        return (int)($stmt->fetch()['total'] ?? 0);
    }

    public static function setStatus(int $id, string $status): bool
    {
        Database::query("UPDATE users SET status = :status WHERE id = :id", [
            ':status' => $status,
            ':id' => $id
        ]);
        return true;
    }

    public static function getPreferences(int $userId): array
    {
        $stmt = Database::query("SELECT * FROM user_preferences WHERE user_id = :id LIMIT 1", [':id' => $userId]);
        $pref = $stmt->fetch();
        return $pref ?: [
            'theme' => 'dark',
            'email_notifications' => 1,
            'in_app_notifications' => 1
        ];
    }

    public static function savePreferences(int $userId, array $data): bool
    {
        $stmt = Database::query(
            "INSERT INTO user_preferences (user_id, theme, email_notifications, in_app_notifications)
             VALUES (:user_id, :theme, :email_notif, :in_app_notif)
             ON DUPLICATE KEY UPDATE 
                theme = VALUES(theme),
                email_notifications = VALUES(email_notifications),
                in_app_notifications = VALUES(in_app_notifications)",
            [
                ':user_id' => $userId,
                ':theme' => $data['theme'] ?? 'dark',
                ':email_notif' => isset($data['email_notifications']) ? 1 : 0,
                ':in_app_notif' => isset($data['in_app_notifications']) ? 1 : 0,
            ]
        );
        return true;
    }

    public static function getOrganizations(): array
    {
        $stmt = Database::query("SELECT * FROM organizations ORDER BY name ASC");
        return $stmt->fetchAll();
    }
}
