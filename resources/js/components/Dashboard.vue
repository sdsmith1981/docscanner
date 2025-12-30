<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    recentDocuments: Object,
    totalDocuments: Number,
    processingQueue: Number,
    failedDocuments: Number,
    integrations: Object,
});

onMounted(() => {
    // Initialize any real-time updates if needed
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <div class="bg-white dark:bg-gray-800 shadow-lg">
            <!-- Navigation Header -->
            <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex-shrink-0">
                                <img class="h-8 w-auto" src="/images/logo.svg" alt="DocScanner" />
                            </div>
                            
                            <!-- Navigation Links -->
                            <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                                <Link :href="route('documents.index')" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 px-3 py-2 rounded-md text-sm font-medium">
                                    Documents
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
                            </div>
                            
                            <!-- User Menu -->
                            <div class="relative">
                                <button class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 p-1 rounded-md">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 4 4 0 0 0a4 4 0 0 1 1 0 0 0-4 4 0 0-4 4 0 0-1-1 0 0-2 2a2 2 0 0 0 1 2 0 2 2 0-1 4 0 0 2zm1 1h6a1 1 0 0 1v2a1 1 0 0 1v-2a1 1 0 0 1h6a1 1 0 0 1-1-1 0-2 2a1 1 0 0 1-2zm10 0a1 1 0 1h10a1 1 0 0 1v-2a1 1 0 0 0-1-2 2 2 2 0 0 0 2z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                <!-- Welcome Section -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden rounded-lg shadow">
                    <div class="px-4 py-5 sm:p-6 sm:px-8">
                        <div class="text-center">
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                                Welcome to DocScanner
                            </h1>
                            <p class="mt-2 text-lg text-gray-600 dark:text-gray-400">
                                Your document processing SaaS is ready. Upload documents, search invoices, and integrate with your accounting software.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Total Documents -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden rounded-lg shadow">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900 rounded-lg p-3">
                                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 6v6m0 0h6" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ totalDocuments }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Documents</p>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Documents -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden rounded-lg shadow">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Documents</h3>
                            <div v-if="recentDocuments.length > 0" class="space-y-3">
                                <div v-for="document in recentDocuments.slice(0, 5)" :key="document.id" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ document.title }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ document.type?.charAt(0).toUpperCase() + document.type?.slice(1) }}</p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span :class="{
                                            'pending': 'bg-yellow-100 text-yellow-800',
                                            'processing': 'bg-blue-100 text-blue-800',
                                            'processed': 'bg-green-100 text-green-800',
                                            'failed': 'bg-red-100 text-red-800'
                                        }[document.status]" class="px-2 py-1 text-xs font-medium rounded-full">
                                            {{ document.status?.charAt(0).toUpperCase() + document.status?.slice(1) }}
                                        </span>
                                        <Link :href="route('documents.show', document.id)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 text-sm font-medium">
                                            View
                                        </Link>
                                    </div>
                                </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-12">
                                <p class="text-sm text-gray-500 dark:text-gray-400">No recent documents</p>
                                <Link :href="route('documents.index')" class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-white hover:bg-indigo-700">
                                    View All Documents
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Processing Queue -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden rounded-lg shadow">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-yellow-100 dark:bg-yellow-900 rounded-lg p-3">
                                    <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v1a3 3 0 0 0-1.541-1.403 1.403 0 0-3-3z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ processingQueue }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">In Processing Queue</p>
                            </div>
                        </div>
                    </div>

                    <!-- Failed Documents -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden rounded-lg shadow">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-red-100 dark:bg-red-900 rounded-lg p-3">
                                    <svg class="h-6 w-6 text-red-600 dark:text-red-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2a1 1 0 0 0-2 2-1 1 0 0-1 1-1-1h4a1 1 0 0 0-1-1 1-1-1 1-1z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ failedDocuments }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Failed Processing</p>
                            </div>
                        </div>
                    </div>

                    <!-- Integrations Status -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden rounded-lg shadow">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-green-100 dark:bg-green-900 rounded-lg p-3">
                                    <svg class="h-6 w-6 text-green-600 dark:text-green-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 0 0-2-2-2 2 2zM3 8a5 5 0 0 0 7.5.5 5 5 7.5-7.5.5 0 0 0 0zm1 3a5 5 0 0 0 7.5.5 5 5 7.5-7.5.5 0 0 0 0-1 3-3a5 5 0 0 0 0-2 2 0-2 2 2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ Object.keys(integrations).length }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Active Integrations</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mt-8 text-center">
                    <div class="inline-flex rounded-md shadow" role="group">
                        <Link :href="route('documents.create')" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 h-5 w-5" fill="none" viewBox="0 0 20 20" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13a6 6 0 1h12a2 2 0 0 2 2v6a2 2 0 0-2.707 0-2.707 0 0 0-.53-.23.231-.408-.697-.231-.707 1.088-.706-.47-1.064-1.064z" />
                            </svg>
                            Upload Document
                        </Link>
                        <Link :href="route('integrations.create')" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="-ml-1 h-5 w-5" fill="none" viewBox="0 0 20 20" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-6 6v6m0 0h6" />
                            </svg>
                            Add Integration
                        </Link>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>