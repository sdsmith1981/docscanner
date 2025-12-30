<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Switch } from '@/components/ui/switch';
import AuthenticatedLayout from '@/layouts/auth/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowLeft,
    Building,
    Calculator,
    CheckCircle,
    ExternalLink,
    FileText,
    RefreshCw,
    Save,
    Settings,
    Trash2,
    Undo,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

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

const form = useForm({
    settings: props.integration.settings ?? {},
    is_active: props.integration.is_active,
});

const isRefreshing = ref(false);
const hasChanges = computed(() => {
    return (
        JSON.stringify(form.settings) !==
            JSON.stringify(props.integration.settings ?? {}) ||
        form.is_active !== props.integration.is_active
    );
});

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

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString();
};

const formatDateTime = (dateString: string): string => {
    return new Date(dateString).toLocaleString();
};

const saveSettings = () => {
    form.put(route('integrations.update', props.integration.id), {
        onSuccess: () => {
            // Success handled by Inertia
        },
    });
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

const deleteIntegration = () => {
    if (
        confirm(
            `Are you sure you want to disconnect ${getProviderName(props.integration.provider)} integration? This action cannot be undone.`,
        )
    ) {
        router.delete(route('integrations.destroy', props.integration.id));
    }
};

const resetToDefaults = () => {
    if (
        confirm(
            'Are you sure you want to reset all settings to their default values?',
        )
    ) {
        form.settings = {};
        form.is_active = props.integration.is_active;
    }
};

const addSettingField = () => {
    const key = prompt('Enter setting key:');
    if (key && !form.settings[key]) {
        form.settings[key] = '';
    }
};

const removeSettingField = (key: string) => {
    if (confirm(`Remove setting "${key}"?`)) {
        delete form.settings[key];
    }
};

const getDefaultSettings = (provider: string) => {
    switch (provider) {
        case 'xero':
            return [
                {
                    key: 'auto_sync_invoices',
                    label: 'Auto-sync Invoices',
                    type: 'switch',
                    default: true,
                },
                {
                    key: 'sync_interval',
                    label: 'Sync Interval (minutes)',
                    type: 'number',
                    default: 30,
                },
                {
                    key: 'default_currency',
                    label: 'Default Currency',
                    type: 'text',
                    default: 'GBP',
                },
                {
                    key: 'validate_vat_numbers',
                    label: 'Validate VAT Numbers',
                    type: 'switch',
                    default: true,
                },
                {
                    key: 'create_missing_customers',
                    label: 'Create Missing Customers',
                    type: 'switch',
                    default: false,
                },
            ];
        case 'sage':
            return [
                {
                    key: 'auto_sync_transactions',
                    label: 'Auto-sync Transactions',
                    type: 'switch',
                    default: true,
                },
                {
                    key: 'sync_interval',
                    label: 'Sync Interval (minutes)',
                    type: 'number',
                    default: 60,
                },
                {
                    key: 'default_ledger',
                    label: 'Default Ledger Account',
                    type: 'text',
                    default: '',
                },
                {
                    key: 'auto_categorise',
                    label: 'Auto-categorise Expenses',
                    type: 'switch',
                    default: true,
                },
            ];
        case 'quickbooks':
            return [
                {
                    key: 'auto_sync_invoices',
                    label: 'Auto-sync Invoices',
                    type: 'switch',
                    default: true,
                },
                {
                    key: 'sync_interval',
                    label: 'Sync Interval (minutes)',
                    type: 'number',
                    default: 45,
                },
                {
                    key: 'default_account',
                    label: 'Default Account',
                    type: 'text',
                    default: '',
                },
                {
                    key: 'match_transactions',
                    label: 'Match Bank Transactions',
                    type: 'switch',
                    default: true,
                },
            ];
        default:
            return [];
    }
};

const providerSettings = computed(() => {
    return getDefaultSettings(props.integration.provider);
});
</script>

<template>
    <Head
        :title="`Edit ${getProviderName(integration.provider)} Integration`"
    />

    <AuthenticatedLayout>
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <Link
                            :href="route('integrations.show', integration.id)"
                        >
                            <Button variant="outline" size="sm" class="mr-4">
                                <ArrowLeft class="mr-2 h-4 w-4" />
                                Back to Integration
                            </Button>
                        </Link>
                        <div>
                            <h1 class="text-3xl font-bold tracking-tight">
                                Edit
                                {{
                                    getProviderName(integration.provider)
                                }}
                                Integration
                            </h1>
                            <p class="text-muted-foreground">
                                Configure settings and connection options for
                                your integration
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button
                            variant="outline"
                            @click="resetToDefaults"
                            :disabled="form.processing"
                        >
                            <Undo class="mr-2 h-4 w-4" />
                            Reset to Defaults
                        </Button>
                        <Button
                            @click="saveSettings"
                            :disabled="!hasChanges || form.processing"
                        >
                            <Save
                                :class="[
                                    'mr-2 h-4 w-4',
                                    form.processing && 'animate-spin',
                                ]"
                            />
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </Button>
                    </div>
                </div>
            </div>

            <form @submit.prevent="saveSettings" class="space-y-6">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Main Content -->
                    <div class="space-y-6 lg:col-span-2">
                        <!-- Integration Info -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center">
                                    <component
                                        :is="
                                            getProviderIcon(
                                                integration.provider,
                                            )
                                        "
                                        class="mr-2 h-5 w-5"
                                    />
                                    {{ getProviderName(integration.provider) }}
                                </CardTitle>
                                <CardDescription>
                                    Configure your
                                    {{
                                        getProviderName(integration.provider)
                                    }}
                                    integration settings
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <!-- Active Status -->
                                <div class="flex items-center justify-between">
                                    <div>
                                        <Label for="is_active"
                                            >Active Status</Label
                                        >
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            Enable or disable this integration
                                        </p>
                                    </div>
                                    <Switch
                                        id="is_active"
                                        v-model="form.is_active"
                                    />
                                </div>

                                <!-- Connection Status -->
                                <div
                                    class="flex items-center justify-between rounded bg-muted p-3"
                                >
                                    <div class="flex items-center">
                                        <component
                                            :is="
                                                isExpired(
                                                    integration.expires_at,
                                                )
                                                    ? AlertTriangle
                                                    : CheckCircle
                                            "
                                            :class="[
                                                'mr-2 h-4 w-4',
                                                isExpired(
                                                    integration.expires_at,
                                                )
                                                    ? 'text-red-500'
                                                    : 'text-green-500',
                                            ]"
                                        />
                                        <span class="text-sm">
                                            {{
                                                isExpired(
                                                    integration.expires_at,
                                                )
                                                    ? 'Connection Expired'
                                                    : needsRefresh(
                                                            integration.expires_at,
                                                        )
                                                      ? 'Needs Refresh'
                                                      : 'Connection Active'
                                            }}
                                        </span>
                                    </div>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="refreshIntegration"
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
                                                : 'Refresh'
                                        }}
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Integration Settings -->
                        <Card>
                            <CardHeader>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <CardTitle
                                            >Integration Settings</CardTitle
                                        >
                                        <CardDescription>
                                            Configure how this integration works
                                            with your documents
                                        </CardDescription>
                                    </div>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="addSettingField"
                                        type="button"
                                    >
                                        Add Custom Setting
                                    </Button>
                                </div>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <!-- Default Settings -->
                                <div
                                    v-if="providerSettings.length > 0"
                                    class="space-y-4"
                                >
                                    <div
                                        v-for="setting in providerSettings"
                                        :key="setting.key"
                                        class="space-y-2"
                                    >
                                        <Label :for="setting.key">{{
                                            setting.label
                                        }}</Label>

                                        <!-- Switch Input -->
                                        <div
                                            v-if="setting.type === 'switch'"
                                            class="flex items-center space-x-2"
                                        >
                                            <Switch
                                                :id="setting.key"
                                                v-model="
                                                    form.settings[setting.key]
                                                "
                                            />
                                            <span
                                                class="text-sm text-muted-foreground"
                                            >
                                                {{
                                                    form.settings[setting.key]
                                                        ? 'Enabled'
                                                        : 'Disabled'
                                                }}
                                            </span>
                                        </div>

                                        <!-- Number Input -->
                                        <Input
                                            v-else-if="
                                                setting.type === 'number'
                                            "
                                            :id="setting.key"
                                            v-model.number="
                                                form.settings[setting.key]
                                            "
                                            type="number"
                                            :placeholder="
                                                setting.default.toString()
                                            "
                                        />

                                        <!-- Text Input -->
                                        <Input
                                            v-else
                                            :id="setting.key"
                                            v-model="form.settings[setting.key]"
                                            type="text"
                                            :placeholder="
                                                setting.default.toString()
                                            "
                                        />
                                    </div>
                                </div>

                                <!-- Custom Settings -->
                                <div
                                    v-if="
                                        form.settings &&
                                        Object.keys(form.settings).length > 0
                                    "
                                >
                                    <Separator />
                                    <h4 class="mb-3 font-medium">
                                        Custom Settings
                                    </h4>
                                    <div class="space-y-3">
                                        <div
                                            v-for="(
                                                value, key
                                            ) in form.settings"
                                            :key="key"
                                            class="space-y-2"
                                        >
                                            <div
                                                class="flex items-center justify-between"
                                            >
                                                <Label :for="key">{{
                                                    key
                                                }}</Label>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    @click="
                                                        removeSettingField(key)
                                                    "
                                                    type="button"
                                                    class="text-destructive hover:text-destructive"
                                                >
                                                    <Trash2 class="h-4 w-4" />
                                                </Button>
                                            </div>
                                            <Input
                                                :id="key"
                                                v-model="form.settings[key]"
                                                :placeholder="`Enter ${key}`"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <!-- No Settings -->
                                <div v-else class="py-8 text-center">
                                    <Settings
                                        class="mx-auto mb-4 h-12 w-12 text-muted-foreground"
                                    />
                                    <h3 class="mb-2 text-lg font-semibold">
                                        No Custom Settings
                                    </h3>
                                    <p class="mb-4 text-muted-foreground">
                                        This integration uses default settings.
                                        Add custom settings to override default
                                        behavior.
                                    </p>
                                    <Button
                                        variant="outline"
                                        @click="addSettingField"
                                        type="button"
                                    >
                                        Add Custom Setting
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Integration Info -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Integration Details</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="font-medium">Provider:</span>
                                    <span>{{
                                        getProviderName(integration.provider)
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium">Type:</span>
                                    <span>{{ integration.type }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium">Status:</span>
                                    <Badge
                                        :variant="
                                            integration.is_active
                                                ? 'default'
                                                : 'secondary'
                                        "
                                    >
                                        {{
                                            integration.is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
                                    </Badge>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium">Created:</span>
                                    <span>{{
                                        formatDate(integration.created_at)
                                    }}</span>
                                </div>
                                <div
                                    v-if="integration.expires_at"
                                    class="flex justify-between"
                                >
                                    <span class="font-medium">Expires:</span>
                                    <span>{{
                                        formatDate(integration.expires_at)
                                    }}</span>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Danger Zone -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="text-destructive"
                                    >Danger Zone</CardTitle
                                >
                                <CardDescription>
                                    Irreversible actions that affect this
                                    integration
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-3">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="w-full justify-start"
                                    @click="resetToDefaults"
                                    :disabled="form.processing"
                                >
                                    <Undo class="mr-2 h-4 w-4" />
                                    Reset to Defaults
                                </Button>
                                <Button
                                    variant="destructive"
                                    size="sm"
                                    class="w-full justify-start"
                                    @click="deleteIntegration"
                                >
                                    <Trash2 class="mr-2 h-4 w-4" />
                                    Disconnect Integration
                                </Button>
                            </CardContent>
                        </Card>

                        <!-- Help -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Need Help?</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-3">
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
                                        <ExternalLink class="mr-2 h-4 w-4" />
                                        Documentation
                                    </a>
                                </Button>
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
                                            getProviderName(
                                                integration.provider,
                                            )
                                        }}
                                        Help
                                    </a>
                                </Button>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
