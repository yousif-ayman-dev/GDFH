<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(
        protected SearchService $searchService
    ) {}

    public function index(Request $request): View
    {
        $term = $request->query('q', '');
        $type = $request->query('type', 'all'); // 'all', 'projects', 'tasks', 'teams', 'services', 'freelancers'

        $searchData = $this->searchService->search(Auth::user(), $term, $type);

        return view('search.index', [
            'query' => $searchData['query'],
            'type' => $searchData['type'],
            'counts' => $searchData['counts'],
            'results' => $searchData['results'],
        ]);
    }
}
