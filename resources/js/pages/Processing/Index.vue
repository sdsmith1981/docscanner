<script setup lang="ts">
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import AuthenticatedLayout from '@/layouts/auth/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Activity,
    AlertCircle,
    CheckCircle,
    Clock,
    Eye,
    File,
    FileText as FileDoc,
    FileText,
    Image,
    RefreshCw,
} from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

interface ProcessingDocument {
    id: number;
    title: string;
    type: string;
    file_name: string;
    file_size: number;
    file_mime_type: string;
    processing_status: 'pending' | 'processing' | 'completed' | 'failed';
    processing_started_at?: string;
    processing_completed_at?: string;
    processing_error?: string;
    progress?: number;
    created_at: string;
    updated_at: string;
    processing_attempts?: Array<{
        id: number;
        status: string;
        started_at: string;
        completed_at?: string;
        error_message?: string;
    }>;
}

interface ProcessingStats {
    total: number;
    pending: number;
    processing: number;
    completed: number;
    failed: number;
}

const props = defineProps<{
    documents: ProcessingDocument[];
    stats: ProcessingStats;
}>();

const autoRefresh = ref(true);
const refreshInterval = ref<NodeJS.Timeout | null>(null);
const isRefreshing = ref(false);

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'pending':
            return Clock;
        case 'processing':
            return RefreshCw;
        case 'completed':
            return CheckCircle;
        case 'failed':
            return AlertCircle;
        default:
            return Activity;
    }
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'pending':
            return 'secondary';
        case 'processing':
            return 'default';
        case 'completed':
            return 'default';
        case 'failed':
            return 'destructive';
        default:
            return 'outline';
    }
};

const getStatusText = (status: string) => {
    switch (status) {
        case 'pending':
            return 'Queued';
        case 'processing':
            return 'Processing';
        case 'completed':
            return 'Completed';
        case 'failed':
            return 'Failed';
        default:
            return 'Unknown';
    }
};

const getFileIcon = (mimeType: string) => {
    if (mimeType.startsWith('image/')) return Image;
    if (mimeType.includes('pdf')) return FileDoc;
    return File;
};

const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleString();
};

const formatDuration = (start: string, end?: string): string => {
    const startTime = new Date(start);
    const endTime = end ? new Date(end) : new Date();
    const duration = endTime.getTime() - startTime.getTime();

    const seconds = Math.floor(duration / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);

    if (hours > 0) {
        return `${hours}h ${minutes % 60}m`;
    } else if (minutes > 0) {
        return `${minutes}m ${seconds % 60}s`;
    } else {
        return `${seconds}s`;
    }
};

const refresh = () => {
    isRefreshing.value = true;
    window.location.reload();
};

const toggleAutoRefresh = () => {
    autoRefresh.value = !autoRefresh.value;
    if (autoRefresh.value) {
        startAutoRefresh();
    } else {
        stopAutoRefresh();
    }
};

const startAutoRefresh = () => {
    refreshInterval.value = setInterval(() => {
        refresh();
    }, 10000); // Refresh every 10 seconds
};

const stopAutoRefresh = () => {
    if (refreshInterval.value) {
        clearInterval(refreshInterval.value);
        refreshInterval.value = null;
    }
};

onMounted(() => {
    if (autoRefresh.value) {
        startAutoRefresh();
    }
});

onUnmounted(() => {
    stopAutoRefresh();
});

const getProgressValue = (document: ProcessingDocument): number => {
    if (document.progress !== undefined) {
        return document.progress;
    }

    // Estimate progress based on status
    switch (document.processing_status) {
        case 'pending':
            return 0;
        case 'processing':
            return 50;
        case 'completed':
            return 100;
        case 'failed':
            return document.processing_attempts?.length ? 25 : 10;
        default:
            return 0;
    }
};
</script>

<template>
    <Head title="Processing Queue" />

    <AuthenticatedLayout>
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">
                            Processing Queue
                        </h1>
                        <p class="text-muted-foreground">
                            Monitor document processing status and progress
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            @click="toggleAutoRefresh"
                        >
                            <RefreshCw
                                :class="[
                                    'mr-2 h-4 w-4',
                                    autoRefresh && 'animate-spin',
                                ]"
                            />
                            {{
                                autoRefresh
                                    ? 'Auto-refresh ON'
                                    : 'Auto-refresh OFF'
                            }}
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            @click="refresh"
                            :disabled="isRefreshing"
                        >
                            <RefreshCw
                                :class="[
                                    'mr-2 h-4 w-4',
                                    isRefreshing && 'animate-spin',
                                ]"
                            />
                            Refresh
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Total
                                </p>
                                <p class="text-2xl font-bold">
                                    {{ stats.total }}
                                </p>
                            </div>
                            <Activity class="h-8 w-8 text-muted-foreground" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Queued
                                </p>
                                <p class="text-2xl font-bold">
                                    {{ stats.pending }}
                                </p>
                            </div>
                            <Clock class="h-8 w-8 text-muted-foreground" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Processing
                                </p>
                                <p class="text-2xl font-bold">
                                    {{ stats.processing }}
                                </p>
                            </div>
                            <RefreshCw
                                class="h-8 w-8 animate-spin text-muted-foreground"
                            />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Failed
                                </p>
                                <p class="text-2xl font-bold">
                                    {{ stats.failed }}
                                </p>
                            </div>
                            <AlertCircle class="h-8 w-8 text-destructive" />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Documents List -->
            <div class="space-y-4">
                <div v-if="documents.length > 0">
                    <!-- Processing Items -->
                    <Card
                        v-for="document in documents"
                        :key="document.id"
                        class="transition-shadow hover:shadow-md"
                    >
                        <CardContent class="p-6">
                            <div class="mb-4 flex items-start justify-between">
                                <!-- Document Info -->
                                <div class="flex-1">
                                    <div class="mb-2 flex items-center gap-3">
                                        <component
                                            :is="
                                                getFileIcon(
                                                    document.file_mime_type,
                                                )
                                            "
                                            class="h-5 w-5 text-muted-foreground"
                                        />
                                        <Link
                                            :href="
                                                route(
                                                    'documents.show',
                                                    document.id,
                                                )
                                            "
                                            class="hover:underline"
                                        >
                                            <h3 class="text-lg font-semibold">
                                                {{ document.title }}
                                            </h3>
                                        </Link>
                                        <Badge variant="outline">{{
                                            document.type
                                        }}</Badge>
                                        <Badge
                                            :variant="
                                                getStatusVariant(
                                                    document.processing_status,
                                                )
                                            "
                                        >
                                            <component
                                                :is="
                                                    getStatusIcon(
                                                        document.processing_status,
                                                    )
                                                "
                                                class="mr-1 inline h-3 w-3"
                                            />
                                            {{
                                                getStatusText(
                                                    document.processing_status,
                                                )
                                            }}
                                        </Badge>
                                    </div>

                                    <!-- File Info -->
                                    <div
                                        class="mb-3 flex items-center gap-4 text-sm text-muted-foreground"
                                    >
                                        <span>{{ document.file_name }}</span>
                                        <span>{{
                                            formatFileSize(document.file_size)
                                        }}</span>
                                        <span
                                            >Added
                                            {{
                                                formatDate(document.created_at)
                                            }}</span
                                        >
                                    </div>

                                    <!-- Processing Progress -->
                                    <div
                                        v-if="
                                            document.processing_status ===
                                                'processing' ||
                                            document.processing_status ===
                                                'completed'
                                        "
                                        class="mb-3"
                                    >
                                        <div
                                            class="mb-1 flex justify-between text-sm"
                                        >
                                            <span>Processing Progress</span>
                                            <span
                                                >{{
                                                    getProgressValue(document)
                                                }}%</span
                                            >
                                        </div>
                                        <Progress
                                            :value="getProgressValue(document)"
                                            class="h-2"
                                        />
                                    </div>

                                    <!-- Processing Duration -->
                                    <div
                                        v-if="document.processing_started_at"
                                        class="mb-2 text-sm text-muted-foreground"
                                    >
                                        <span
                                            v-if="
                                                document.processing_completed_at
                                            "
                                        >
                                            Processing completed in
                                            {{
                                                formatDuration(
                                                    document.processing_started_at,
                                                    document.processing_completed_at,
                                                )
                                            }}
                                        </span>
                                        <span
                                            v-else-if="
                                                document.processing_status ===
                                                'processing'
                                            "
                                        >
                                            Processing for
                                            {{
                                                formatDuration(
                                                    document.processing_started_at,
                                                )
                                            }}
                                        </span>
                                    </div>

                                    <!-- Error Message -->
                                    <Alert
                                        v-if="document.processing_error"
                                        variant="destructive"
                                        class="mb-3"
                                    >
                                        <AlertCircle class="h-4 w-4" />
                                        <AlertDescription>{{
                                            document.processing_error
                                        }}</AlertDescription>
                                    </Alert>

                                    <!-- Processing Attempts -->
                                    <div
                                        v-if="
                                            document.processing_attempts &&
                                            document.processing_attempts
                                                .length > 0
                                        "
                                        class="text-sm"
                                    >
                                        <p class="mb-2 font-medium">
                                            Processing Attempts:
                                        </p>
                                        <div class="space-y-1">
                                            <div
                                                v-for="(
                                                    attempt, index
                                                ) in document.processing_attempts"
                                                :key="attempt.id"
                                                class="flex items-center justify-between rounded bg-muted/50 px-2 py-1"
                                            >
                                                <div
                                                    class="flex items-center gap-2"
                                                >
                                                    <component
                                                        :is="
                                                            getStatusIcon(
                                                                attempt.status,
                                                            )
                                                        "
                                                        class="h-3 w-3"
                                                    />
                                                    <span
                                                        >Attempt
                                                        {{ index + 1 }}</span
                                                    >
                                                </div>
                                                <div
                                                    class="text-muted-foreground"
                                                >
                                                    <span
                                                        v-if="
                                                            attempt.completed_at
                                                        "
                                                    >
                                                        {{
                                                            formatDuration(
                                                                attempt.started_at,
                                                                attempt.completed_at,
                                                            )
                                                        }}
                                                    </span>
                                                    <span v-else>
                                                        Started
                                                        {{
                                                            formatDate(
                                                                attempt.started_at,
                                                            )
                                                        }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="ml-4 flex items-center gap-2">
                                    <Button
                                        v-if="
                                            document.processing_status ===
                                            'completed'
                                        "
                                        variant="ghost"
                                        size="sm"
                                        :href="
                                            route('documents.show', document.id)
                                        "
                                    >
                                        <Eye class="h-4 w-4" />
                                    </Button>
                                    <Link
                                        v-if="
                                            document.processing_status ===
                                            'failed'
                                        "
                                        :href="route('processing.failed')"
                                    >
                                        <Button variant="outline" size="sm">
                                            <AlertCircle class="mr-2 h-4 w-4" />
                                            View Error
                                        </Button>
                                    </Link>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Empty State -->
                <Card v-else>
                    <CardContent class="p-12 text-center">
                        <Activity
                            class="mx-auto mb-4 h-12 w-12 text-muted-foreground"
                        />
                        <h3 class="mb-2 text-lg font-semibold">
                            No documents in processing queue
                        </h3>
                        <p class="mb-4 text-muted-foreground">
                            All documents have been processed successfully.
                            Upload new documents to see them here.
                        </p>
                        <Link :href="route('documents.create')">
                            <Button>
                                <FileText class="mr-2 h-4 w-4" />
                                Upload Document
                            </Button>
                        </Link>
                    </CardContent>
                </Card>
            </div>

            <!-- Auto-refresh Notice -->
            <Alert v-if="autoRefresh" class="mt-6">
                <RefreshCw class="h-4 w-4 animate-spin" />
                <AlertDescription>
                    This page automatically refreshes every 10 seconds to show
                    the latest processing status.
                </AlertDescription>
            </Alert>
        </div>
    </AuthenticatedLayout>
</template>
