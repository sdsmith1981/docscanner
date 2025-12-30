<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    uploading: Boolean,
});

const form = useForm({
    file: null,
    title: '',
    type: 'invoice',
});

const fileInput = ref(null);

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.file = file;

        // Auto-populate title if not set
        if (!form.title) {
            form.title = file.name.replace(/\.[^/.]+$/, '');
        }

        // Auto-detect document type from file extension
        const extension = file.name.split('.').pop().toLowerCase();
        const typeMap = {
            pdf: 'invoice',
            jpg: 'receipt',
            jpeg: 'receipt',
            png: 'receipt',
            doc: 'invoice',
            docx: 'invoice',
            xls: 'invoice',
            xlsx: 'invoice',
        };

        form.type = typeMap[extension] || 'other';
    }
};

const submit = () => {
    form.post(route('documents.store'), {
        onSuccess: () => {
            form.reset();
            fileInput.value = null;
        },
        onError: (errors) => {
            console.error('Upload errors:', errors);
        },
    });
};

const dragOver = (event) => {
    event.preventDefault();
    event.currentTarget.classList.add('border-indigo-500', 'bg-indigo-50');
};

const dragLeave = (event) => {
    event.preventDefault();
    event.currentTarget.classList.remove('border-indigo-500', 'bg-indigo-50');
};

const drop = (event) => {
    event.preventDefault();
    event.currentTarget.classList.remove('border-indigo-500', 'bg-indigo-50');

    const file = event.dataTransfer.files[0];
    if (file) {
        form.file = file;

        if (!form.title) {
            form.title = file.name.replace(/\.[^/.]+$/, '');
        }

        const extension = file.name.split('.').pop().toLowerCase();
        const typeMap = {
            pdf: 'invoice',
            jpg: 'receipt',
            jpeg: 'receipt',
            png: 'receipt',
            doc: 'invoice',
            docx: 'invoice',
            xls: 'invoice',
            xlsx: 'invoice',
        };

        form.type = typeMap[extension] || 'other';
    }
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <div class="rounded-lg bg-white shadow-xl dark:bg-gray-800">
            <div class="px-4 py-6">
                <div class="mb-6">
                    <h2
                        class="text-2xl font-semibold text-gray-900 dark:text-white"
                    >
                        Upload Document
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Upload documents for AI processing. Supports PDF,
                        images, and Office documents.
                    </p>
                </div>

                <form @submit="submit" class="space-y-6">
                    <!-- File Upload Area -->
                    <div
                        class="rounded-lg border-2 border-dashed border-gray-300 p-6 dark:border-gray-600"
                    >
                        <div class="text-center">
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
                                    d="M7 16a8 8 0 100 8 8 0 0zm-1 0a7 7 0 100 7 7 0 100 7 0-7-7-7 0zm-7-2a7 7 0 100 7 7 0-100 7 0-7-7-7 0z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M16 7v3a1 1 0 110-1h4a1 1 0 110 1v4a1 1 0 110 1h11a1 1 0 110 1v-4a1 1 0 110-1h4z"
                                />
                            </svg>
                            <p
                                class="mt-4 text-sm text-gray-600 dark:text-gray-400"
                            >
                                Drag and drop files here, or click to select
                            </p>
                            <input
                                ref="fileInput"
                                type="file"
                                @change="handleFileChange"
                                class="hidden"
                                :disabled="props.uploading"
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                            />
                            <button
                                type="button"
                                @click="fileInput?.click()"
                                :disabled="props.uploading"
                                class="relative mt-4 inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 font-medium text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <svg
                                    v-if="!props.uploading"
                                    class="-mr-2 -ml-2 h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 13a4 4 0 00 4 4 0 8a4 4 0 00 4 4 0zm0 8a8 8 0 000 8 8 0 0 0 0 0zm3 8a3 8 0 000 8 8 0 3 8 0zm-7 4a7 7 0 000 7 7 0 4 7 4z"
                                    />
                                </svg>
                                <div
                                    v-else
                                    class="-mr-2 -ml-2 h-5 w-5 animate-spin rounded-full border-2 border-solid border-indigo-600 border-t-transparent border-r-transparent border-b-transparent border-l-transparent"
                                ></div>
                                <span v-if="!props.uploading">Select File</span>
                                <span v-else>Uploading...</span>
                            </button>
                        </div>
                    </div>

                    <!-- Upload Form -->
                    <div class="space-y-4">
                        <div>
                            <label
                                for="title"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Title (optional)
                            </label>
                            <input
                                id="title"
                                v-model="form.title"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                placeholder="Document title"
                                :disabled="props.uploading"
                            />
                        </div>

                        <div>
                            <label
                                for="type"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Document Type
                            </label>
                            <select
                                id="type"
                                v-model="form.type"
                                class="mt-1 block w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                :disabled="props.uploading"
                            >
                                <option value="invoice">Invoice</option>
                                <option value="receipt">Receipt</option>
                                <option value="purchase_order">
                                    Purchase Order
                                </option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <button
                            type="submit"
                            :disabled="props.uploading || !form.file"
                            class="flex w-full justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 font-medium text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <svg
                                v-if="!props.uploading && form.file"
                                class="-mr-2 -mb-1 h-4 w-4 animate-spin"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 4v12a8 8 0 1008 8 0-4 4 4zm2 12a8 8 0 1008 8 0-2 2zm3 8a8 8 0 1008 8 0 0 0 0-2 2z"
                                />
                            </svg>
                            <span v-if="props.uploading">Uploading...</span>
                            <span v-else-if="form.file">Upload Document</span>
                            <span v-else>Select a file</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
