<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    documents: Object,
    filters: Object,
    query: String,
});

const form = useForm({
    type: props.filters.type || '',
    status: props.filters.status || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
});

const searchQuery = ref(props.query);

const submitSearch = () => {
    form.get('type', props.filters.type || '');
    form.get('status', props.filters.status || '');
    form.get('date_from', props.filters.date_from || '');
    form.get('date_to', props.filters.date_to || '');

    router.get(
        route('search.index'),
        {
            q: searchQuery.value,
            ...form.data(),
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const clearFilters = () => {
    form.reset();
    router.get(
        route('search.index'),
        {
            q: searchQuery.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-GB', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
    }).format(amount);
};
</script>

<template>
    <div class="space-y-6">
        <!-- Search Header -->
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
            <div class="flex flex-col gap-4 sm:flex-row">
                <div class="flex-1">
                    <label
                        for="search"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >
                        Search Documents
                    </label>
                    <div class="relative mt-1 rounded-md shadow-sm">
                        <input
                            id="search"
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search by title, vendor, or invoice number..."
                            class="block w-full rounded-md border-gray-300 px-4 py-2 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            @keyup.enter="submitSearch"
                        />
                        <button
                            @click="submitSearch"
                            class="absolute inset-y-0 right-0 rounded-r-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                        >
                            Search
                        </button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="flex flex-col gap-4 sm:flex-row">
                    <div>
                        <label
                            for="type"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Type
                        </label>
                        <select
                            id="type"
                            v-model="form.type"
                            @change="submitSearch"
                            class="mt-1 block w-full rounded-md border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">All Types</option>
                            <option value="invoice">Invoices</option>
                            <option value="receipt">Receipts</option>
                            <option value="purchase_order">
                                Purchase Orders
                            </option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label
                            for="status"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Status
                        </label>
                        <select
                            id="status"
                            v-model="form.status"
                            @change="submitSearch"
                            class="mt-1 block w-full rounded-md border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="processed">Processed</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                </div>

                <!-- Date Range -->
                <div class="flex flex-col gap-4 sm:flex-row">
                    <div>
                        <label
                            for="date_from"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            From Date
                        </label>
                        <input
                            id="date_from"
                            v-model="form.date_from"
                            type="date"
                            @change="submitSearch"
                            class="mt-1 block w-full rounded-md border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />
                    </div>

                    <div>
                        <label
                            for="date_to"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            To Date
                        </label>
                        <input
                            id="date_to"
                            v-model="form.date_to"
                            type="date"
                            @change="submitSearch"
                            class="mt-1 block w-full rounded-md border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />
                    </div>
                </div>

                <button
                    @click="clearFilters"
                    class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                >
                    Clear Filters
                </button>
            </div>
        </div>

        <!-- Results Count -->
        <div
            v-if="documents.data.length > 0"
            class="rounded-lg bg-white p-4 shadow dark:bg-gray-800"
        >
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Found {{ documents.total }} document{{
                    documents.total !== 1 ? 's' : ''
                }}
                <span v-if="searchQuery" class="ml-2"
                    >for "{{ searchQuery }}"</span
                >
            </p>
        </div>

        <!-- Documents List -->
        <div
            class="overflow-hidden bg-white shadow sm:rounded-md dark:bg-gray-800"
        >
            <ul
                v-if="documents.data.length > 0"
                role="list"
                class="divide-y divide-gray-200 dark:divide-gray-700"
            >
                <li
                    v-for="document in documents.data"
                    :key="document.id"
                    class="hover:bg-gray-50 dark:hover:bg-gray-900"
                >
                    <inertia-link
                        :href="route('documents.show', document.id)"
                        class="block p-4"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center">
                                    <!-- Document Icon -->
                                    <div class="h-10 w-10 flex-shrink-0">
                                        <svg
                                            class="h-6 w-6 text-gray-400"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12h6m-6 6h6m-6 6h6"
                                            />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p
                                            class="truncate text-sm font-medium text-gray-900 dark:text-white"
                                        >
                                            {{ document.title }}
                                        </p>
                                        <p
                                            class="text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            {{
                                                document.type
                                                    ?.charAt(0)
                                                    .toUpperCase() +
                                                document.type?.slice(1)
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Status Badge -->
                                <div class="flex items-center">
                                    <span
                                        :class="
                                            {
                                                pending:
                                                    'bg-yellow-100 text-yellow-800',
                                                processing:
                                                    'bg-blue-100 text-blue-800',
                                                processed:
                                                    'bg-green-100 text-green-800',
                                                failed: 'bg-red-100 text-red-800',
                                            }[document.status]
                                        "
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    >
                                        {{
                                            document.status
                                                ?.charAt(0)
                                                .toUpperCase() +
                                            document.status?.slice(1)
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Document Details -->
                        <div class="mt-2">
                            <p
                                v-if="document.processed_data?.vendor_name"
                                class="text-sm text-gray-600 dark:text-gray-400"
                            >
                                Vendor:
                                {{ document.processed_data.vendor_name }}
                            </p>
                            <p
                                v-if="document.processed_data?.invoice_number"
                                class="text-sm text-gray-600 dark:text-gray-400"
                            >
                                Invoice:
                                {{ document.processed_data.invoice_number }}
                            </p>
                            <p
                                v-if="document.processed_data?.total_amount"
                                class="text-sm text-gray-600 dark:text-gray-400"
                            >
                                Total:
                                {{
                                    formatCurrency(
                                        document.processed_data.total_amount,
                                    )
                                }}
                            </p>
                            <p
                                v-if="document.processed_data?.invoice_date"
                                class="text-sm text-gray-600 dark:text-gray-400"
                            >
                                Date:
                                {{
                                    formatDate(
                                        document.processed_data.invoice_date,
                                    )
                                }}
                            </p>
                        </div>
                    </inertia-link>
                </li>
            </ul>

            <!-- Empty State -->
            <div v-else class="py-12 text-center">
                <svg
                    class="mx-auto h-12 w-12 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 13h6m-3 9a9 9 0 0118 18 0a9 9 0 00-18 0h6a9 9 0 00-18 0m0 18a9 9 0 0118 0v6a9 9 0 00-18-18 9H18a9 9 0 00-18-18zm-9 2h6a9 9 0 00-18-18v6a9 9 0 00-18 18H0a9 9 0 00-18-18z"
                    />
                </svg>
                <h3
                    class="mt-2 text-sm font-medium text-gray-900 dark:text-white"
                >
                    No documents found
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{
                        searchQuery
                            ? `No documents found for "${searchQuery}"`
                            : 'No documents found. Try adjusting your search or filters.'
                    }}
                </p>
            </div>
        </div>

        <!-- Pagination -->
        <div
            v-if="documents.links.length > 3"
            class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 sm:py-4 dark:border-gray-700 dark:bg-gray-800"
        >
            <div class="flex flex-1 justify-between sm:hidden">
                <a
                    :href="documents.links.prev"
                    class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-indigo-500 dark:focus:ring-offset-gray-700"
                >
                    Previous
                </a>
                <a
                    :href="documents.links.next"
                    class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-indigo-500 dark:focus:ring-offset-gray-700"
                >
                    Next
                </a>
            </div>
            <div
                class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between"
            >
                <div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        Showing
                        <span class="font-medium"
                            >{{ documents.from }}-{{ documents.to }}</span
                        >
                        of
                        <span class="font-medium">{{ documents.total }}</span>
                        results
                    </p>
                </div>
                <nav
                    class="relative z-0 inline-flex -space-x-px rounded-md shadow-sm"
                    aria-label="Pagination"
                >
                    <a
                        v-for="(link, i) in documents.links"
                        :key="i"
                        :href="link.url"
                        :class="{
                            'px-3 py-2 text-sm font-medium': true,
                            'border border-gray-300 bg-white text-gray-500 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700':
                                link.active,
                            'border-indigo-500 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-900 dark:hover:bg-indigo-800':
                                !link.active,
                        }"
                    >
                        {{ link.label }}
                    </a>
                </nav>
            </div>
        </div>
    </div>
</template>
