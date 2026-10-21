<?php
namespace App\Repositories;

use App\Core\Database;

class AuditLogRepository
{
    public static function log(
        ?int $userId,
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?string $details = null,
        ?string $ipAddress = null
    ): int {
        $sql = "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details, ip_address)
                VALUES (:uid, :act, :etype, :eid, :details, :ip)";

        Database::query($sql, [
            ':uid' => $userId,
            ':act' => $action,
            ':etype' => $entityType,
            ':eid' => $entityId,
            ':details' => $details,
            ':ip' => $ipAddress ?? ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1')
        ]);

        return (int)Database::getConnection()->lastInsertId();
    }

    public static function getRecent(int $limit = 50): array
    {
        $sql = "SELECT a.*, u.name AS user_name, u.email AS user_email
                FROM audit_logs a
                LEFT JOIN users u ON a.user_id = u.id
                ORDER BY a.created_at DESC
                LIMIT {$limit}";

        $stmt = Database::query($sql);
        return $stmt->fetchAll();
    }
}
