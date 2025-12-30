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
import AuthenticatedLayout from '@/layouts/auth/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Building,
    Calculator,
    CheckCircle,
    Clock,
    ExternalLink,
    Eye,
    EyeOff,
    FileText,
    Link2,
    Plus,
    RefreshCw,
    Settings,
    Trash2,
} from 'lucide-vue-next';

interface Integration {
    id: number;
    type: string;
    provider: string;
    is_active: boolean;
    expires_at?: string;
    created_at: string;
    updated_at: string;
    provider_data?: Record<string, any>;
    user?: {
        id: number;
        name: string;
        email: string;
    };
}

const props = defineProps<{
    integrations: Integration[];
}>();

const getProviderIcon = (provider: string) => {
    switch (provider) {
        case 'xero':
            return Calculator;
        case 'sage':
            return Building;
        case 'quickbooks':
            return FileText;
        default:
            return Link2;
    }
};

const getProviderName = (provider: string) => {
    switch (provider) {
        case 'xero':
            return 'Xero';
        case 'sage':
            return 'Sage';
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

const toggleStatus = (integration: Integration) => {
    router.patch(
        route('integrations.toggle', integration.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                // Success handled by Inertia
            },
        },
    );
};

const deleteIntegration = (integration: Integration) => {
    if (
        confirm(
            `Are you sure you want to disconnect the ${getProviderName(integration.provider)} integration? This action cannot be undone.`,
        )
    ) {
        router.delete(route('integrations.destroy', integration.id));
    }
};

const availableProviders = [
    {
        provider: 'xero',
        name: 'Xero',
        description:
            'Connect to Xero accounting software for invoice validation and financial data',
        icon: Calculator,
        color: 'text-blue-600',
    },
    {
        provider: 'sage',
        name: 'Sage',
        description:
            'Integrate with Sage for comprehensive business management',
        icon: Building,
        color: 'text-green-600',
    },
    {
        provider: 'quickbooks',
        name: 'QuickBooks',
        description:
            'Sync with QuickBooks for streamlined accounting workflows',
        icon: FileText,
        color: 'text-indigo-600',
    },
];
</script>

<template>
    <Head title="Integrations" />

    <AuthenticatedLayout>
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">
                            Integrations
                        </h1>
                        <p class="text-muted-foreground">
                            Connect external services to extend the
                            functionality of your document scanner
                        </p>
                    </div>
                    <Link :href="route('integrations.create')">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Add Integration
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Available Integrations -->
            <div class="mb-8">
                <h2 class="mb-4 text-xl font-semibold">
                    Available Integrations
                </h2>
                <div
                    class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
                >
                    <Card
                        v-for="provider in availableProviders"
                        :key="provider.provider"
                        class="cursor-pointer transition-shadow hover:shadow-md"
                        @click="
                            router.get(
                                route('integrations.create', {
                                    provider: provider.provider,
                                }),
                            )
                        "
                    >
                        <CardHeader>
                            <CardTitle class="flex items-center">
                                <component
                                    :is="provider.icon"
                                    :class="['mr-2 h-5 w-5', provider.color]"
                                />
                                {{ provider.name }}
                            </CardTitle>
                            <CardDescription>{{
                                provider.description
                            }}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Button variant="outline" size="sm" class="w-full">
                                <ExternalLink class="mr-2 h-4 w-4" />
                                Connect {{ provider.name }}
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Active Integrations -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold">
                        Connected Integrations
                    </h2>
                    <div class="text-sm text-muted-foreground">
                        {{ integrations.length }}
                        {{
                            integrations.length === 1
                                ? 'integration'
                                : 'integrations'
                        }}
                        connected
                    </div>
                </div>

                <div v-if="integrations.length > 0" class="space-y-4">
                    <Card
                        v-for="integration in integrations"
                        :key="integration.id"
                    >
                        <CardContent class="p-6">
                            <div class="flex items-center justify-between">
                                <!-- Integration Info -->
                                <div class="flex items-center space-x-4">
                                    <div class="rounded-full bg-muted p-2">
                                        <component
                                            :is="
                                                getProviderIcon(
                                                    integration.provider,
                                                )
                                            "
                                            class="h-6 w-6"
                                        />
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h3 class="font-semibold">
                                                {{
                                                    getProviderName(
                                                        integration.provider,
                                                    )
                                                }}
                                            </h3>
                                            <Badge
                                                :variant="
                                                    getStatusBadge(integration)
                                                        .variant
                                                "
                                            >
                                                <component
                                                    :is="
                                                        getStatusBadge(
                                                            integration,
                                                        ).icon
                                                    "
                                                    class="mr-1 h-3 w-3"
                                                />
                                                {{
                                                    getStatusBadge(integration)
                                                        .text
                                                }}
                                            </Badge>
                                            <Badge variant="outline">{{
                                                integration.type
                                            }}</Badge>
                                        </div>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            Connected
                                            {{
                                                formatDate(
                                                    integration.created_at,
                                                )
                                            }}
                                            <span v-if="integration.expires_at">
                                                • Expires
                                                {{
                                                    formatDate(
                                                        integration.expires_at,
                                                    )
                                                }}
                                            </span>
                                        </p>
                                        <p
                                            v-if="integration.user"
                                            class="text-xs text-muted-foreground"
                                        >
                                            Connected by
                                            {{ integration.user.name }} ({{
                                                integration.user.email
                                            }})
                                        </p>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="toggleStatus(integration)"
                                    >
                                        <component
                                            :is="
                                                integration.is_active
                                                    ? EyeOff
                                                    : Eye
                                            "
                                            class="mr-2 h-4 w-4"
                                        />
                                        {{
                                            integration.is_active
                                                ? 'Disable'
                                                : 'Enable'
                                        }}
                                    </Button>
                                    <Link
                                        :href="
                                            route(
                                                'integrations.show',
                                                integration.id,
                                            )
                                        "
                                    >
                                        <Button variant="outline" size="sm">
                                            <Settings class="mr-2 h-4 w-4" />
                                            Configure
                                        </Button>
                                    </Link>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="
                                            router.get(
                                                route(
                                                    'integrations.edit',
                                                    integration.id,
                                                ),
                                            )
                                        "
                                    >
                                        <RefreshCw class="mr-2 h-4 w-4" />
                                        Refresh
                                    </Button>
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        @click="deleteIntegration(integration)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>

                            <!-- Warning for expired/expiring integrations -->
                            <Alert
                                v-if="isExpired(integration.expires_at)"
                                class="mt-4"
                            >
                                <AlertTriangle class="h-4 w-4" />
                                <AlertDescription>
                                    This integration has expired. Please refresh
                                    the connection to continue using
                                    {{ getProviderName(integration.provider) }}.
                                </AlertDescription>
                            </Alert>

                            <Alert
                                v-else-if="needsRefresh(integration.expires_at)"
                                class="mt-4"
                            >
                                <Clock class="h-4 w-4" />
                                <AlertDescription>
                                    This integration will expire soon. Refresh
                                    the connection to maintain uninterrupted
                                    service.
                                </AlertDescription>
                            </Alert>
                        </CardContent>
                    </Card>
                </div>

                <!-- Empty State -->
                <Card v-else>
                    <CardContent class="p-12 text-center">
                        <Link2
                            class="mx-auto mb-4 h-12 w-12 text-muted-foreground"
                        />
                        <h3 class="mb-2 text-lg font-semibold">
                            No Integrations Connected
                        </h3>
                        <p class="mb-4 text-muted-foreground">
                            Connect external services to enhance your document
                            processing workflows.
                        </p>
                        <Link :href="route('integrations.create')">
                            <Button>
                                <Plus class="mr-2 h-4 w-4" />
                                Add Your First Integration
                            </Button>
                        </Link>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
