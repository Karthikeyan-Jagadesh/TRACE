<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\MatchRepository;

class MatchController
{
    public function index(Request $request): void
    {
        $userId = Session::getUserId();
        $matches = MatchRepository::getPendingMatchesForUser($userId, 30);

        View::render('matches.index', [
            'pageTitle' => 'Potential Intelligence Matches &mdash; TRACE',
            'matches' => $matches,
        ]);
    }

    public function updateStatus(Request $request, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $status = (string)$request->input('status', '');

        if (!in_array($status, ['relevant', 'dismissed'], true)) {
            Response::json(['success' => false, 'error' => 'Invalid status'], 400);
        }

        MatchRepository::updateStatus($id, $status);

        if ($request->isAjax()) {
            Response::json(['success' => true]);
        } else {
            Session::flash('info', "Match marked as {$status}.");
            Response::back();
        }
    }
}
