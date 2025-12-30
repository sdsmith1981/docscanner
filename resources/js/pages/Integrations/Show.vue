<script setup lang="ts">
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Switch } from '@/components/ui/switch';
import AuthenticatedLayout from '@/layouts/auth/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Activity,
    AlertTriangle,
    ArrowLeft,
    Building,
    Calculator,
    CheckCircle,
    Clock,
    ExternalLink,
    EyeOff,
    FileText,
    RefreshCw,
    Settings,
    Shield,
    Trash2,
    Zap,
} from 'lucide-vue-next';
import { ref } from 'vue';

interface Integration {
    id: number;
    type: string;
    provider: string;
    is_active: boolean;
    expires_at?: string;
    created_at: string;
    updated_at: string;
    provider_data?: Record<string, any>;
    settings?: Record<string, any>;
    user?: {
        id: number;
        name: string;
        email: string;
    };
}

const props = defineProps<{
    integration: Integration;
}>();

const isSaving = ref(false);
const isRefreshing = ref(false);
const testResults = ref<{ success: boolean; message: string } | null>(null);

const getProviderIcon = (provider: string) => {
    switch (provider) {
        case 'xero':
            return Calculator;
        case 'sage':
            return Building;
        case 'quickbooks':
            return FileText;
        default:
            return Settings;
    }
};

const getProviderName = (provider: string) => {
    switch (provider) {
        case 'xero':
            return 'Xero';
        case 'sage':
            return 'Sage Business Cloud';
        case 'quickbooks':
            return 'QuickBooks';
        default:
            return provider.charAt(0).toUpperCase() + provider.slice(1);
    }
};

const getProviderDescription = (provider: string) => {
    switch (provider) {
        case 'xero':
            return 'Cloud-based accounting software for small and medium-sized businesses';
        case 'sage':
            return 'Business management software for accounting, payroll, and payments';
        case 'quickbooks':
            return 'Accounting software for tracking income and expenses';
        default:
            return 'Third-party integration for extended functionality';
    }
};

const isExpired = (expiresAt?: string): boolean => {
    if (!expiresAt) return false;
    return new Date(expiresAt) < new Date();
};

const needsRefresh = (expiresAt?: string): boolean => {
    if (!expiresAt) return false;
    const expiryDate = new Date(expiresAt);
    const now = new Date();
    const hoursUntilExpiry =
        (expiryDate.getTime() - now.getTime()) / (1000 * 60 * 60);
    return hoursUntilExpiry < 24;
};

const getStatusBadge = (integration: Integration) => {
    if (!integration.is_active) {
        return { variant: 'secondary', text: 'Inactive', icon: EyeOff };
    }

    if (isExpired(integration.expires_at)) {
        return { variant: 'destructive', text: 'Expired', icon: AlertTriangle };
    }

    if (needsRefresh(integration.expires_at)) {
        return { variant: 'default', text: 'Needs Refresh', icon: Clock };
    }

    return { variant: 'default', text: 'Active', icon: CheckCircle };
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString();
};

const formatDateTime = (dateString: string): string => {
    return new Date(dateString).toLocaleString();
};

const toggleStatus = () => {
    isSaving.value = true;
    router.patch(
        route('integrations.toggle', props.integration.id),
        {},
        {
            onFinish: () => {
                isSaving.value = false;
            },
            preserveScroll: true,
        },
    );
};

const refreshIntegration = () => {
    isRefreshing.value = true;
    router.post(
        route('integrations.refresh', props.integration.id),
        {},
        {
            onFinish: () => {
                isRefreshing.value = false;
            },
        },
    );
};

const testConnection = () => {
    testResults.value = null;
    router.post(
        route('integrations.test', props.integration.id),
        {},
        {
            onSuccess: (response) => {
                testResults.value = {
                    success: true,
                    message:
                        response.props.flash?.success ||
                        'Connection test successful',
                };
            },
            onError: (errors) => {
                testResults.value = {
                    success: false,
                    message: errors.connection?.[0] || 'Connection test failed',
                };
            },
            preserveScroll: true,
        },
    );
};

const deleteIntegration = () => {
    if (
        confirm(
            `Are you sure you want to disconnect ${getProviderName(props.integration.provider)} integration? This action cannot be undone.`,
        )
    ) {
        router.delete(route('integrations.destroy', props.integration.id));
    }
};

const getIntegrationFeatures = (provider: string) => {
    switch (provider) {
        case 'xero':
            return [
                'Invoice validation and verification',
                'Automatic invoice data extraction',
                'Real-time financial data sync',
                'Multi-currency support',
                'Advanced reporting and analytics',
            ];
        case 'sage':
            return [
                'Comprehensive accounting tools',
                'Payroll management',
                'Payment processing',
                'Tax compliance',
                'Business analytics',
            ];
        case 'quickbooks':
            return [
                'Income and expense tracking',
                'Invoice creation and management',
                'Bank reconciliation',
                'Financial reporting',
                'Tax preparation tools',
            ];
        default:
            return [
                'Data synchronization',
                'API integration',
                'Automated workflows',
            ];
    }
};
</script>

<template>
    <Head :title="`${getProviderName(integration.provider)} Integration`" />

    <AuthenticatedLayout>
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <Link :href="route('integrations.index')">
                            <Button variant="outline" size="sm" class="mr-4">
                                <ArrowLeft class="mr-2 h-4 w-4" />
                                Back to Integrations
                            </Button>
                        </Link>
                        <div>
                            <h1 class="text-3xl font-bold tracking-tight">
                                {{
                                    getProviderName(integration.provider)
                                }}
                                Integration
                            </h1>
                            <p class="text-muted-foreground">
                                Manage your
                                {{
                                    getProviderName(integration.provider)
                                }}
                                connection and settings
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            @click="testConnection"
                        >
                            <Activity class="mr-2 h-4 w-4" />
                            Test Connection
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            @click="
                                router.get(
                                    route('integrations.edit', integration.id),
                                )
                            "
                        >
                            <Settings class="mr-2 h-4 w-4" />
                            Edit Settings
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Test Results Alert -->
            <Alert
                v-if="testResults"
                :variant="testResults.success ? 'default' : 'destructive'"
                class="mb-6"
            >
                <component
                    :is="testResults.success ? CheckCircle : AlertTriangle"
                    class="h-4 w-4"
                />
                <AlertDescription>{{ testResults.message }}</AlertDescription>
            </Alert>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Integration Overview -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center">
                                <component
                                    :is="getProviderIcon(integration.provider)"
                                    class="mr-2 h-5 w-5"
                                />
                                Integration Overview
                            </CardTitle>
                            <CardDescription>{{
                                getProviderDescription(integration.provider)
                            }}</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <!-- Status -->
                            <div class="flex items-center justify-between">
                                <Label for="active-status">Active Status</Label>
                                <div class="flex items-center space-x-2">
                                    <Switch
                                        id="active-status"
                                        :checked="integration.is_active"
                                        @click="toggleStatus"
                                        :disabled="isSaving"
                                    />
                                    <Badge
                                        :variant="
                                            getStatusBadge(integration).variant
                                        "
                                    >
                                        <component
                                            :is="
                                                getStatusBadge(integration).icon
                                            "
                                            class="mr-1 h-3 w-3"
                                        />
                                        {{ getStatusBadge(integration).text }}
                                    </Badge>
                                </div>
                            </div>

                            <Separator />

                            <!-- Features -->
                            <div>
                                <h4 class="mb-3 flex items-center font-medium">
                                    <Zap class="mr-2 h-4 w-4" />
                                    Available Features
                                </h4>
                                <ul class="space-y-2">
                                    <li
                                        v-for="feature in getIntegrationFeatures(
                                            integration.provider,
                                        )"
                                        :key="feature"
                                        class="flex items-start"
                                    >
                                        <CheckCircle
                                            class="mt-0.5 mr-2 h-4 w-4 flex-shrink-0 text-green-500"
                                        />
                                        <span class="text-sm">{{
                                            feature
                                        }}</span>
                                    </li>
                                </ul>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Connection Details -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Connection Details</CardTitle>
                            <CardDescription
                                >Information about your
                                {{
                                    getProviderName(integration.provider)
                                }}
                                connection</CardDescription
                            >
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="font-medium"
                                        >Integration Type:</span
                                    >
                                    <span class="ml-2">{{
                                        integration.type
                                    }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Provider:</span>
                                    <span class="ml-2">{{
                                        getProviderName(integration.provider)
                                    }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Created:</span>
                                    <span class="ml-2">{{
                                        formatDateTime(integration.created_at)
                                    }}</span>
                                </div>
                                <div>
                                    <span class="font-medium"
                                        >Last Updated:</span
                                    >
                                    <span class="ml-2">{{
                                        formatDateTime(integration.updated_at)
                                    }}</span>
                                </div>
                                <div v-if="integration.user">
                                    <span class="font-medium"
                                        >Connected by:</span
                                    >
                                    <span class="ml-2">{{
                                        integration.user.name
                                    }}</span>
                                </div>
                                <div v-if="integration.expires_at">
                                    <span class="font-medium">Expires:</span>
                                    <span class="ml-2">{{
                                        formatDateTime(integration.expires_at)
                                    }}</span>
                                </div>
                            </div>

                            <!-- Provider Data -->
                            <div v-if="integration.provider_data">
                                <h4 class="mb-2 font-medium">
                                    Provider Information
                                </h4>
                                <div class="rounded bg-muted p-3 text-sm">
                                    <div
                                        v-for="(
                                            value, key
                                        ) in integration.provider_data"
                                        :key="key"
                                        class="flex justify-between py-1"
                                    >
                                        <span class="font-mono"
                                            >{{ key }}:</span
                                        >
                                        <span>{{ value }}</span>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Settings -->
                    <Card v-if="integration.settings">
                        <CardHeader>
                            <CardTitle>Integration Settings</CardTitle>
                            <CardDescription
                                >Custom settings for this
                                integration</CardDescription
                            >
                        </CardHeader>
                        <CardContent>
                            <div class="rounded bg-muted p-3 text-sm">
                                <div
                                    v-for="(value, key) in integration.settings"
                                    :key="key"
                                    class="flex justify-between py-1"
                                >
                                    <span class="font-mono">{{ key }}:</span>
                                    <span>{{ value }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Actions</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <Button
                                @click="testConnection"
                                variant="outline"
                                size="sm"
                                class="w-full justify-start"
                            >
                                <Activity class="mr-2 h-4 w-4" />
                                Test Connection
                            </Button>
                            <Button
                                @click="refreshIntegration"
                                variant="outline"
                                size="sm"
                                class="w-full justify-start"
                                :disabled="isRefreshing"
                            >
                                <RefreshCw
                                    :class="[
                                        'mr-2 h-4 w-4',
                                        isRefreshing && 'animate-spin',
                                    ]"
                                />
                                {{
                                    isRefreshing
                                        ? 'Refreshing...'
                                        : 'Refresh Connection'
                                }}
                            </Button>
                            <Button
                                @click="
                                    router.get(
                                        route(
                                            'integrations.edit',
                                            integration.id,
                                        ),
                                    )
                                "
                                variant="outline"
                                size="sm"
                                class="w-full justify-start"
                            >
                                <Settings class="mr-2 h-4 w-4" />
                                Edit Settings
                            </Button>
                        </CardContent>
                    </Card>

                    <!-- Status Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Connection Status</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3 text-center">
                                <div
                                    :class="[
                                        'mx-auto flex h-16 w-16 items-center justify-center rounded-full',
                                        integration.is_active &&
                                        !isExpired(integration.expires_at)
                                            ? 'bg-green-100'
                                            : 'bg-red-100',
                                    ]"
                                >
                                    <component
                                        :is="getStatusBadge(integration).icon"
                                        :class="[
                                            'h-8 w-8',
                                            integration.is_active &&
                                            !isExpired(integration.expires_at)
                                                ? 'text-green-600'
                                                : 'text-red-600',
                                        ]"
                                    />
                                </div>
                                <div>
                                    <p class="font-semibold">
                                        {{ getStatusBadge(integration).text }}
                                    </p>
                                    <p
                                        class="mt-1 text-sm text-muted-foreground"
                                    >
                                        {{
                                            isExpired(integration.expires_at)
                                                ? 'Connection has expired'
                                                : needsRefresh(
                                                        integration.expires_at,
                                                    )
                                                  ? 'Expires soon - please refresh'
                                                  : 'Connection is active and healthy'
                                        }}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- External Resources -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Resources</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <Button
                                variant="outline"
                                size="sm"
                                class="w-full justify-start"
                                asChild
                            >
                                <a
                                    :href="`https://${integration.provider}.com`"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <ExternalLink class="mr-2 h-4 w-4" />
                                    {{
                                        getProviderName(integration.provider)
                                    }}
                                    Website
                                </a>
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                class="w-full justify-start"
                                asChild
                            >
                                <a
                                    href="#"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <Shield class="mr-2 h-4 w-4" />
                                    Documentation
                                </a>
                            </Button>
                        </CardContent>
                    </Card>

                    <!-- Danger Zone -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-destructive"
                                >Danger Zone</CardTitle
                            >
                        </CardHeader>
                        <CardContent>
                            <Button
                                variant="destructive"
                                size="sm"
                                class="w-full"
                                @click="deleteIntegration"
                            >
                                <Trash2 class="mr-2 h-4 w-4" />
                                Disconnect Integration
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
