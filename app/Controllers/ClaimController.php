<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\ClaimRepository;
use App\Repositories\ReportRepository;
use App\Services\ClaimService;

class ClaimController
{
    public function create(Request $request, array $params): void
    {
        $reportId = (int)($params['report_id'] ?? 0);
        $report = ReportRepository::findById($reportId);

        if (!$report) {
            http_response_code(404);
            View::render('home.404', ['pageTitle' => 'Report Not Found']);
            return;
        }

        $userId = Session::getUserId();
        if ($userId === (int)$report['creator_id']) {
            Session::flash('error', 'You cannot claim your own reported item.');
            Response::redirect("/reports/{$reportId}");
        }

        $privateDetails = ReportRepository::getPrivateDetails($reportId);

        View::render('claims.create', [
            'pageTitle' => 'Claim Ownership &mdash; ' . htmlspecialchars($report['item_title']),
            'report' => $report,
            'privateDetails' => $privateDetails,
        ]);
    }

    public function store(Request $request): void
    {
        $userId = Session::getUserId();
        $data = $request->only(['report_id', 'claim_description', 'ownership_evidence']);

        $result = ClaimService::submitClaim($userId, $data);

        if (!$result['success']) {
            Session::flash('error', 'Please complete all required fields.');
            $report = ReportRepository::findById((int)($data['report_id'] ?? 0));
            $privateDetails = ReportRepository::getPrivateDetails((int)($data['report_id'] ?? 0));
            View::render('claims.create', [
                'pageTitle' => 'Claim Ownership &mdash; TRACE',
                'report' => $report,
                'privateDetails' => $privateDetails,
                'errors' => $result['errors'],
                'old' => $data,
            ]);
            return;
        }

        Session::flash('success', 'Your ownership claim has been submitted for verification.');
        Response::redirect("/claims/{$result['claim_id']}");
    }

    public function show(Request $request, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $claim = ClaimRepository::findById($id);

        if (!$claim) {
            http_response_code(404);
            View::render('home.404', ['pageTitle' => 'Claim Not Found']);
            return;
        }

        $userId = Session::getUserId();
        $isAdmin = Session::isAdmin();
        $isClaimant = ($userId === (int)$claim['claimant_id']);
        $isFinder = ($userId === (int)$claim['report_creator_id']);

        // Authorization check: Only claimant, finder, or administrator may view this claim
        if (!$isClaimant && !$isFinder && !$isAdmin) {
            Session::flash('error', 'Unauthorized access to claim records.');
            Response::redirect('/dashboard');
        }

        $privateDetails = null;
        if ($isFinder || $isAdmin) {
            $privateDetails = ReportRepository::getPrivateDetails((int)$claim['report_id']);
        }

        $history = ClaimRepository::getStatusHistory($id);

        View::render('claims.show', [
            'pageTitle' => 'Claim #' . $claim['id'] . ' &mdash; TRACE',
            'claim' => $claim,
            'isClaimant' => $isClaimant,
            'isFinder' => $isFinder,
            'isAdmin' => $isAdmin,
            'privateDetails' => $privateDetails,
            'history' => $history,
        ]);
    }

    public function review(Request $request, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $claim = ClaimRepository::findById($id);

        if (!$claim) {
            Response::redirect('/dashboard');
        }

        $userId = Session::getUserId();
        $isAdmin = Session::isAdmin();
        $isFinder = ($userId === (int)$claim['report_creator_id']);

        if (!$isFinder && !$isAdmin) {
            Session::flash('error', 'Unauthorized action.');
            Response::redirect("/claims/{$id}");
        }

        $action = (string)$request->input('action', '');
        $note = (string)$request->input('note', '');
        $instructions = (string)$request->input('return_instructions', '');

        $result = ClaimService::reviewClaim($userId, $id, $action, $note, $instructions);

        if (!$result['success']) {
            Session::flash('error', $result['error'] ?? 'Review action failed.');
        } else {
            Session::flash('success', "Claim status updated to {$result['status']}.");
        }

        Response::redirect("/claims/{$id}");
    }

    public function index(Request $request): void
    {
        $userId = Session::getUserId();
        $myClaims = ClaimRepository::getByClaimant($userId);
        $claimsOnMyItems = ClaimRepository::getByFinder($userId);

        View::render('claims.index', [
            'pageTitle' => 'Ownership Claims &mdash; TRACE',
            'myClaims' => $myClaims,
            'claimsOnMyItems' => $claimsOnMyItems,
        ]);
    }
}
