<?php
namespace App\Repositories;

use App\Core\Database;

class NotificationRepository
{
    public static function create(int $userId, string $eventType, string $title, string $message, ?string $linkUrl = null): int
    {
        $sql = "INSERT INTO notifications (user_id, event_type, title, message, link_url, is_read)
                VALUES (:uid, :type, :title, :msg, :link, 0)";
        
        Database::query($sql, [
            ':uid' => $userId,
            ':type' => $eventType,
            ':title' => $title,
            ':msg' => $message,
            ':link' => $linkUrl
        ]);

        return (int)Database::getConnection()->lastInsertId();
    }

    public static function getByUser(int $userId, int $limit = 30): array
    {
        $sql = "SELECT * FROM notifications 
                WHERE user_id = :uid 
                ORDER BY created_at DESC 
                LIMIT {$limit}";

        $stmt = Database::query($sql, [':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public static function countUnread(int $userId): int
    {
        $stmt = Database::query(
            "SELECT COUNT(*) AS total FROM notifications WHERE user_id = :uid AND is_read = 0",
            [':uid' => $userId]
        );
        return (int)($stmt->fetch()['total'] ?? 0);
    }

    public static function markAsRead(int $id, int $userId): bool
    {
        Database::query(
            "UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :uid",
            [':id' => $id, ':uid' => $userId]
        );
        return true;
    }

    public static function markAllAsRead(int $userId): bool
    {
        Database::query(
            "UPDATE notifications SET is_read = 1 WHERE user_id = :uid",
            [':uid' => $userId]
        );
        return true;
    }
}
