<?php
namespace App\Services;

use App\Repositories\AuditLogRepository;
use App\Repositories\ClaimRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\ReportRepository;
use App\Validators\Validator;

class ClaimService
{
    public static function submitClaim(int $claimantId, array $data): array
    {
        $validator = Validator::make($data, [
            'report_id' => 'required|numeric',
            'claim_description' => 'required|min:10|max:2000',
            'ownership_evidence' => 'required|min:10|max:2000',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors()];
        }

        $report = ReportRepository::findById((int)$data['report_id']);
        if (!$report) {
            return ['success' => false, 'errors' => ['report_id' => ['Report not found.']]];
        }

        if ($report['creator_id'] === $claimantId) {
            return ['success' => false, 'errors' => ['claim' => ['You cannot claim an item you reported.']]];
        }

        if (in_array($report['status'], ['recovered', 'closed'])) {
            return ['success' => false, 'errors' => ['claim' => ['This report is already recovered or closed.']]];
        }

        $claimId = ClaimRepository::create([
            'report_id' => $report['id'],
            'claimant_id' => $claimantId,
            'claim_description' => trim($data['claim_description']),
            'ownership_evidence' => trim($data['ownership_evidence']),
        ]);

        // Notify finder/creator of the report
        NotificationRepository::create(
            $report['creator_id'],
            'claim_submitted',
            'New Ownership Claim Submitted',
            "A user has submitted an ownership claim on your found item '{$report['item_title']}'.",
            "/claims/{$claimId}"
        );

        AuditLogRepository::log(
            $claimantId,
            'claim_submitted',
            'claims',
            $claimId,
            "Submitted claim for report #{$report['identifier']}"
        );

        return ['success' => true, 'claim_id' => $claimId];
    }

    public static function reviewClaim(int $reviewerId, int $claimId, string $action, ?string $note, ?string $returnInstructions = null): array
    {
        $claim = ClaimRepository::findById($claimId);
        if (!$claim) {
            return ['success' => false, 'error' => 'Claim not found.'];
        }

        $validActions = ['approve', 'request_info', 'reject', 'complete'];
        if (!in_array($action, $validActions, true)) {
            return ['success' => false, 'error' => 'Invalid claim action.'];
        }

        $newStatus = match ($action) {
            'approve' => 'approved',
            'request_info' => 'info_requested',
            'reject' => 'rejected',
            'complete' => 'completed',
        };

        ClaimRepository::updateStatus(
            $claimId,
            $newStatus,
            $reviewerId,
            $note,
            $returnInstructions
        );

        // Notify claimant
        $notifMsg = match ($newStatus) {
            'approved' => "Your claim for '{$claim['report_title']}' has been approved! Safe collection instructions have been provided.",
            'info_requested' => "Additional ownership details requested for your claim on '{$claim['report_title']}'.",
            'rejected' => "Your claim for '{$claim['report_title']}' was reviewed and could not be verified.",
            'completed' => "Recovery confirmed! '{$claim['report_title']}' has been safely returned to you.",
        };

        NotificationRepository::create(
            $claim['claimant_id'],
            'claim_' . $newStatus,
            'Claim Update: ' . ucfirst(str_replace('_', ' ', $newStatus)),
            $notifMsg,
            "/claims/{$claimId}"
        );

        AuditLogRepository::log(
            $reviewerId,
            'claim_' . $newStatus,
            'claims',
            $claimId,
            "Claim status changed to {$newStatus}"
        );

        return ['success' => true, 'status' => $newStatus];
    }
}
