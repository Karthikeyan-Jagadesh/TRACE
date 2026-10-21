<?php
namespace App\Repositories;

use App\Core\Database;

class MatchRepository
{
    public static function findMatchesForReport(int $reportId): array
    {
        $sql = "SELECT m.*, 
                       lr.identifier AS lost_identifier, lr.item_title AS lost_title, lr.category AS lost_category,
                       lr.general_location AS lost_location, lr.event_date AS lost_date,
                       (SELECT file_path FROM report_images WHERE report_id = lr.id AND is_primary = 1 LIMIT 1) AS lost_image,
                       fr.identifier AS found_identifier, fr.item_title AS found_title, fr.category AS found_category,
                       fr.general_location AS found_location, fr.event_date AS found_date,
                       (SELECT file_path FROM report_images WHERE report_id = fr.id AND is_primary = 1 LIMIT 1) AS found_image
                FROM match_suggestions m
                JOIN reports lr ON m.lost_report_id = lr.id
                JOIN reports fr ON m.found_report_id = fr.id
                WHERE (m.lost_report_id = :id1 OR m.found_report_id = :id2)
                  AND m.status != 'dismissed'
                ORDER BY m.score DESC, m.created_at DESC";

        $stmt = Database::query($sql, [':id1' => $reportId, ':id2' => $reportId]);
        return $stmt->fetchAll();
    }

    public static function findMatch(int $lostReportId, int $foundReportId): ?array
    {
        $stmt = Database::query(
            "SELECT * FROM match_suggestions 
             WHERE lost_report_id = :l_id AND found_report_id = :f_id LIMIT 1",
            [':l_id' => $lostReportId, ':f_id' => $foundReportId]
        );
        $match = $stmt->fetch();
        return $match ?: null;
    }

    public static function saveMatch(int $lostReportId, int $foundReportId, int $score, string $confidenceBand, string $explanation): bool
    {
        $sql = "INSERT INTO match_suggestions (
                    lost_report_id, found_report_id, score, confidence_band, explanation, status
                ) VALUES (
                    :lost_id, :found_id, :score, :band, :explanation, 'unreviewed'
                ) ON DUPLICATE KEY UPDATE
                    score = VALUES(score),
                    confidence_band = VALUES(confidence_band),
                    explanation = VALUES(explanation),
                    updated_at = CURRENT_TIMESTAMP";

        Database::query($sql, [
            ':lost_id' => $lostReportId,
            ':found_id' => $foundReportId,
            ':score' => $score,
            ':band' => $confidenceBand,
            ':explanation' => $explanation,
        ]);
        return true;
    }

    public static function updateStatus(int $id, string $status): bool
    {
        Database::query("UPDATE match_suggestions SET status = :status WHERE id = :id", [
            ':status' => $status,
            ':id' => $id
        ]);
        return true;
    }

    public static function getPendingMatchesForUser(int $userId, int $limit = 10): array
    {
        $sql = "SELECT m.*,
                       lr.identifier AS lost_identifier, lr.item_title AS lost_title, lr.general_location AS lost_location,
                       lr.event_date AS lost_date, lr.creator_id AS lost_creator_id,
                       fr.identifier AS found_identifier, fr.item_title AS found_title, fr.general_location AS found_location,
                       fr.event_date AS found_date, fr.creator_id AS found_creator_id,
                       (SELECT file_path FROM report_images WHERE report_id = lr.id AND is_primary = 1 LIMIT 1) AS lost_image,
                       (SELECT file_path FROM report_images WHERE report_id = fr.id AND is_primary = 1 LIMIT 1) AS found_image
                FROM match_suggestions m
                JOIN reports lr ON m.lost_report_id = lr.id
                JOIN reports fr ON m.found_report_id = fr.id
                WHERE (lr.creator_id = :uid1 OR fr.creator_id = :uid2)
                  AND m.status = 'unreviewed'
                  AND lr.status = 'active'
                  AND fr.status = 'active'
                ORDER BY m.score DESC, m.created_at DESC
                LIMIT {$limit}";

        $stmt = Database::query($sql, [':uid1' => $userId, ':uid2' => $userId]);
        return $stmt->fetchAll();
    }

    public static function countPendingMatchesForUser(int $userId): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM match_suggestions m
                JOIN reports lr ON m.lost_report_id = lr.id
                JOIN reports fr ON m.found_report_id = fr.id
                WHERE (lr.creator_id = :uid1 OR fr.creator_id = :uid2)
                  AND m.status = 'unreviewed'
                  AND lr.status = 'active'
                  AND fr.status = 'active'";

        $stmt = Database::query($sql, [':uid1' => $userId, ':uid2' => $userId]);
        return (int)($stmt->fetch()['total'] ?? 0);
    }
}
