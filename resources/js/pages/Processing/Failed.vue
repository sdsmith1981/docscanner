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
import { Collapsible, CollapsibleContent } from '@/components/ui/collapsible';
import { Separator } from '@/components/ui/separator';
import AuthenticatedLayout from '@/layouts/auth/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ChevronDown,
    ChevronUp,
    Eye,
    File,
    FileText as FileDoc,
    FileText,
    Image,
    RefreshCw,
    RotateCcw,
    Trash2,
} from 'lucide-vue-next';
import { ref } from 'vue';

interface FailedDocument {
    id: number;
    title: string;
    type: string;
    description?: string;
    file_name: string;
    file_size: number;
    file_mime_type: string;
    processing_status: 'failed';
    processing_error?: string;
    processing_started_at?: string;
    processing_completed_at?: string;
    created_at: string;
    updated_at: string;
    processing_attempts: Array<{
        id: number;
        status: string;
        started_at: string;
        completed_at?: string;
        error_message?: string;
        error_details?: string;
    }>;
}

const props = defineProps<{
    documents: FailedDocument[];
}>();

const retryForm = useForm({});
const deleteForm = useForm({});
const expandedDocuments = ref<Set<number>>(new Set());

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

const toggleExpanded = (documentId: number) => {
    if (expandedDocuments.value.has(documentId)) {
        expandedDocuments.value.delete(documentId);
    } else {
        expandedDocuments.value.add(documentId);
    }
};

const retryProcessing = (documentId: number) => {
    retryForm.post(route('processing.retry', documentId), {
        onSuccess: () => {
            // The page will reload automatically via Inertia
        },
        onError: (errors) => {
            console.error('Retry failed:', errors);
        },
    });
};

const deleteDocument = (documentId: number) => {
    if (
        confirm(
            'Are you sure you want to delete this document? This action cannot be undone.',
        )
    ) {
        deleteForm.delete(route('documents.destroy', documentId), {
            onSuccess: () => {
                // The page will reload automatically via Inertia
            },
        });
    }
};

const retryAll = () => {
    if (
        confirm(
            'Are you sure you want to retry processing all failed documents?',
        )
    ) {
        const documentIds = props.documents.map((doc) => doc.id);
        retryForm.post(route('processing.retry-all'), {
            data: { document_ids: documentIds },
            onSuccess: () => {
                // The page will reload automatically via Inertia
            },
        });
    }
};

const getErrorSeverity = (errorMessage?: string): 'low' | 'medium' | 'high' => {
    if (!errorMessage) return 'low';

    const lowerError = errorMessage.toLowerCase();
    if (
        lowerError.includes('timeout') ||
        lowerError.includes('network') ||
        lowerError.includes('connection')
    ) {
        return 'low';
    } else if (
        lowerError.includes('format') ||
        lowerError.includes('corrupt') ||
        lowerError.includes('invalid')
    ) {
        return 'high';
    }
    return 'medium';
};

const getErrorBadgeVariant = (severity: 'low' | 'medium' | 'high') => {
    switch (severity) {
        case 'low':
            return 'secondary';
        case 'medium':
            return 'destructive';
        case 'high':
            return 'destructive';
        default:
            return 'destructive';
    }
};

const getErrorLabel = (severity: 'low' | 'medium' | 'high') => {
    switch (severity) {
        case 'low':
            return 'Retry Likely';
        case 'medium':
            return 'Manual Review';
        case 'high':
            return 'File Issue';
        default:
            return 'Error';
    }
};
</script>

<template>
    <Head title="Failed Processing" />

    <AuthenticatedLayout>
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">
                            Failed Processing
                        </h1>
                        <p class="text-muted-foreground">
                            Review and resolve documents that failed during
                            processing
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Link :href="route('processing.index')">
                            <Button variant="outline" size="sm">
                                <AlertTriangle class="mr-2 h-4 w-4" />
                                Back to Queue
                            </Button>
                        </Link>
                        <Button
                            v-if="documents.length > 0"
                            variant="outline"
                            size="sm"
                            @click="retryAll"
                            :disabled="retryForm.processing"
                        >
                            <RefreshCw
                                :class="[
                                    'mr-2 h-4 w-4',
                                    retryForm.processing && 'animate-spin',
                                ]"
                            />
                            Retry All
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Summary Card -->
            <Card v-if="documents.length > 0" class="mb-6">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <AlertTriangle class="h-8 w-8 text-destructive" />
                            <div>
                                <h3 class="text-lg font-semibold">
                                    {{ documents.length }} Documents Failed
                                </h3>
                                <p class="text-sm text-muted-foreground">
                                    These documents encountered errors during
                                    processing and require attention.
                                </p>
                            </div>
                        </div>
                        <Button
                            @click="retryAll"
                            :disabled="retryForm.processing"
                        >
                            <RotateCcw
                                :class="[
                                    'mr-2 h-4 w-4',
                                    retryForm.processing && 'animate-spin',
                                ]"
                            />
                            {{
                                retryForm.processing
                                    ? 'Retrying...'
                                    : 'Retry All'
                            }}
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Failed Documents List -->
            <div class="space-y-4">
                <Card
                    v-for="document in documents"
                    :key="document.id"
                    class="transition-shadow hover:shadow-md"
                >
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <component
                                    :is="getFileIcon(document.file_mime_type)"
                                    class="h-5 w-5 text-muted-foreground"
                                />
                                <div>
                                    <CardTitle class="text-lg">{{
                                        document.title
                                    }}</CardTitle>
                                    <CardDescription
                                        class="mt-1 flex items-center gap-2"
                                    >
                                        <span>{{ document.file_name }}</span>
                                        <Separator
                                            orientation="vertical"
                                            class="h-4"
                                        />
                                        <span>{{
                                            formatFileSize(document.file_size)
                                        }}</span>
                                        <Separator
                                            orientation="vertical"
                                            class="h-4"
                                        />
                                        <Badge variant="outline">{{
                                            document.type
                                        }}</Badge>
                                    </CardDescription>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Badge
                                    :variant="
                                        getErrorBadgeVariant(
                                            getErrorSeverity(
                                                document.processing_error,
                                            ),
                                        )
                                    "
                                >
                                    {{
                                        getErrorLabel(
                                            getErrorSeverity(
                                                document.processing_error,
                                            ),
                                        )
                                    }}
                                </Badge>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="toggleExpanded(document.id)"
                                >
                                    {{
                                        expandedDocuments.has(document.id)
                                            ? ChevronUp
                                            : ChevronDown
                                    }}
                                </Button>
                            </div>
                        </div>
                    </CardHeader>

                    <CardContent class="pt-0">
                        <!-- Error Message -->
                        <Alert variant="destructive" class="mb-4">
                            <AlertTriangle class="h-4 w-4" />
                            <AlertDescription>
                                <strong>Processing Error:</strong>
                                {{
                                    document.processing_error ||
                                    'Unknown error occurred'
                                }}
                            </AlertDescription>
                        </Alert>

                        <!-- Expanded Details -->
                        <Collapsible :open="expandedDocuments.has(document.id)">
                            <CollapsibleContent class="space-y-4">
                                <!-- Processing Attempts -->
                                <div
                                    v-if="
                                        document.processing_attempts.length > 0
                                    "
                                >
                                    <h4 class="mb-2 font-medium">
                                        Processing Attempts:
                                    </h4>
                                    <div class="space-y-2">
                                        <div
                                            v-for="(
                                                attempt, index
                                            ) in document.processing_attempts"
                                            :key="attempt.id"
                                            class="rounded-lg border bg-muted/50 p-3"
                                        >
                                            <div
                                                class="mb-2 flex items-center justify-between"
                                            >
                                                <div
                                                    class="flex items-center gap-2"
                                                >
                                                    <Badge
                                                        variant="outline"
                                                        class="text-xs"
                                                    >
                                                        Attempt {{ index + 1 }}
                                                    </Badge>
                                                    <Badge
                                                        variant="secondary"
                                                        class="text-xs"
                                                    >
                                                        {{ attempt.status }}
                                                    </Badge>
                                                </div>
                                                <span
                                                    class="text-xs text-muted-foreground"
                                                >
                                                    {{
                                                        formatDate(
                                                            attempt.started_at,
                                                        )
                                                    }}
                                                </span>
                                            </div>

                                            <div
                                                v-if="attempt.error_message"
                                                class="mb-2 text-sm text-destructive"
                                            >
                                                {{ attempt.error_message }}
                                            </div>

                                            <div
                                                v-if="attempt.error_details"
                                                class="rounded bg-muted p-2 font-mono text-xs text-muted-foreground"
                                            >
                                                {{ attempt.error_details }}
                                            </div>

                                            <div
                                                class="mt-2 text-xs text-muted-foreground"
                                            >
                                                Duration:
                                                {{
                                                    formatDuration(
                                                        attempt.started_at,
                                                        attempt.completed_at,
                                                    )
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Document Metadata -->
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="font-medium"
                                            >Created:</span
                                        >
                                        <span
                                            class="ml-2 text-muted-foreground"
                                            >{{
                                                formatDate(document.created_at)
                                            }}</span
                                        >
                                    </div>
                                    <div>
                                        <span class="font-medium"
                                            >Last Updated:</span
                                        >
                                        <span
                                            class="ml-2 text-muted-foreground"
                                            >{{
                                                formatDate(document.updated_at)
                                            }}</span
                                        >
                                    </div>
                                    <div>
                                        <span class="font-medium"
                                            >File Type:</span
                                        >
                                        <span
                                            class="ml-2 text-muted-foreground"
                                            >{{ document.file_mime_type }}</span
                                        >
                                    </div>
                                    <div>
                                        <span class="font-medium"
                                            >Processing Started:</span
                                        >
                                        <span
                                            class="ml-2 text-muted-foreground"
                                        >
                                            {{
                                                document.processing_started_at
                                                    ? formatDate(
                                                          document.processing_started_at,
                                                      )
                                                    : 'Never'
                                            }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div v-if="document.description">
                                    <h4 class="mb-1 font-medium">
                                        Description:
                                    </h4>
                                    <p class="text-sm text-muted-foreground">
                                        {{ document.description }}
                                    </p>
                                </div>
                            </CollapsibleContent>
                        </Collapsible>

                        <!-- Actions -->
                        <div
                            class="mt-4 flex items-center justify-end gap-2 border-t pt-4"
                        >
                            <Link :href="route('documents.show', document.id)">
                                <Button variant="outline" size="sm">
                                    <Eye class="mr-2 h-4 w-4" />
                                    View Details
                                </Button>
                            </Link>
                            <Button
                                variant="destructive"
                                size="sm"
                                @click="deleteDocument(document.id)"
                                :disabled="deleteForm.processing"
                            >
                                <Trash2 class="mr-2 h-4 w-4" />
                                Delete
                            </Button>
                            <Button
                                size="sm"
                                @click="retryProcessing(document.id)"
                                :disabled="retryForm.processing"
                            >
                                <RefreshCw
                                    :class="[
                                        'mr-2 h-4 w-4',
                                        retryForm.processing && 'animate-spin',
                                    ]"
                                />
                                {{
                                    retryForm.processing
                                        ? 'Retrying...'
                                        : 'Retry Processing'
                                }}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <Card v-if="documents.length === 0">
                <CardContent class="p-12 text-center">
                    <AlertTriangle
                        class="mx-auto mb-4 h-12 w-12 text-muted-foreground"
                    />
                    <h3 class="mb-2 text-lg font-semibold">
                        No Failed Documents
                    </h3>
                    <p class="mb-4 text-muted-foreground">
                        Great! All documents have been processed successfully.
                    </p>
                    <div class="flex justify-center gap-4">
                        <Link :href="route('processing.index')">
                            <Button>
                                <Activity class="mr-2 h-4 w-4" />
                                View Processing Queue
                            </Button>
                        </Link>
                        <Link :href="route('documents.index')">
                            <Button variant="outline">
                                <FileText class="mr-2 h-4 w-4" />
                                Browse Documents
                            </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
