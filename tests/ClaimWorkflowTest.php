<?php
/**
 * TRACE — Claim Workflow & Handover State Machine Tests
 * Run via: php tests/ClaimWorkflowTest.php
 */

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

use App\Repositories\ClaimRepository;
use App\Repositories\ReportRepository;
use App\Repositories\UserRepository;
use App\Services\ClaimService;
use App\Services\ReportService;

class ClaimWorkflowTest
{
    private int $passed = 0;
    private int $failed = 0;

    public function run(): void
    {
        echo "===============================================\n";
        echo " RUNNING CLAIM & RECOVERY WORKFLOW TESTS       \n";
        echo "===============================================\n";

        $this->testCompleteClaimLifecycle();

        echo "\n-----------------------------------------------\n";
        echo "Tests Passed: {$this->passed} | Failed: {$this->failed}\n";
        if ($this->failed > 0) {
            echo "FAILED: Some claim workflow tests failed.\n";
            exit(1);
        } else {
            echo "SUCCESS: All claim workflow tests passed 100%!\n";
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

    private function testCompleteClaimLifecycle(): void
    {
        // 1. Setup Finder and Claimant users
        $finder = UserRepository::findByEmail('elena.rostova@campus.edu');
        $claimant = UserRepository::findByEmail('alex.morgan@campus.edu');

        $this->assert($finder && $claimant, "Found test users for claim lifecycle");

        // 2. Create a found report by finder
        $reportRes = ReportService::createReport($finder['id'], [
            'report_type' => 'found',
            'item_title' => 'Anker PowerBank 20000mAh',
            'category' => 'Electronics',
            'brand' => 'Anker',
            'color' => 'Black',
            'description' => 'Black rectangular powerbank left on the charging station table',
            'general_location' => 'Engineering Block B Lobby',
            'event_date' => date('Y-m-d'),
            'custody_status' => 'Held by Finder',
            'secret_question' => 'What is the serial number prefix or port color?',
            'secret_answer' => 'Blue USB 3.0 ports with minor dent on corner'
        ]);

        $this->assert($reportRes['success'] === true, "Found report created with private verification question");
        $reportId = $reportRes['report']['id'];

        // Verify initial report status
        $report = ReportRepository::findById($reportId);
        $this->assert($report['status'] === 'active', "Initial report status is 'active'");

        // 3. Claimant submits claim with ownership evidence
        $claimRes = ClaimService::submitClaim($claimant['id'], [
            'report_id' => $reportId,
            'claim_description' => 'I was charging my phone at the lobby table before my 11am lecture and forgot it.',
            'ownership_evidence' => 'It has two blue USB 3.0 ports and a small dent on the upper corner. I have the receipt.',
        ]);

        $this->assert($claimRes['success'] === true, "Claimant successfully submits ownership claim");
        $claimId = $claimRes['claim_id'];

        // Verify claim initial state & report update
        $claim = ClaimRepository::findById($claimId);
        $this->assert($claim['status'] === 'submitted', "Initial claim status is 'submitted'");

        $reportAfterClaim = ReportRepository::findById($reportId);
        $this->assert($reportAfterClaim['status'] === 'under_review', "Parent report status transitioned to 'under_review'");

        // 4. Finder reviews and approves claim
        $reviewRes = ClaimService::reviewClaim(
            $finder['id'],
            $claimId,
            'approve',
            'Evidence verified. Claimant correctly identified the blue USB ports and corner dent.',
            'Meet me at Engineering Block B reception tomorrow at 11am.'
        );

        $this->assert($reviewRes['success'] === true, "Finder approves claim successfully");
        $claimAfterApprove = ClaimRepository::findById($claimId);
        $this->assert($claimAfterApprove['status'] === 'approved', "Claim status transitioned to 'approved'");

        $reportAfterApprove = ReportRepository::findById($reportId);
        $this->assert($reportAfterApprove['status'] === 'claimed', "Parent report status transitioned to 'claimed'");

        // 5. Handover confirmation -> Complete Recovery
        $completeRes = ClaimService::reviewClaim(
            $claimant['id'],
            $claimId,
            'complete',
            'Claimant received the Anker powerbank safely from finder.'
        );

        $this->assert($completeRes['success'] === true, "Handover completed successfully");
        $claimAfterComplete = ClaimRepository::findById($claimId);
        $this->assert($claimAfterComplete['status'] === 'completed', "Claim status transitioned to 'completed'");

        $reportAfterComplete = ReportRepository::findById($reportId);
        $this->assert($reportAfterComplete['status'] === 'recovered', "Parent report status transitioned to 'recovered'");

        // 6. Check state history audit trail
        $history = ClaimRepository::getStatusHistory($claimId);
        $this->assert(count($history) >= 3, "Claim audit history records state transitions: submitted -> approved -> completed");
    }
}

$test = new ClaimWorkflowTest();
$test->run();
