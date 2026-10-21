<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class ReportRepository
{
    public static function findById(int $id): ?array
    {
        $stmt = Database::query(
            "SELECT r.*, u.name AS creator_name, u.email AS creator_email, o.name AS organization_name,
                    (SELECT file_path FROM report_images WHERE report_id = r.id AND is_primary = 1 LIMIT 1) AS primary_image
             FROM reports r
             JOIN users u ON r.creator_id = u.id
             LEFT JOIN organizations o ON u.organization_id = o.id
             WHERE r.id = :id LIMIT 1",
            [':id' => $id]
        );
        $report = $stmt->fetch();
        return $report ?: null;
    }

    public static function findByIdentifier(string $identifier): ?array
    {
        $stmt = Database::query(
            "SELECT r.*, u.name AS creator_name, u.email AS creator_email, o.name AS organization_name,
                    (SELECT file_path FROM report_images WHERE report_id = r.id AND is_primary = 1 LIMIT 1) AS primary_image
             FROM reports r
             JOIN users u ON r.creator_id = u.id
             LEFT JOIN organizations o ON u.organization_id = o.id
             WHERE r.identifier = :identifier LIMIT 1",
            [':identifier' => trim($identifier)]
        );
        $report = $stmt->fetch();
        return $report ?: null;
    }

    public static function generateIdentifier(string $type): string
    {
        $prefix = (strtolower($type) === 'lost') ? 'TRC-L' : 'TRC-F';
        $year = date('Y');
        
        // Count reports this year
        $stmt = Database::query(
            "SELECT COUNT(*) AS cnt FROM reports WHERE identifier LIKE :prefix",
            [':prefix' => "{$prefix}-{$year}-%"]
        );
        $count = (int)($stmt->fetch()['cnt'] ?? 0) + 1;
        
        do {
            $identifier = sprintf('%s-%s-%05d', $prefix, $year, $count);
            $existing = Database::query(
                "SELECT id FROM reports WHERE identifier = :id",
                [':id' => $identifier]
            )->fetch();
            if (!$existing) {
                return $identifier;
            }
            $count++;
        } while (true);
    }

    public static function create(array $data): int
    {
        $identifier = self::generateIdentifier($data['report_type']);

        $sql = "INSERT INTO reports (
                    identifier, creator_id, report_type, item_title, category,
                    brand, color, description, general_location, latitude, longitude,
                    event_date, event_time, contact_preference, reward_amount,
                    status, expiration_date
                ) VALUES (
                    :identifier, :creator_id, :report_type, :item_title, :category,
                    :brand, :color, :description, :general_location, :latitude, :longitude,
                    :event_date, :event_time, :contact_preference, :reward_amount,
                    :status, :expiration_date
                )";

        Database::query($sql, [
            ':identifier' => $identifier,
            ':creator_id' => $data['creator_id'],
            ':report_type' => $data['report_type'],
            ':item_title' => $data['item_title'],
            ':category' => $data['category'],
            ':brand' => $data['brand'] ?? null,
            ':color' => $data['color'] ?? null,
            ':description' => $data['description'],
            ':general_location' => $data['general_location'],
            ':latitude' => $data['latitude'] ?? null,
            ':longitude' => $data['longitude'] ?? null,
            ':event_date' => $data['event_date'],
            ':event_time' => $data['event_time'] ?? null,
            ':contact_preference' => $data['contact_preference'] ?? 'in_app',
            ':reward_amount' => !empty($data['reward_amount']) ? (float)$data['reward_amount'] : null,
            ':status' => $data['status'] ?? 'active',
            ':expiration_date' => $data['expiration_date']
        ]);

        return (int)Database::getConnection()->lastInsertId();
    }

    public static function savePrivateDetails(int $reportId, array $data): bool
    {
        $sql = "INSERT INTO report_private_details (
                    report_id, custody_status, safe_collection_instructions,
                    secret_question, secret_answer, secret_notes
                ) VALUES (
                    :report_id, :custody_status, :instructions,
                    :question, :answer, :notes
                ) ON DUPLICATE KEY UPDATE
                    custody_status = VALUES(custody_status),
                    safe_collection_instructions = VALUES(safe_collection_instructions),
                    secret_question = VALUES(secret_question),
                    secret_answer = VALUES(secret_answer),
                    secret_notes = VALUES(secret_notes)";

        Database::query($sql, [
            ':report_id' => $reportId,
            ':custody_status' => $data['custody_status'] ?? null,
            ':instructions' => $data['safe_collection_instructions'] ?? null,
            ':question' => $data['secret_question'] ?? null,
            ':answer' => $data['secret_answer'] ?? null,
            ':notes' => $data['secret_notes'] ?? null,
        ]);
        return true;
    }

    public static function getPrivateDetails(int $reportId): ?array
    {
        $stmt = Database::query(
            "SELECT * FROM report_private_details WHERE report_id = :id LIMIT 1",
            [':id' => $reportId]
        );
        $details = $stmt->fetch();
        return $details ?: null;
    }

    public static function addImage(int $reportId, string $filePath, bool $isPrimary = false): bool
    {
        Database::query(
            "INSERT INTO report_images (report_id, file_path, is_primary) VALUES (:report_id, :file_path, :is_primary)",
            [
                ':report_id' => $reportId,
                ':file_path' => $filePath,
                ':is_primary' => $isPrimary ? 1 : 0
            ]
        );
        return true;
    }

    public static function getImages(int $reportId): array
    {
        $stmt = Database::query(
            "SELECT * FROM report_images WHERE report_id = :id ORDER BY is_primary DESC, id ASC",
            [':id' => $reportId]
        );
        return $stmt->fetchAll();
    }

    public static function search(array $filters, int $limit = 12, int $offset = 0): array
    {
        $where = ["r.status NOT IN ('closed', 'expired')"];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "r.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['type'])) {
            $where[] = "r.report_type = :type";
            $params[':type'] = $filters['type'];
        }

        if (!empty($filters['category'])) {
            $where[] = "r.category = :category";
            $params[':category'] = $filters['category'];
        }

        if (!empty($filters['brand'])) {
            $where[] = "LOWER(r.brand) LIKE :brand";
            $params[':brand'] = '%' . strtolower(trim($filters['brand'])) . '%';
        }

        if (!empty($filters['location'])) {
            $where[] = "LOWER(r.general_location) LIKE :loc";
            $params[':loc'] = '%' . strtolower(trim($filters['location'])) . '%';
        }

        if (!empty($filters['date_from'])) {
            $where[] = "r.event_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "r.event_date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        if (!empty($filters['q'])) {
            $where[] = "(LOWER(r.item_title) LIKE :q OR LOWER(r.description) LIKE :q OR LOWER(r.general_location) LIKE :q OR LOWER(r.brand) LIKE :q OR LOWER(r.identifier) LIKE :q)";
            $params[':q'] = '%' . strtolower(trim($filters['q'])) . '%';
        }

        $orderBy = match ($filters['sort'] ?? 'recent') {
            'oldest' => 'r.event_date ASC, r.created_at ASC',
            'title' => 'r.item_title ASC',
            default => 'r.created_at DESC'
        };

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT r.*, u.name AS creator_name,
                       (SELECT file_path FROM report_images WHERE report_id = r.id AND is_primary = 1 LIMIT 1) AS primary_image
                FROM reports r
                JOIN users u ON r.creator_id = u.id
                WHERE {$whereClause}
                ORDER BY {$orderBy}
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = Database::query($sql, $params);
        return $stmt->fetchAll();
    }

    public static function countSearch(array $filters): int
    {
        $where = ["r.status NOT IN ('closed', 'expired')"];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "r.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['type'])) {
            $where[] = "r.report_type = :type";
            $params[':type'] = $filters['type'];
        }

        if (!empty($filters['category'])) {
            $where[] = "r.category = :category";
            $params[':category'] = $filters['category'];
        }

        if (!empty($filters['brand'])) {
            $where[] = "LOWER(r.brand) LIKE :brand";
            $params[':brand'] = '%' . strtolower(trim($filters['brand'])) . '%';
        }

        if (!empty($filters['location'])) {
            $where[] = "LOWER(r.general_location) LIKE :loc";
            $params[':loc'] = '%' . strtolower(trim($filters['location'])) . '%';
        }

        if (!empty($filters['date_from'])) {
            $where[] = "r.event_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "r.event_date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        if (!empty($filters['q'])) {
            $where[] = "(LOWER(r.item_title) LIKE :q OR LOWER(r.description) LIKE :q OR LOWER(r.general_location) LIKE :q OR LOWER(r.brand) LIKE :q OR LOWER(r.identifier) LIKE :q)";
            $params[':q'] = '%' . strtolower(trim($filters['q'])) . '%';
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) AS total FROM reports r WHERE {$whereClause}";
        $stmt = Database::query($sql, $params);
        return (int)($stmt->fetch()['total'] ?? 0);
    }

    public static function getByUser(int $userId, int $limit = 20, int $offset = 0): array
    {
        $stmt = Database::query(
            "SELECT r.*,
                    (SELECT file_path FROM report_images WHERE report_id = r.id AND is_primary = 1 LIMIT 1) AS primary_image,
                    (SELECT COUNT(*) FROM match_suggestions m 
                     WHERE (m.lost_report_id = r.id OR m.found_report_id = r.id) 
                       AND m.status != 'dismissed') AS match_count,
                    (SELECT COUNT(*) FROM claims c WHERE c.report_id = r.id) AS claim_count
             FROM reports r
             WHERE r.creator_id = :user_id
             ORDER BY r.created_at DESC
             LIMIT {$limit} OFFSET {$offset}",
            [':user_id' => $userId]
        );
        return $stmt->fetchAll();
    }

    public static function countByUser(int $userId): int
    {
        $stmt = Database::query(
            "SELECT COUNT(*) AS total FROM reports WHERE creator_id = :user_id",
            [':user_id' => $userId]
        );
        return (int)($stmt->fetch()['total'] ?? 0);
    }

    public static function updateStatus(int $id, string $status): bool
    {
        Database::query("UPDATE reports SET status = :status WHERE id = :id", [
            ':status' => $status,
            ':id' => $id
        ]);
        return true;
    }

    public static function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];

        foreach (['item_title', 'category', 'brand', 'color', 'description', 'general_location', 'latitude', 'longitude', 'event_date', 'event_time', 'reward_amount', 'status'] as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "{$col} = :{$col}";
                $params[":{$col}"] = $data[$col];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE reports SET " . implode(', ', $fields) . " WHERE id = :id";
        Database::query($sql, $params);
        return true;
    }

    public static function getStats(): array
    {
        $stmt = Database::query("
            SELECT 
                COUNT(*) AS total_reports,
                SUM(CASE WHEN status = 'recovered' THEN 1 ELSE 0 END) AS recovered_items,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_reports,
                SUM(CASE WHEN report_type = 'lost' THEN 1 ELSE 0 END) AS lost_reports,
                SUM(CASE WHEN report_type = 'found' THEN 1 ELSE 0 END) AS found_reports
            FROM reports
        ");
        $data = $stmt->fetch();
        $total = (int)($data['total_reports'] ?? 0);
        $recovered = (int)($data['recovered_items'] ?? 0);
        $active = (int)($data['active_reports'] ?? 0);
        $rate = $total > 0 ? round(($recovered / $total) * 100, 1) : 0;

        return [
            'total' => $total,
            'recovered' => $recovered,
            'active' => $active,
            'lost' => (int)($data['lost_reports'] ?? 0),
            'found' => (int)($data['found_reports'] ?? 0),
            'recovery_rate' => $rate
        ];
    }

    public static function getRecentPublic(int $limit = 6): array
    {
        $stmt = Database::query(
            "SELECT r.*,
                    (SELECT file_path FROM report_images WHERE report_id = r.id AND is_primary = 1 LIMIT 1) AS primary_image
             FROM reports r
             WHERE r.status = 'active'
             ORDER BY r.created_at DESC
             LIMIT {$limit}"
        );
        return $stmt->fetchAll();
    }

    public static function getMapPoints(array $filters = []): array
    {
        $where = ["r.status = 'active'", "r.latitude IS NOT NULL", "r.longitude IS NOT NULL"];
        $params = [];

        if (!empty($filters['type'])) {
            $where[] = "r.report_type = :type";
            $params[':type'] = $filters['type'];
        }
        if (!empty($filters['category'])) {
            $where[] = "r.category = :category";
            $params[':category'] = $filters['category'];
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT r.id, r.identifier, r.report_type, r.item_title, r.category,
                       r.general_location, r.latitude, r.longitude, r.event_date,
                       (SELECT file_path FROM report_images WHERE report_id = r.id AND is_primary = 1 LIMIT 1) AS primary_image
                FROM reports r
                WHERE {$whereClause}
                LIMIT 200";

        $stmt = Database::query($sql, $params);
        return $stmt->fetchAll();
    }

    public static function getEligibleOpposites(string $type, ?string $category = null): array
    {
        $oppositeType = (strtolower($type) === 'lost') ? 'found' : 'lost';
        $where = ["report_type = :type", "status = 'active'"];
        $params = [':type' => $oppositeType];

        if ($category) {
            $where[] = "category = :category";
            $params[':category'] = $category;
        }

        $sql = "SELECT * FROM reports WHERE " . implode(' AND ', $where) . " ORDER BY created_at DESC LIMIT 100";
        $stmt = Database::query($sql, $params);
        return $stmt->fetchAll();
    }

    public static function getExpiringReports(string $cutoffDate, string $type): array
    {
        $stmt = Database::query(
            "SELECT * FROM reports 
             WHERE status = 'active' AND report_type = :type AND expiration_date <= :cutoff",
            [':type' => $type, ':cutoff' => $cutoffDate]
        );
        return $stmt->fetchAll();
    }
}
