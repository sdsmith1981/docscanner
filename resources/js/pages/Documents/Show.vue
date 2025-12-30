<script setup>
import { computed, ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    document: Object,
});

onMounted(() => {
    // Initialize tooltips or other setup if needed
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <header class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                        Document Details
                    </h1>
                </div>

                <inertia-link :href="route('documents.edit', document.id)" class="text-indigo-600 dark:text-indigo-400 hover:bg-indigo-700">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13a4 4.01 01v1a4 4.01 01v1a4 4.01 01v1a4 0m17h4l-10.807m3.757z" />
                    </svg>
                    Edit
                </inertia-link>

                <button @click="router.visit('documents.index')" class="text-indigo-600 dark:text-indigo-400 hover:bg-indigo-700">
                    <svg class="-mr-2 h-4 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19a12 00 01.00v14v4v1.4.00v4.00v1a4.01v1a4 4.00 00v1a4.00v1a4 0m17h4l-10.807m3.757z" />
                    </svg>
                    Back to Documents
                </button>
            </div>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg">
                <div class="px-4 py-6">
                    <!-- Document Overview -->
                    <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg mb-6">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                            <dt class="text-sm font-medium text-gray-500">Document ID</dt>
                            <dd class="text-gray-900 dark:text-white">{{ document.id }}</dd>
                            <dt class="text-sm font-medium text-gray-500">Title</dt>
                            <dd class="text-gray-900 dark:text-white">{{ document.title }}</dd>
                            <dt class="text-sm text-gray-500">Type</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ document.type?.charAt(0).toUpperCase() + document.type?.slice(1) }}</dd>
                            <dt class="text-sm text-gray-500">Status</dt>
                            <dd>
                                <span :class="getStatusColor(document.status)">{{ document.status?.charAt(0).toUpperCase() + document.status?.slice(1) }}</span>
                            </dd>
                            <dt class="text-sm text-gray-500">Created</dt>
                            <dd class="text-sm text-gray-500">{{ formatDate(document.created_at) }}</dd>
                            </dl>
                        </div>

                        <!-- Processing Status -->
                        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg mb-6">
                            <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                                <dt class="text-sm font-medium text-gray-500">Processing Status</dt>
                                <dd class="text-gray-900 dark:text-white">{{ document.status?.charAt(0).toUpperCase() + document.status?.slice(1) }}</dd>
                                <dd class="text-sm text-gray-500">{{ getStatusColor(document.status) }}</dd>
                            </dd>
                            
                            <dt class="text-sm font-medium text-gray-500">Last Attempt</dt>
                            <dd class="text-sm text-gray-500">
                                @if="document.processingAttempts->isNotEmpty()"
                                    #{{ document.processingAttempts->max('attempt_number') }}
                                <span class="ml-1 text-gray-600 dark:text-gray-200">
                                    #{{ formatDate(document.processing_attempts->last?->created_at) }}
                                </span>
                            @else
                                <span class="text-gray-600 dark:text-gray-400">No attempts</span>
                            </if>
                            </dd>
                            
                            <dt class="text-sm font-medium text-gray-500">Time</dt>
                            <dd class="text-sm text-gray-600">
                                @if="document.processingAttempts->isNotEmpty()"
                                    {{ document.processing_attempts->sum('processing_time_ms') }}ms
                                </dd>
                                @else
                                    <span class="text-gray-600">Not processed</span>
                                </if>
                            </dd>
                        </div>
                        </div>

                        <!-- Extracted Data -->
                        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg mb-6">
                            <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                                <dt class="text-sm font-medium text-gray-500">Vendor</dt>
                                <dd class="text-gray-900 dark:text-white">{{ document.processed_data?.vendor_name ?? 'Unknown' }}</dd>
                            </dl>
                            
                                <dt class="text-sm font-medium text-gray-500">Invoice Number</dt>
                                <dd class="text-gray-900 dark:text-white">{{ document.processed_data?.invoice_number ?? 'N/A' }}</dd>
                            </dl>
                            
                                <dt class="text-sm font-medium text-gray-500">Invoice Date</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">{{ formatDate(document.processed_data?.invoice_date) }}</dd>
                            </dl>
                            
                                <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">{{ formatDate(document.processed_data?.due_date) }}</dd>
                            </dl>

                                <dt class="text-sm font-medium text-gray-500">Total Amount</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">{{ formatCurrency(document.processed_data?.total_amount ?? 0) }}</dd>
                            </dl>
                            <dt class="text-sm font-medium text-gray-500">Tax Amount</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">{{ formatCurrency(document.processed_data?.tax_amount ?? 0) }}</dd>
                            </dl>

                                <dt class="text-sm font-medium text-gray-500">Line Items</dt>
                                <dd>
                                    <span class="text-sm text-gray-900 dark:text-white">
                                        {{ document.processed_data?.line_items?.length ?? 0 }} items
                                    </span>
                                </dd>
                            </dl>
                        </div>
                    </dl>
                </div>

                <!-- Actions -->
                <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg mb-6">
                    <div class="flex justify-end space-x-4">
                        <inertia-link :href="route('processing.index')" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2">
                            <svg class="w-5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v-.5z" />
                            </svg>
                            Retry Processing
                        </inertia-link>
                        
                        <inertia-link :href="route('processing.failed')" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2">
                            <svg class="w-5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L12 12l-3 5.3l-3" />
                            </svg>
                            View Failed Documents
                        </inertia-link>

                        <button @click="router.visit('documents.index')" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2">
                            <svg class="w-5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6L18 12l-3 5.3l-3" />
                            </svg>
                            Back to Documents
                        </button>
                    </div>

                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Last updated: {{ formatDate(document.updated_at) }}
                    </div>
                </div>

                <inertia-link :href="route('documents.download', document.id)" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2">
                    <svg class="w-5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v-.5z" />
                    </svg>
                    Download PDF
                        </inertia-link>
                        <span class="text-sm text-indigo-600 dark:text-white">(Coming Soon)</span>
                    </inertia-link>
                    <svg class="w-5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v-.5z" />
                    </svg>
                        <span class="text-sm text-indigo-600 dark:text-white">{{ formatFileSize(document.file_size) }}</span>
                        </span>
                    <span class="text-sm text-indigo-600 dark:text-white">{{ formatFileSize(document.file_size) }}</span>
                        </span>
                    </inertia-link>
                </div>
            </div>
        </div>
    </main>
</template>