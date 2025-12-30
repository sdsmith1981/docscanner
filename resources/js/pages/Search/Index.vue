<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AuthenticatedLayout from '@/layouts/auth/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { debounce } from 'lodash-es';
import {
    Calendar,
    Download,
    Eye,
    File,
    FileText as FileDoc,
    FileText,
    Filter,
    Image,
    Search,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface SearchDocument {
    id: number;
    title: string;
    type: string;
    description?: string;
    file_name: string;
    file_size: number;
    file_mime_type: string;
    created_at: string;
    updated_at: string;
    processing_status?: string;
    highlighted?: {
        title?: string;
        description?: string;
        content?: string;
    };
    _highlights?: Record<string, string[]>;
}

interface SearchResponse {
    data: SearchDocument[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    filters?: {
        types: string[];
        date_range?: string;
    };
}

const props = defineProps<{
    documents: SearchDocument[];
    meta: SearchResponse['meta'];
    filters?: {
        query?: string;
        type?: string;
        date_from?: string;
        date_to?: string;
    };
}>();

const searchQuery = ref(props.filters?.query ?? '');
const selectedType = ref(props.filters?.type ?? '');
const dateFrom = ref(props.filters?.date_from ?? '');
const dateTo = ref(props.filters?.date_to ?? '');
const showFilters = ref(false);

// Debounced search function
const debouncedSearch = debounce(() => {
    performSearch();
}, 300);

// Watch search query changes
watch(searchQuery, debouncedSearch);

const performSearch = () => {
    const params: Record<string, string> = {};

    if (searchQuery.value.trim()) {
        params.query = searchQuery.value.trim();
    }
    if (selectedType.value) {
        params.type = selectedType.value;
    }
    if (dateFrom.value) {
        params.date_from = dateFrom.value;
    }
    if (dateTo.value) {
        params.date_to = dateTo.value;
    }

    router.get(route('search.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    selectedType.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    performSearch();
};

const highlightText = (text: string, highlights?: string[]): string => {
    if (!highlights || highlights.length === 0) return text;

    let highlightedText = text;
    highlights.forEach((highlight) => {
        const regex = new RegExp(`(${highlight})`, 'gi');
        highlightedText = highlightedText.replace(regex, '<mark>$1</mark>');
    });

    return highlightedText;
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
    return new Date(dateString).toLocaleDateString();
};

const getStatusVariant = (status?: string) => {
    switch (status) {
        case 'completed':
            return 'default';
        case 'failed':
            return 'destructive';
        case 'processing':
            return 'secondary';
        default:
            return 'outline';
    }
};

const activeFiltersCount = computed(() => {
    let count = 0;
    if (selectedType.value) count++;
    if (dateFrom.value) count++;
    if (dateTo.value) count++;
    return count;
});

const documentTypes = [
    { value: 'invoice', label: 'Invoice' },
    { value: 'receipt', label: 'Receipt' },
    { value: 'purchase_order', label: 'Purchase Order' },
    { value: 'other', label: 'Other' },
];
</script>

<template>
    <Head title="Search Documents" />

    <AuthenticatedLayout>
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold tracking-tight">
                    Search Documents
                </h1>
                <p class="text-muted-foreground">
                    Search through your documents using keywords, filters, and
                    advanced options.
                </p>
            </div>

            <!-- Search Section -->
            <Card class="mb-6">
                <CardHeader>
                    <CardTitle class="flex items-center">
                        <Search class="mr-2 h-5 w-5" />
                        Search
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-col gap-4 lg:flex-row">
                        <!-- Search Input -->
                        <div class="flex-1">
                            <div class="relative">
                                <Search
                                    class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-muted-foreground"
                                />
                                <Input
                                    v-model="searchQuery"
                                    placeholder="Search documents by title, description, or content..."
                                    class="pl-10"
                                />
                            </div>
                        </div>

                        <!-- Filter Toggle -->
                        <Button
                            variant="outline"
                            @click="showFilters = !showFilters"
                            class="relative"
                        >
                            <Filter class="mr-2 h-4 w-4" />
                            Filters
                            <Badge
                                v-if="activeFiltersCount > 0"
                                variant="secondary"
                                class="ml-2 px-1.5 py-0.5 text-xs"
                            >
                                {{ activeFiltersCount }}
                            </Badge>
                        </Button>
                    </div>

                    <!-- Advanced Filters -->
                    <div
                        v-if="showFilters"
                        class="mt-4 space-y-4 border-t pt-4"
                    >
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <!-- Document Type Filter -->
                            <div>
                                <label class="text-sm font-medium"
                                    >Document Type</label
                                >
                                <select
                                    v-model="selectedType"
                                    class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                    @change="performSearch"
                                >
                                    <option value="">All Types</option>
                                    <option
                                        v-for="type in documentTypes"
                                        :key="type.value"
                                        :value="type.value"
                                    >
                                        {{ type.label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Date From Filter -->
                            <div>
                                <label class="text-sm font-medium"
                                    >Date From</label
                                >
                                <Input
                                    v-model="dateFrom"
                                    type="date"
                                    class="mt-1"
                                    @change="performSearch"
                                />
                            </div>

                            <!-- Date To Filter -->
                            <div>
                                <label class="text-sm font-medium"
                                    >Date To</label
                                >
                                <Input
                                    v-model="dateTo"
                                    type="date"
                                    class="mt-1"
                                    @change="performSearch"
                                />
                            </div>
                        </div>

                        <!-- Clear Filters -->
                        <div class="flex justify-end">
                            <Button
                                variant="outline"
                                size="sm"
                                @click="clearFilters"
                            >
                                <X class="mr-2 h-4 w-4" />
                                Clear Filters
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Results Section -->
            <div class="space-y-4">
                <!-- Results Header -->
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold">
                            {{ meta.total }}
                            {{
                                meta.total === 1 ? 'Document' : 'Documents'
                            }}
                            Found
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            {{ meta.from }}-{{ meta.to }} of
                            {{ meta.total }} results
                        </p>
                    </div>
                </div>

                <!-- Documents List -->
                <div v-if="documents.length > 0" class="space-y-4">
                    <Card
                        v-for="document in documents"
                        :key="document.id"
                        class="transition-shadow hover:shadow-md"
                    >
                        <CardContent class="p-6">
                            <div class="flex items-start justify-between">
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
                                            <h3
                                                class="text-lg font-semibold"
                                                v-html="
                                                    highlightText(
                                                        document.title,
                                                        document._highlights
                                                            ?.title,
                                                    )
                                                "
                                            />
                                        </Link>
                                        <Badge variant="outline">{{
                                            document.type
                                        }}</Badge>
                                        <Badge
                                            v-if="document.processing_status"
                                            :variant="
                                                getStatusVariant(
                                                    document.processing_status,
                                                )
                                            "
                                        >
                                            {{ document.processing_status }}
                                        </Badge>
                                    </div>

                                    <!-- Description -->
                                    <p
                                        v-if="document.description"
                                        class="mb-3 line-clamp-2 text-muted-foreground"
                                        v-html="
                                            highlightText(
                                                document.description,
                                                document._highlights
                                                    ?.description,
                                            )
                                        "
                                    />

                                    <!-- Content Highlights -->
                                    <div
                                        v-if="
                                            document._highlights?.content &&
                                            document._highlights.content
                                                .length > 0
                                        "
                                        class="mb-3 text-sm"
                                    >
                                        <span class="text-muted-foreground"
                                            >...
                                        </span>
                                        <span
                                            v-html="
                                                highlightText(
                                                    document._highlights.content.join(
                                                        ' ... ',
                                                    ),
                                                    document._highlights
                                                        .content,
                                                )
                                            "
                                        />
                                        <span class="text-muted-foreground">
                                            ...</span
                                        >
                                    </div>

                                    <!-- Metadata -->
                                    <div
                                        class="flex items-center gap-4 text-sm text-muted-foreground"
                                    >
                                        <div class="flex items-center">
                                            <FileText class="mr-1 h-3 w-3" />
                                            {{ document.file_name }}
                                        </div>
                                        <div class="flex items-center">
                                            <Calendar class="mr-1 h-3 w-3" />
                                            {{
                                                formatDate(document.created_at)
                                            }}
                                        </div>
                                        <div>
                                            {{
                                                formatFileSize(
                                                    document.file_size,
                                                )
                                            }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="ml-4 flex items-center gap-2">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        :href="
                                            route('documents.show', document.id)
                                        "
                                    >
                                        <Eye class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        :href="
                                            route(
                                                'documents.download',
                                                document.id,
                                            )
                                        "
                                    >
                                        <Download class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- No Results -->
                <Card v-else>
                    <CardContent class="p-12 text-center">
                        <Search
                            class="mx-auto mb-4 h-12 w-12 text-muted-foreground"
                        />
                        <h3 class="mb-2 text-lg font-semibold">
                            No documents found
                        </h3>
                        <p class="mb-4 text-muted-foreground">
                            {{
                                searchQuery
                                    ? 'Try adjusting your search terms or filters.'
                                    : 'Upload some documents to get started with searching.'
                            }}
                        </p>
                        <div
                            v-if="!searchQuery"
                            class="flex justify-center gap-4"
                        >
                            <Link :href="route('documents.create')">
                                <Button>
                                    <FileText class="mr-2 h-4 w-4" />
                                    Upload Document
                                </Button>
                            </Link>
                            <Link :href="route('documents.index')">
                                <Button variant="outline">
                                    Browse Documents
                                </Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Pagination (if applicable) -->
            <div v-if="meta.last_page > 1" class="mt-8 flex justify-center">
                <!-- Pagination component would go here -->
                <p class="text-sm text-muted-foreground">
                    Page {{ meta.current_page }} of {{ meta.last_page }}
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

:deep(mark) {
    background-color: #fef08a;
    color: #713f12;
    padding: 1px 2px;
    border-radius: 2px;
}
</style>
