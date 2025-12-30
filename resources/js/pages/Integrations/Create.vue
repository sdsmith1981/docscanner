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
import { Separator } from '@/components/ui/separator';
import AuthenticatedLayout from '@/layouts/auth/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Building,
    Calculator,
    CheckCircle,
    Clock,
    Database,
    ExternalLink,
    FileText,
    Link2,
    Shield,
    Zap,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    provider?: string;
}>();

const selectedProvider = ref(props.provider ?? '');
const isConnecting = ref(false);

const availableProviders = [
    {
        provider: 'xero',
        name: 'Xero',
        description:
            'Cloud-based accounting software for small and medium-sized businesses',
        longDescription:
            'Xero is a comprehensive cloud accounting platform that helps businesses manage their finances, send invoices, track expenses, and get real-time financial insights.',
        features: [
            'Invoice validation and verification',
            'Automatic invoice data extraction',
            'Real-time financial data sync',
            'Multi-currency support',
            'Advanced reporting and analytics',
        ],
        benefits: [
            'Reduce manual data entry',
            'Improve accuracy of financial records',
            'Streamline invoice processing',
            'Better cash flow management',
        ],
        icon: Calculator,
        color: 'text-blue-600',
        bgColor: 'bg-blue-50',
        borderColor: 'border-blue-200',
        url: 'https://www.xero.com/uk/',
        connectUrl: '/auth/xero/redirect',
    },
    {
        provider: 'sage',
        name: 'Sage Business Cloud',
        description:
            'Business management software for accounting, payroll, and payments',
        longDescription:
            'Sage Business Cloud provides powerful accounting, payroll, and payment solutions designed to help businesses grow and succeed.',
        features: [
            'Comprehensive accounting tools',
            'Payroll management',
            'Payment processing',
            'Tax compliance',
            'Business analytics',
        ],
        benefits: [
            'All-in-one business management',
            'Simplified tax compliance',
            'Improved payroll efficiency',
            'Better financial visibility',
        ],
        icon: Building,
        color: 'text-green-600',
        bgColor: 'bg-green-50',
        borderColor: 'border-green-200',
        url: 'https://www.sage.com/en-gb/',
        connectUrl: '/auth/sage/redirect',
    },
    {
        provider: 'quickbooks',
        name: 'QuickBooks',
        description: 'Accounting software for tracking income and expenses',
        longDescription:
            'QuickBooks is a popular accounting solution that helps small businesses manage their finances, track expenses, and streamline accounting operations.',
        features: [
            'Income and expense tracking',
            'Invoice creation and management',
            'Bank reconciliation',
            'Financial reporting',
            'Tax preparation tools',
        ],
        benefits: [
            'Simplified bookkeeping',
            'Faster invoice processing',
            'Better expense management',
            'Tax time savings',
        ],
        icon: FileText,
        color: 'text-indigo-600',
        bgColor: 'bg-indigo-50',
        borderColor: 'border-indigo-200',
        url: 'https://quickbooks.intuit.com/',
        connectUrl: '/auth/quickbooks/redirect',
    },
];

const getSelectedProvider = computed(() => {
    return availableProviders.find(
        (p) => p.provider === selectedProvider.value,
    );
});

const selectProvider = (provider: string) => {
    selectedProvider.value = provider;
};

const connectIntegration = () => {
    if (!selectedProvider.value || !getSelectedProvider.value) return;

    isConnecting.value = true;

    // Redirect to OAuth provider
    window.location.href = getSelectedProvider.value.connectUrl;
};

const canProceed = computed(() => {
    return selectedProvider.value && getSelectedProvider.value !== undefined;
});
</script>

<template>
    <Head title="Add Integration" />

    <AuthenticatedLayout>
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center">
                    <Link :href="route('integrations.index')">
                        <Button variant="outline" size="sm" class="mr-4">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Back to Integrations
                        </Button>
                    </Link>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">
                            Add Integration
                        </h1>
                        <p class="text-muted-foreground">
                            Choose a service to integrate with your document
                            scanner
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                <!-- Provider Selection -->
                <div class="space-y-6">
                    <div>
                        <h2 class="mb-4 text-xl font-semibold">
                            Select Provider
                        </h2>
                        <div class="space-y-3">
                            <Card
                                v-for="provider in availableProviders"
                                :key="provider.provider"
                                :class="[
                                    'cursor-pointer transition-all hover:shadow-md',
                                    selectedProvider === provider.provider
                                        ? 'border-primary ring-2 ring-primary'
                                        : 'hover:border-muted-foreground/50',
                                ]"
                                @click="selectProvider(provider.provider)"
                            >
                                <CardContent class="p-6">
                                    <div class="flex items-start space-x-4">
                                        <div
                                            :class="[
                                                'rounded-full p-2',
                                                provider.bgColor,
                                            ]"
                                        >
                                            <component
                                                :is="provider.icon"
                                                :class="[
                                                    'h-6 w-6',
                                                    provider.color,
                                                ]"
                                            />
                                        </div>
                                        <div class="flex-1">
                                            <div
                                                class="flex items-center justify-between"
                                            >
                                                <h3
                                                    class="text-lg font-semibold"
                                                >
                                                    {{ provider.name }}
                                                </h3>
                                                <div
                                                    v-if="
                                                        selectedProvider ===
                                                        provider.provider
                                                    "
                                                    class="text-primary"
                                                >
                                                    <CheckCircle
                                                        class="h-5 w-5"
                                                    />
                                                </div>
                                            </div>
                                            <p
                                                class="text-sm text-muted-foreground"
                                            >
                                                {{ provider.description }}
                                            </p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>

                <!-- Provider Details -->
                <div class="space-y-6">
                    <div v-if="getSelectedProvider">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-xl font-semibold">
                                {{ getSelectedProvider.name }} Integration
                            </h2>
                            <Badge variant="outline">{{
                                getSelectedProvider.provider
                            }}</Badge>
                        </div>

                        <!-- Provider Info Card -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center">
                                    <component
                                        :is="getSelectedProvider.icon"
                                        :class="[
                                            'mr-2 h-5 w-5',
                                            getSelectedProvider.color,
                                        ]"
                                    />
                                    {{ getSelectedProvider.name }}
                                </CardTitle>
                                <CardDescription>{{
                                    getSelectedProvider.longDescription
                                }}</CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-6">
                                <!-- Features -->
                                <div>
                                    <h4
                                        class="mb-3 flex items-center font-medium"
                                    >
                                        <Zap class="mr-2 h-4 w-4" />
                                        Key Features
                                    </h4>
                                    <ul class="space-y-2">
                                        <li
                                            v-for="feature in getSelectedProvider.features"
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

                                <Separator />

                                <!-- Benefits -->
                                <div>
                                    <h4
                                        class="mb-3 flex items-center font-medium"
                                    >
                                        <Shield class="mr-2 h-4 w-4" />
                                        Benefits
                                    </h4>
                                    <ul class="space-y-2">
                                        <li
                                            v-for="benefit in getSelectedProvider.benefits"
                                            :key="benefit"
                                            class="flex items-start"
                                        >
                                            <CheckCircle
                                                class="mt-0.5 mr-2 h-4 w-4 flex-shrink-0 text-green-500"
                                            />
                                            <span class="text-sm">{{
                                                benefit
                                            }}</span>
                                        </li>
                                    </ul>
                                </div>

                                <Separator />

                                <!-- Additional Info -->
                                <div
                                    class="flex items-center justify-between text-sm text-muted-foreground"
                                >
                                    <div class="flex items-center">
                                        <Database class="mr-2 h-4 w-4" />
                                        <span>OAuth 2.0 Authentication</span>
                                    </div>
                                    <div class="flex items-center">
                                        <Clock class="mr-2 h-4 w-4" />
                                        <span>Secure connection</span>
                                    </div>
                                </div>

                                <!-- External Link -->
                                <div class="text-center">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        as-child
                                    >
                                        <a
                                            :href="getSelectedProvider.url"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <ExternalLink
                                                class="mr-2 h-4 w-4"
                                            />
                                            Learn more about
                                            {{ getSelectedProvider.name }}
                                        </a>
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Security Notice -->
                        <Alert>
                            <Shield class="h-4 w-4" />
                            <AlertDescription>
                                This integration uses OAuth 2.0 for secure
                                authentication. We only access the data
                                necessary for document processing and never
                                store your credentials.
                            </AlertDescription>
                        </Alert>
                    </div>

                    <!-- Empty State -->
                    <Card v-else>
                        <CardContent class="p-12 text-center">
                            <Link2
                                class="mx-auto mb-4 h-12 w-12 text-muted-foreground"
                            />
                            <h3 class="mb-2 text-lg font-semibold">
                                Select a Provider
                            </h3>
                            <p class="text-muted-foreground">
                                Choose an integration provider from the left to
                                see detailed information and connect your
                                account.
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Action Buttons -->
            <div v-if="getSelectedProvider" class="mt-8 flex justify-end">
                <div class="space-x-4">
                    <Link :href="route('integrations.index')">
                        <Button variant="outline"> Cancel </Button>
                    </Link>
                    <Button
                        @click="connectIntegration"
                        :disabled="!canProceed || isConnecting"
                        size="lg"
                    >
                        <Link2 class="mr-2 h-4 w-4" />
                        {{
                            isConnecting
                                ? 'Connecting...'
                                : `Connect ${getSelectedProvider.name}`
                        }}
                    </Button>
                </div>
            </div>

            <!-- Loading Overlay -->
            <div
                v-if="isConnecting"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            >
                <Card class="w-96">
                    <CardContent class="p-6 text-center">
                        <RefreshCw class="mx-auto mb-4 h-8 w-8 animate-spin" />
                        <h3 class="mb-2 font-semibold">
                            Connecting to {{ getSelectedProvider?.name }}
                        </h3>
                        <p class="text-sm text-muted-foreground">
                            You will be redirected to
                            {{ getSelectedProvider?.name }} to authorise this
                            integration.
                        </p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
