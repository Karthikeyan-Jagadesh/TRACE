<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class ClaimRepository
{
    public static function findById(int $id): ?array
    {
        $sql = "SELECT c.*,
                       r.identifier AS report_identifier, r.item_title AS report_title, r.category AS report_category,
                       r.general_location AS report_location, r.creator_id AS report_creator_id, r.status AS report_status,
                       r.report_type,
                       u.name AS claimant_name, u.email AS claimant_email,
                       rev.name AS reviewer_name,
                       (SELECT file_path FROM report_images WHERE report_id = r.id AND is_primary = 1 LIMIT 1) AS report_image
                FROM claims c
                JOIN reports r ON c.report_id = r.id
                JOIN users u ON c.claimant_id = u.id
                LEFT JOIN users rev ON c.reviewer_id = rev.id
                WHERE c.id = :id LIMIT 1";

        $stmt = Database::query($sql, [':id' => $id]);
        $claim = $stmt->fetch();
        return $claim ?: null;
    }

    public static function create(array $data): int
    {
        return Database::transaction(function (PDO $pdo) use ($data) {
            $sql = "INSERT INTO claims (
                        report_id, claimant_id, status, claim_description,
                        ownership_evidence
                    ) VALUES (
                        :report_id, :claimant_id, 'submitted', :desc, :evidence
                    )";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':report_id' => $data['report_id'],
                ':claimant_id' => $data['claimant_id'],
                ':desc' => $data['claim_description'],
                ':evidence' => $data['ownership_evidence'],
            ]);

            $claimId = (int)$pdo->lastInsertId();

            // Record initial history
            $histSql = "INSERT INTO claim_status_history (claim_id, old_status, new_status, note, changed_by)
                        VALUES (:cid, NULL, 'submitted', 'Claim submitted by claimant', :by)";
            $histStmt = $pdo->prepare($histSql);
            $histStmt->execute([':cid' => $claimId, ':by' => $data['claimant_id']]);

            // Update report status to under_review if currently active
            $updReport = $pdo->prepare("UPDATE reports SET status = 'under_review' WHERE id = :rid AND status = 'active'");
            $updReport->execute([':rid' => $data['report_id']]);

            return $claimId;
        });
    }

    public static function updateStatus(
        int $claimId,
        string $newStatus,
        int $changedBy,
        ?string $note = null,
        ?string $returnInstructions = null
    ): bool {
        return Database::transaction(function (PDO $pdo) use ($claimId, $newStatus, $changedBy, $note, $returnInstructions) {
            // Fetch current claim
            $stmt = $pdo->prepare("SELECT * FROM claims WHERE id = :id FOR UPDATE");
            $stmt->execute([':id' => $claimId]);
            $claim = $stmt->fetch();
            if (!$claim) {
                return false;
            }

            $oldStatus = $claim['status'];

            // Update claim
            $sql = "UPDATE claims SET 
                        status = :status, 
                        reviewer_id = :reviewer_id,
                        verification_notes = COALESCE(:notes, verification_notes),
                        return_instructions = COALESCE(:instr, return_instructions),
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id";

            $updStmt = $pdo->prepare($sql);
            $updStmt->execute([
                ':status' => $newStatus,
                ':reviewer_id' => $changedBy,
                ':notes' => $note,
                ':instr' => $returnInstructions,
                ':id' => $claimId
            ]);

            // Record history
            $histStmt = $pdo->prepare(
                "INSERT INTO claim_status_history (claim_id, old_status, new_status, note, changed_by)
                 VALUES (:cid, :old_s, :new_s, :note, :by)"
            );
            $histStmt->execute([
                ':cid' => $claimId,
                ':old_s' => $oldStatus,
                ':new_s' => $newStatus,
                ':note' => $note,
                ':by' => $changedBy
            ]);

            // If completed or approved, update parent report status appropriately
            if ($newStatus === 'completed') {
                $updRep = $pdo->prepare("UPDATE reports SET status = 'recovered' WHERE id = :rid");
                $updRep->execute([':rid' => $claim['report_id']]);
            } elseif ($newStatus === 'approved') {
                $updRep = $pdo->prepare("UPDATE reports SET status = 'claimed' WHERE id = :rid");
                $updRep->execute([':rid' => $claim['report_id']]);
            } elseif (in_array($newStatus, ['rejected', 'cancelled'])) {
                // If no other pending/approved claims remain on this report, set report back to active
                $checkOther = $pdo->prepare("SELECT COUNT(*) AS cnt FROM claims WHERE report_id = :rid AND status IN ('submitted', 'under_review', 'approved') AND id != :cid");
                $checkOther->execute([':rid' => $claim['report_id'], ':cid' => $claimId]);
                if ((int)$checkOther->fetch()['cnt'] === 0) {
                    $updRep = $pdo->prepare("UPDATE reports SET status = 'active' WHERE id = :rid");
                    $updRep->execute([':rid' => $claim['report_id']]);
                }
            }

            return true;
        });
    }

    public static function getByClaimant(int $claimantId): array
    {
        $sql = "SELECT c.*,
                       r.identifier AS report_identifier, r.item_title AS report_title, r.category AS report_category,
                       r.general_location AS report_location, r.report_type,
                       (SELECT file_path FROM report_images WHERE report_id = r.id AND is_primary = 1 LIMIT 1) AS report_image
                FROM claims c
                JOIN reports r ON c.report_id = r.id
                WHERE c.claimant_id = :cid
                ORDER BY c.created_at DESC";

        $stmt = Database::query($sql, [':cid' => $claimantId]);
        return $stmt->fetchAll();
    }

    public static function getByFinder(int $finderId): array
    {
        $sql = "SELECT c.*,
                       r.identifier AS report_identifier, r.item_title AS report_title, r.category AS report_category,
                       r.general_location AS report_location,
                       u.name AS claimant_name, u.email AS claimant_email,
                       (SELECT file_path FROM report_images WHERE report_id = r.id AND is_primary = 1 LIMIT 1) AS report_image
                FROM claims c
                JOIN reports r ON c.report_id = r.id
                JOIN users u ON c.claimant_id = u.id
                WHERE r.creator_id = :fid
                ORDER BY c.created_at DESC";

        $stmt = Database::query($sql, [':fid' => $finderId]);
        return $stmt->fetchAll();
    }

    public static function getByReport(int $reportId): array
    {
        $sql = "SELECT c.*, u.name AS claimant_name, u.email AS claimant_email
                FROM claims c
                JOIN users u ON c.claimant_id = u.id
                WHERE c.report_id = :rid
                ORDER BY c.created_at DESC";

        $stmt = Database::query($sql, [':rid' => $reportId]);
        return $stmt->fetchAll();
    }

    public static function countPendingForUser(int $userId): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM claims c
                JOIN reports r ON c.report_id = r.id
                WHERE (c.claimant_id = :uid1 OR r.creator_id = :uid2)
                  AND c.status IN ('submitted', 'under_review', 'info_requested')";

        $stmt = Database::query($sql, [':uid1' => $userId, ':uid2' => $userId]);
        return (int)($stmt->fetch()['total'] ?? 0);
    }

    public static function getStatusHistory(int $claimId): array
    {
        $sql = "SELECT h.*, u.name AS changer_name
                FROM claim_status_history h
                JOIN users u ON h.changed_by = u.id
                WHERE h.claim_id = :cid
                ORDER BY h.created_at ASC";

        $stmt = Database::query($sql, [':cid' => $claimId]);
        return $stmt->fetchAll();
    }

    public static function getAll(int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT c.*,
                       r.identifier AS report_identifier, r.item_title AS report_title, r.report_type,
                       u.name AS claimant_name, u.email AS claimant_email,
                       f.name AS finder_name
                FROM claims c
                JOIN reports r ON c.report_id = r.id
                JOIN users u ON c.claimant_id = u.id
                JOIN users f ON r.creator_id = f.id
                ORDER BY c.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = Database::query($sql);
        return $stmt->fetchAll();
    }
}
