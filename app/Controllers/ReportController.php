<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\ClaimRepository;
use App\Repositories\MatchRepository;
use App\Repositories\ReportRepository;
use App\Services\ReportService;

class ReportController
{
    public function createLost(Request $request): void
    {
        View::render('reports.create_lost', [
            'pageTitle' => 'Report a Lost Item &mdash; TRACE',
            'categories' => self::getCategories()
        ]);
    }

    public function storeLost(Request $request): void
    {
        $userId = Session::getUserId();
        $data = $request->all();
        $data['report_type'] = 'lost';

        $file = $request->file('image');
        $result = ReportService::createReport($userId, $data, $file);

        if (!$result['success']) {
            Session::flash('error', 'Please resolve the highlighted errors below.');
            View::render('reports.create_lost', [
                'pageTitle' => 'Report a Lost Item &mdash; TRACE',
                'errors' => $result['errors'],
                'old' => $data,
                'categories' => self::getCategories()
            ]);
            return;
        }

        Session::flash('success', "Lost item report #{$result['report']['identifier']} registered successfully.");
        Response::redirect("/reports/{$result['report']['id']}/confirmation");
    }

    public function createFound(Request $request): void
    {
        View::render('reports.create_found', [
            'pageTitle' => 'Report a Found Item &mdash; TRACE',
            'categories' => self::getCategories()
        ]);
    }

    public function storeFound(Request $request): void
    {
        $userId = Session::getUserId();
        $data = $request->all();
        $data['report_type'] = 'found';

        $file = $request->file('image');
        $result = ReportService::createReport($userId, $data, $file);

        if (!$result['success']) {
            Session::flash('error', 'Please resolve the highlighted errors below.');
            View::render('reports.create_found', [
                'pageTitle' => 'Report a Found Item &mdash; TRACE',
                'errors' => $result['errors'],
                'old' => $data,
                'categories' => self::getCategories()
            ]);
            return;
        }

        Session::flash('success', "Found item report #{$result['report']['identifier']} registered successfully.");
        Response::redirect("/reports/{$result['report']['id']}/confirmation");
    }

    public function show(Request $request, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $report = ReportRepository::findById($id);

        if (!$report) {
            http_response_code(404);
            View::render('home.404', ['pageTitle' => 'Report Not Found &mdash; TRACE']);
            return;
        }

        $userId = Session::getUserId();
        $isOwner = ($userId !== null && $userId === (int)$report['creator_id']);
        $isAdmin = Session::isAdmin();

        $images = ReportRepository::getImages($id);
        $matches = [];
        $claims = [];
        $privateDetails = null;

        // Only owner or admin may view private details, matches, and claims
        if ($isOwner || $isAdmin) {
            $privateDetails = ReportRepository::getPrivateDetails($id);
            $matches = MatchRepository::findMatchesForReport($id);
            $claims = ClaimRepository::getByReport($id);
        }

        // Check if current user has already submitted a claim on this report
        $userHasClaimed = false;
        if ($userId && !$isOwner) {
            $myClaims = ClaimRepository::getByClaimant($userId);
            foreach ($myClaims as $c) {
                if ((int)$c['report_id'] === $id) {
                    $userHasClaimed = true;
                    break;
                }
            }
        }

        View::render('reports.show', [
            'pageTitle' => htmlspecialchars($report['item_title']) . ' &mdash; TRACE',
            'report' => $report,
            'images' => $images,
            'isOwner' => $isOwner,
            'isAdmin' => $isAdmin,
            'privateDetails' => $privateDetails,
            'matches' => $matches,
            'claims' => $claims,
            'userHasClaimed' => $userHasClaimed,
        ]);
    }

    public function confirmation(Request $request, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $report = ReportRepository::findById($id);

        if (!$report) {
            Response::redirect('/dashboard');
        }

        $matches = MatchRepository::findMatchesForReport($id);

        View::render('reports.confirmation', [
            'pageTitle' => 'Report Confirmation &mdash; TRACE',
            'report' => $report,
            'matches' => $matches,
        ]);
    }

    public static function getCategories(): array
    {
        return [
            'Electronics',
            'Identity Documents',
            'Bags and Accessories',
            'Books and Stationery',
            'Clothing',
            'Keys',
            'Jewelry',
            'Water Bottles',
            'Other'
        ];
    }
}
