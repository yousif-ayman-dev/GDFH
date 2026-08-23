<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateFreelancerProfileRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use App\Models\User;
use App\Services\MarketplaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function createService(): View
    {
        $this->authorize('create', Service::class);

        return view('marketplace.services.create');
    }

    public function storeService(StoreServiceRequest $request): RedirectResponse
    {
        $this->authorize('create', Service::class);

        $service = $this->marketplaceService->createService(
            Auth::user(),
            $request->validated(),
            $request->file('cover_image')
        );

        return redirect()->route('marketplace.services.show', $service)
            ->with('success', 'تم إضافة الخدمة الجديدة إلى السوق بنجاح!');
    }

    public function editService(Service $service): View
    {
        $this->authorize('update', $service);

        return view('marketplace.services.edit', compact('service'));
    }

    public function updateService(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $this->authorize('update', $service);

        $this->marketplaceService->updateService(
            $service,
            $request->validated(),
            $request->file('cover_image')
        );

        return redirect()->route('marketplace.services.show', $service)
            ->with('success', 'تم تحديث بيانات الخدمة بنجاح!');
    }

    public function destroyService(Service $service): RedirectResponse
    {
        $this->authorize('delete', $service);

        $this->marketplaceService->deleteService($service);

        return redirect()->route('marketplace.index', ['tab' => 'services'])
            ->with('success', 'تم حذف الخدمة من السوق بنجاح.');
    }

    public function showFreelancer(User $user): View
    {
        $user->load(['freelancerProfile', 'services']);

        return view('marketplace.freelancers.show', compact('user'));
    }

    public function editFreelancerProfile(): View
    {
        $user = Auth::user();
        $user->load('freelancerProfile');

        return view('marketplace.freelancers.edit', compact('user'));
    }

    public function updateFreelancerProfile(UpdateFreelancerProfileRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $this->marketplaceService->updateFreelancerProfile($user, $request->validated());

        return redirect()->route('marketplace.freelancers.show', $user)
            ->with('success', 'تم تحديث بروفايل المستقل الخاص بك بنجاح!');
    }

    public function orderService(Request $request, Service $service): RedirectResponse
    {
        $client = Auth::user();

        try {
            $contract = $this->marketplaceService->orderService($client, $service);

            return redirect()->route('contracts.show', $contract)
                ->with('success', 'تم إنشاء عقد وتأكيد طلب الخدمة بنجاح!');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['order' => $e->getMessage()]);
        }
    }
}
