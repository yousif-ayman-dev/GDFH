<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\User;
use App\Services\MarketplaceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function __construct(
        protected MarketplaceService $marketplaceService
    ) {}

    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'services'); // 'services', 'freelancers', 'projects'

        $filters = [
            'search' => $request->query('search'),
            'category' => $request->query('category'),
            'min_price' => $request->query('min_price'),
            'max_price' => $request->query('max_price'),
            'min_rate' => $request->query('min_rate'),
            'max_rate' => $request->query('max_rate'),
        ];

        $services = $this->marketplaceService->getServices($filters);
        $freelancers = $this->marketplaceService->getFreelancers($filters);
        $projects = $this->marketplaceService->getPublicProjects($filters);

        return view('marketplace.index', compact(
            'tab',
            'filters',
            'services',
            'freelancers',
            'projects'
        ));
    }

    public function showService(Service $service): View
    {
        $service->load(['user.freelancerProfile']);

        return view('marketplace.services.show', compact('service'));
    }

    public function showFreelancer(User $user): View
    {
        $user->load(['freelancerProfile', 'services']);

        return view('marketplace.freelancers.show', compact('user'));
    }
}
