<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Integration\StoreIntegrationRequest;
use App\Http\Requests\Integration\UpdateIntegrationRequest;
use App\Models\Integration;
use App\Services\Accounting\XeroService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class IntegrationController extends Controller
{
    public function index(Request $request): Response
    {
        $integrations = $request->user()
            ->integrations()
            ->latest()
            ->get();

        return Inertia::render('Integrations/Index', [
            'integrations' => $integrations,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Integrations/Create', [
            'providers' => [
                'xero' => [
                    'name' => 'Xero',
                    'description' => 'Connect to Xero accounting software',
                    'scopes' => 'accounting.transactions accounting.contacts accounting.settings offline_access',
                ],
                'sage' => [
                    'name' => 'Sage Business Cloud',
                    'description' => 'Connect to Sage Business Cloud accounting',
                    'scopes' => 'read_full_profile',
                ],
            ],
        ]);
    }

    public function store(StoreIntegrationRequest $request): RedirectResponse
    {
        $user = $request->user();
        $provider = $request->input('provider');

        if ($provider === 'xero') {
            return $this->connectXero($request, $user);
        }

        if ($provider === 'sage') {
            return $this->connectSage($request, $user);
        }

        return redirect()
            ->back()
            ->with('error', 'Unsupported integration provider.');
    }

    public function show(Integration $integration): Response
    {
        $this->authorize('view', $integration);

        $integration->load('user');

        return Inertia::render('Integrations/Show', [
            'integration' => $integration,
        ]);
    }

    public function edit(Integration $integration): Response
    {
        $this->authorize('update', $integration);

        return Inertia::render('Integrations/Edit', [
            'integration' => $integration,
        ]);
    }

    public function update(UpdateIntegrationRequest $request, Integration $integration): RedirectResponse
    {
        $this->authorize('update', $integration);

        $integration->update($request->validated());

        return redirect()
            ->route('integrations.index')
            ->with('success', 'Integration updated successfully.');
    }

    public function destroy(Integration $integration): RedirectResponse
    {
        $this->authorize('delete', $integration);

        $integration->delete();

        return redirect()
            ->route('integrations.index')
            ->with('success', 'Integration removed successfully.');
    }

    public function test(Integration $integration): JsonResponse
    {
        $this->authorize('update', $integration);

        try {
            $service = $this->getServiceForIntegration($integration);
            $result = $service->testConnection();

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Connection successful' : 'Connection failed',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ]);
        }
    }

    public function callback(Request $request): RedirectResponse
    {
        $provider = $request->input('provider');
        $code = $request->input('code');
        $state = $request->input('state');

        if (!$provider || !$code || !$state) {
            return redirect()
                ->route('integrations.index')
                ->with('error', 'Invalid OAuth callback.');
        }

        try {
            if ($provider === 'xero') {
                $this->handleXeroCallback($request);
            } elseif ($provider === 'sage') {
                $this->handleSageCallback($request);
            }

            return redirect()
                ->route('integrations.index')
                ->with('success', 'Integration connected successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->route('integrations.index')
                ->with('error', 'Failed to connect integration: ' . $e->getMessage());
        }
    }

    private function connectXero(Request $request, User $user): RedirectResponse
    {
        $state = Str::random(40);
        session(['xero_state' => $state, 'xero_tenant_id' => $user->tenant->id]);

        $params = [
            'response_type' => 'code',
            'client_id' => config('services.xero.client_id'),
            'redirect_uri' => route('integrations.callback') . '?provider=xero',
            'scope' => 'accounting.transactions accounting.contacts accounting.settings offline_access',
            'state' => $state,
        ];

        $url = 'https://login.xero.com/identity/connect/authorize?' . http_build_query($params);

        return redirect()->away($url);
    }

    private function connectSage(Request $request, User $user): RedirectResponse
    {
        $state = Str::random(40);
        session(['sage_state' => $state, 'sage_tenant_id' => $user->tenant->id]);

        $params = [
            'client_id' => config('services.sage.client_id'),
            'redirect_uri' => route('integrations.callback') . '?provider=sage',
            'response_type' => 'code',
            'scope' => 'read_full_profile',
            'state' => $state,
        ];

        $url = 'https://www.sageone.com/oauth2/auth/authorize?' . http_build_query($params);

        return redirect()->away($url);
    }

    private function handleXeroCallback(Request $request): void
    {
        $response = Http::asForm()->post('https://identity.xero.com/connect/token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.xero.client_id'),
            'client_secret' => config('services.xero.client_secret'),
            'code' => $request->input('code'),
            'redirect_uri' => route('integrations.callback') . '?provider=xero',
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to exchange code for token');
        }

        $tokenData = $response->json();
        
        Integration::create([
            'tenant_id' => session('xero_tenant_id'),
            'user_id' => auth()->id(),
            'type' => 'accounting',
            'provider' => 'xero',
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'],
            'expires_at' => now()->addSeconds($tokenData['expires_in']),
            'scope' => $tokenData['scope'],
            'is_active' => true,
            'settings' => [
                'auto_sync' => false,
                'revenue_account_code' => '200',
            ],
        ]);

        session()->forget(['xero_state', 'xero_tenant_id']);
    }

    private function handleSageCallback(Request $request): void
    {
        // Sage integration implementation would go here
        // For now, we'll create a placeholder integration
        Integration::create([
            'tenant_id' => session('sage_tenant_id'),
            'user_id' => auth()->id(),
            'type' => 'accounting',
            'provider' => 'sage',
            'access_token' => $request->input('code'),
            'refresh_token' => null,
            'expires_at' => now()->addYears(1),
            'scope' => 'read_full_profile',
            'is_active' => true,
            'settings' => [
                'auto_sync' => false,
            ],
        ]);

        session()->forget(['sage_state', 'sage_tenant_id']);
    }

    private function getServiceForIntegration(Integration $integration): mixed
    {
        if ($integration->isXero()) {
            return new XeroService($integration);
        }

        throw new \Exception('Unsupported integration provider');
    }
}