<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Integration\StoreIntegrationRequest;
use App\Http\Requests\Integration\UpdateIntegrationRequest;
use App\Models\Integration;
use App\Services\Accounting\XeroService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IntegrationController extends Controller
{
    public function index(Request $request): Response
    {
        $integrations = $request->user()
            ->integrations()
            ->with(['user'])
            ->latest()
            ->get();

        return Inertia::render('Integrations/Index', [
            'integrations' => $integrations,
            'providers' => [
                'xero' => [
                    'name' => 'Xero',
                    'description' => 'Connect to Xero accounting software for automated invoice management.',
                ],
                'sage' => [
                    'name' => 'Sage Business Cloud',
                    'description' => 'Connect to Sage Business Cloud accounting software.',
                ],
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Integrations/Create', [
            'providers' => [
                'xero' => [
                    'name' => 'Xero',
                    'description' => 'Connect to Xero accounting software for automated invoice management.',
                ],
                'sage' => [
                    'name' => 'Sage Business Cloud',
                    'description' => 'Connect to Sage Business Cloud accounting software.',
                ],
            ],
        ]);
    }

    public function store(StoreIntegrationRequest $request): RedirectResponse
    {
        $integration = $request->user()->integrations()->create($request->validated());

        if ($integration->provider === 'xero') {
            return redirect()->route('integrations.xero.callback');
        }

        if ($integration->provider === 'sage') {
            return redirect()->route('integrations.sage.callback');
        }

        return redirect()
            ->route('integrations.index')
            ->with('success', 'Integration created successfully.');
    }

    public function xeroCallback(Request $request): RedirectResponse
    {
        // Handle Xero OAuth callback
        $code = $request->input('code');
        $state = $request->input('state');

        // Exchange code for access token
        $response = Http::asForm()->post('https://identity.xero.com/connect/token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.xero.client_id'),
            'client_secret' => config('services.xero.client_secret'),
            'redirect_uri' => route('integrations.xero.callback').'?provider=xero',
            'code' => $code,
        ]);

        if (! $response->successful()) {
            return redirect()
                ->route('integrations.index')
                ->with('error', 'Failed to connect to Xero.');
        }

        $tokenData = $response->json();

        $integration = Integration::where('provider', 'xero')->where('user_id', $request->user()->id)->first();
        if ($integration) {
            $integration->update([
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'],
                'expires_at' => now()->addSeconds($tokenData['expires_in']),
                'is_active' => true,
            ]);
        } else {
            $integration = Integration::create([
                'tenant_id' => $request->user()->tenant_id,
                'user_id' => $request->user()->id,
                'provider' => 'xero',
                'type' => 'accounting',
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'],
                'expires_at' => now()->addSeconds($tokenData['expires_in']),
                'is_active' => true,
                'settings' => [
                    'auto_sync' => false,
                    'revenue_account_code' => '200',
                ],
            ]);
        }

        return redirect()
            ->route('integrations.index')
            ->with('success', 'Xero integration connected successfully.');
    }

    public function sageCallback(Request $request): RedirectResponse
    {
        // Handle Sage OAuth callback (placeholder implementation)
        $code = $request->input('code');
        $state = $request->input('state');

        // For now, create a placeholder integration
        $integration = Integration::create([
            'tenant_id' => $request->user()->tenant_id,
            'user_id' => $request->user()->id,
            'provider' => 'sage',
            'type' => 'accounting',
            'access_token' => 'demo_sage_token',
            'is_active' => true,
            'settings' => [
                'auto_sync' => false,
            ],
        ]);

        return redirect()
            ->route('integrations.index')
            ->with('success', 'Sage integration connected successfully.');
    }

    public function show(Integration $integration): Response
    {
        $this->authorize('view', $integration);

        $integration->load(['user']);

        return Inertia::render('Integrations/Show', [
            'integration' => $integration,
        ]);
    }

    public function edit(Integration $integration): Response
    {
        $this->authorize('update', $integration);

        $integration->load(['user']);

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
            $service = match ($integration->provider) {
                'xero' => new XeroService($integration),
                'sage' => new class {}, // Placeholder for Sage
                'default' => new class {}
            };

            $result = $service->testConnection();

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Connection successful' : 'Connection failed',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test failed: '.$e->getMessage(),
            ]);
        }
    }
}
