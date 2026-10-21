<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Repositories\ReportRepository;

class SearchController
{
    public function index(Request $request): void
    {
        $filters = [
            'q' => $request->input('q', ''),
            'type' => $request->input('type', ''),
            'category' => $request->input('category', ''),
            'brand' => $request->input('brand', ''),
            'location' => $request->input('location', ''),
            'date_from' => $request->input('date_from', ''),
            'date_to' => $request->input('date_to', ''),
            'status' => $request->input('status', ''),
            'sort' => $request->input('sort', 'recent'),
        ];

        $page = max(1, (int)$request->input('page', 1));
        $limit = 12;
        $offset = ($page - 1) * $limit;

        $reports = ReportRepository::search($filters, $limit, $offset);
        $total = ReportRepository::countSearch($filters);
        $totalPages = max(1, (int)ceil($total / $limit));

        View::render('search.index', [
            'pageTitle' => 'Discover Reports &mdash; TRACE',
            'reports' => $reports,
            'filters' => $filters,
            'total' => $total,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'categories' => ReportController::getCategories(),
        ]);
    }

    public function map(Request $request): void
    {
        $filters = [
            'type' => $request->input('type', ''),
            'category' => $request->input('category', '')
        ];
        $points = ReportRepository::getMapPoints($filters);

        View::render('search.map', [
            'pageTitle' => 'Spatial Intelligence Map &mdash; TRACE',
            'points' => $points,
            'filters' => $filters,
            'categories' => ReportController::getCategories()
        ]);
    }

    public function mapData(Request $request): void
    {
        $filters = [
            'type' => $request->input('type', ''),
            'category' => $request->input('category', '')
        ];
        $points = ReportRepository::getMapPoints($filters);
        Response::json(['points' => $points]);
    }
}
