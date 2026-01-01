<script setup>
import { computed, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    documents: Object,
});

const searchQuery = ref('');
const currentPage = ref(1);
const selectedType = ref('');
const selectedStatus = ref('');
const sortBy = ref('created_at');
const sortOrder = ref('desc');

const filters = computed(() => ({
    type: selectedType.value,
    status: selectedStatus.value,
    search: searchQuery.value,
}));

const applyFilters = () => {
    const params = new URLSearchParams();
    
    Object.entries(filters).forEach(([key, value]) => {
        if (value) {
            params.append(key, value);
        }
    });
    
    return params.toString();
};

const filteredDocuments = computed(() => {
    let filtered = props.documents.data;
    
    // Apply type filter
    if (filters.type) {
        filtered = filtered.filter(doc => doc.type === filters.type);
    }
    
    // Apply status filter
    if (filters.status) {
        filtered = filtered.filter(doc => doc.status === filters.status);
    }
    
    // Apply search filter
    if (filters.search) {
        const searchLower = filters.search.toLowerCase();
        filtered = filtered.filter(doc => 
            doc.title.toLowerCase().includes(searchLower) ||
            doc.processed_data?.vendor_name?.toLowerCase().includes(searchLower) ||
            doc.processed_data?.invoice_number?.toLowerCase().includes(searchLower)
        );
    }
    
    // Apply sorting
    return filtered.sort((a, b) => {
        const aValue = getValue(a, sortBy.value);
        const bValue = getValue(b, sortBy.value);
        
        if (sortOrder.value === 'asc') {
            return aValue > bValue ? 1 : -1;
        }
        return aValue < bValue ? 1 : -1;
    });
});

const getValue = (obj, key) => {
    return obj[key] === null || obj[key] === undefined ? '' : obj[key];
};

const getStatusColor = (status) => {
    switch (status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'processing':
            return 'bg-blue-100 text-blue-800';
        case 'processed':
            return 'bg-green-100 text-green-800';
        case 'failed':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-GB');
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
    }).format(amount);
};

const getTypeColor = (type) => {
    switch (type) {
        case 'invoice':
            return 'bg-purple-100 text-purple-800';
        case 'receipt':
            return 'bg-blue-100 text-blue-800';
        case 'purchase_order':
            return 'bg-indigo-100 text-indigo-800';
        case 'other':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

onMounted(() => {
    // Initialize tooltips
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Header -->
        <header class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Documents
                    </h1>
                    <nav class="flex space-x-8">
                        <Link :href="route('documents.index')" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 px-3 py-2 rounded-md text-sm font-medium">
                            All Documents
                        </Link>
                        <Link :href="route('integrations.index')" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 px-3 py-2 rounded-md text-sm font-medium">
                            Integrations
                        </Link>
                        <Link :href="route('search.index')" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 px-3 py-2 rounded-md text-sm font-medium">
                            Search
                        </Link>
                        <Link :href="route('processing.index')" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 px-3 py-2 rounded-md text-sm font-medium">
                            Processing
                        </Link>
                        <Link :href="route('email-settings.edit')" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 px-3 py-2 rounded-md text-sm font-medium">
                            Email Settings
                        </Link>
                        
                        <div class="relative">
                            <button class="text-gray-400 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 p-1 rounded-md text-sm font-medium">
                                <svg class="-mr-2 -mb-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 9h6m-6 0v6h6a4 4 4 0 6z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                                </svg>
                                User Menu
                            </button>
                        </div>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Search Bar -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <input
                            v-model="searchQuery"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Search by title, vendor, or invoice number..."
                            type="text"
                            @keyup.enter="performSearch"
                        />
                    </div>

                    <div class="flex gap-2">
                        <select
                            v-model="selectedType"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            @change="resetPage"
                        >
                            <option value="">All Types</option>
                            <option value="invoice">Invoices</option>
                            <option value="receipt">Receipts</option>
                            <option value="purchase_order">Purchase Orders</option>
                            <option value="other">Other</option>
                        </select>

                        <select
                            v-model="selectedStatus"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            @change="resetPage"
                        >
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="processed">Processed</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <select
                            v-model="sortBy"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            @change="resetPage"
                        >
                            <option value="created_at">Date Created</option>
                            <option value="title">Title</option>
                        </select>

                        <select
                            v-model="sortOrder"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            @change="resetPage"
                        >
                            <option value="desc">Newest First</option>
                            <option value="asc">Oldest First</option>
                        </select>

                        <button
                            class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white px-4 py-2 border border-transparent rounded-md font-medium hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            @click="resetFilters"
                        >
                            Clear Filters
                        </button>

                        <Link :href="route('documents.create')" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-2 -mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M9 13h6m-6 0v6h6a4 4 4 0 6z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                            </svg>
                            Upload Document
                        </Link>
                    </div>
                </div>
            </div>


            <!-- Results Count -->
            <div v-if="documents.data.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Found {{ documents.total }} document{{ documents.total !== 1 ? 's' : '' }}
                    <span v-if="searchQuery" class="ml-2">for "{{ searchQuery }}"</span>
                </p>
            </div>

            <!-- Documents List -->
            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
                <ul v-if="documents.data.length > 0" class="divide-y divide-gray-200 dark:divide-gray-700" role="list">
                    <li v-for="document in documents.data" :key="document.id" class="hover:bg-gray-50 dark:hover:bg-gray-900">
                        <inertia-link :href="route('documents.show', document.id)">
                            <div class="px-4 py-3">
                                <!-- Document Header -->
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white truncate">
                                            {{ document.title }}
                                        </h3>
                                        
                                        <!-- Status Badge -->
                                        <div class="ml-4">
                                            <span
                                                :class="getStatusColor(document.status)"
                                                class="px-2 py-1 text-xs font-medium rounded-full"
                                            >
                                                {{ document.status?.charAt(0).toUpperCase() + document.status?.slice(1) }}
                                            </span>
                                        </div>
                                        
                                        <!-- Document Type -->
                                        <span
                                            :class="getTypeColor(document.type)"
                                            class="ml-2 text-xs font-medium rounded-full"
                                        >
                                            {{ document.type?.charAt(0).toUpperCase() + document.type?.slice(1) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Document Details -->
                                <div class="mt-2">
                                    <p v-if="document.processed_data?.vendor_name" class="text-sm text-gray-600 dark:text-gray-400">
                                        Vendor: {{ document.processed_data.vendor_name }}
                                    </p>
                                    <p v-if="document.processed_data?.invoice_number" class="text-sm text-gray-600 dark:text-gray-400">
                                        Invoice: {{ document.processed_data.invoice_number }}
                                    </p>
                                    <p v-if="document.processed_data?.total_amount" class="text-sm text-gray-600 dark:text-gray-400">
                                        Total: {{ formatCurrency(document.processed_data.total_amount) }}
                                    </p>
                                    <p v-if="document.processed_data?.invoice_date" class="text-sm text-gray-600 dark:text-gray-400">
                                        Date: {{ formatDate(document.processed_data.invoice_date) }}
                                    </p>
                                    <p v-if="document.processed_data?.due_date" class="text-sm text-gray-600 dark:text-gray-400">
                                        Due: {{ formatDate(document.processed_data.due_date) }}
                                    </p>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center space-x-4 mt-4">
                                    <inertia-link :href="route('documents.edit', document.id)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 px-3 py-2 rounded-md text-sm font-medium">
                                        Edit
                                    </inertia-link>
                                    
                                    <inertia-link :href="route('documents.download', document.id)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 px-3 py-2 rounded-md text-sm font-medium">
                                        Download
                                    </inertia-link>
                                    
                                    <inertia-link :href="route('processing.retry', document.id)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 px-3 py-2 rounded-md text-sm font-medium">
                                        Retry Processing
                                    </inertia-link>
                                </div>
                            </div>
                        </inertia-link>
                    </li>
                </ul>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M9 13h6m-6 0v6h6a4 4 4 0 6z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-white">No documents found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ searchQuery ? `No documents found for "${searchQuery}"` : 'No documents found. Try adjusting your search or filters.' }}
                    </p>
                    <inertia-link :href="route('documents.create')" class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-white hover:bg-indigo-700">
                        <svg class="-mr-2 -mb-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M9 13h6m-6 0v6h6a4 4 4 0 6z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                        </svg>
                        Upload Your First Document
                        </inertia-link>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="documents.links.length > 3" class="bg-white dark:bg-gray-800 px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-700">
                <div class="flex flex-1">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        Showing
                        <span class="font-medium">{{ documents.from }}-{{ documents.to }}</span>
                        of
                        <span class="font-medium">{{ documents.total }}</span>
                        results
                    </p>
                </div>
                
                <nav class="flex-1">
                    <span class="text-sm text-gray-700 dark:text-gray-300">
                        Page {{ documents.current_page }} of {{ documents.last_page }}
                    </span>
                </nav>
                
                <nav aria-label="Pagination" class="relative z-0 inline-flex -space-x-px rounded-md shadow-sm">
                    <a
                        v-for="(link, i) in documents.links"
                        :key="i"
                        :class="{
                            'px-3 py-2': true,
                            'relative block': true,
                            'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300': link.active ? 'bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-white border border-gray-300 text-gray-500 dark:text-gray-700 dark:hover:text-gray-300',
                            'rounded-md': true,
                            'text-sm font-medium': true,
                        }"
                        :href="link.url"
                    >
                        {{ link.label }}
                    </a>
                </nav>
            </div>
        </main>
    </div>
</template>