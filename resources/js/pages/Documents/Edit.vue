<script setup lang="ts">
import { Alert, AlertDescription } from '@/components/ui/alert';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AuthenticatedLayout from '@/layouts/auth/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, FileText, Save, Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    document: {
        id: number;
        title: string;
        type: string;
        description?: string;
        file_path: string;
        file_name: string;
        file_size: number;
        file_mime_type: string;
        created_at: string;
        updated_at: string;
    };
}>();

const form = useForm({
    title: props.document.title,
    type: props.document.type,
    description: props.document.description ?? '',
});

const submit = () => {
    form.put(route('documents.update', props.document.id));
};

const deleteDocument = () => {
    if (
        confirm(
            'Are you sure you want to delete this document? This action cannot be undone.',
        )
    ) {
        form.delete(route('documents.destroy', props.document.id));
    }
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
</script>

<template>
    <Head :title="`Edit Document: ${document.title}`" />

    <AuthenticatedLayout>
        <div class="container mx-auto px-4 py-8">
            <div class="mx-auto max-w-2xl">
                <!-- Header -->
                <div class="mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold tracking-tight">
                                Edit Document
                            </h1>
                            <p class="text-muted-foreground">
                                Update document information
                            </p>
                        </div>
                        <Link :href="route('documents.show', document.id)">
                            <Button variant="outline" size="sm">
                                <ArrowLeft class="mr-2 h-4 w-4" />
                                Back to Document
                            </Button>
                        </Link>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Document Information Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center">
                                <FileText class="mr-2 h-5 w-5" />
                                Document Details
                            </CardTitle>
                            <CardDescription>
                                Update the document information below.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <!-- Title -->
                            <div class="space-y-2">
                                <Label for="title">Title</Label>
                                <Input
                                    id="title"
                                    v-model="form.title"
                                    placeholder="Document title"
                                    :class="{
                                        'border-destructive': form.errors.title,
                                    }"
                                />
                                <p
                                    v-if="form.errors.title"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.title }}
                                </p>
                            </div>

                            <!-- Document Type -->
                            <div class="space-y-2">
                                <Label for="type">Document Type</Label>
                                <Select v-model="form.type">
                                    <SelectTrigger
                                        :class="{
                                            'border-destructive':
                                                form.errors.type,
                                        }"
                                    >
                                        <SelectValue
                                            placeholder="Select document type"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="invoice"
                                            >Invoice</SelectItem
                                        >
                                        <SelectItem value="receipt"
                                            >Receipt</SelectItem
                                        >
                                        <SelectItem value="purchase_order"
                                            >Purchase Order</SelectItem
                                        >
                                        <SelectItem value="other"
                                            >Other</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                                <p
                                    v-if="form.errors.type"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.type }}
                                </p>
                            </div>

                            <!-- Description -->
                            <div class="space-y-2">
                                <Label for="description">Description</Label>
                                <Textarea
                                    id="description"
                                    v-model="form.description"
                                    placeholder="Add a description for this document..."
                                    rows="3"
                                    :class="{
                                        'border-destructive':
                                            form.errors.description,
                                    }"
                                />
                                <p
                                    v-if="form.errors.description"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.description }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- File Information Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>File Information</CardTitle>
                            <CardDescription>
                                Information about the uploaded file. File cannot
                                be changed in edit mode.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div
                                class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2"
                            >
                                <div>
                                    <p
                                        class="font-medium text-muted-foreground"
                                    >
                                        File Name
                                    </p>
                                    <p class="truncate">
                                        {{ document.file_name }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="font-medium text-muted-foreground"
                                    >
                                        File Size
                                    </p>
                                    <p>
                                        {{ formatFileSize(document.file_size) }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="font-medium text-muted-foreground"
                                    >
                                        File Type
                                    </p>
                                    <p>{{ document.file_mime_type }}</p>
                                </div>
                                <div>
                                    <p
                                        class="font-medium text-muted-foreground"
                                    >
                                        Created
                                    </p>
                                    <p>{{ formatDate(document.created_at) }}</p>
                                </div>
                                <div>
                                    <p
                                        class="font-medium text-muted-foreground"
                                    >
                                        Last Updated
                                    </p>
                                    <p>{{ formatDate(document.updated_at) }}</p>
                                </div>
                            </div>

                            <div class="mt-4 border-t pt-4">
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    :href="
                                        route('documents.download', document.id)
                                    "
                                    target="_blank"
                                >
                                    <FileText class="mr-2 h-4 w-4" />
                                    Download File
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Actions -->
                    <div class="flex items-center justify-between">
                        <div class="flex space-x-4">
                            <Link :href="route('documents.show', document.id)">
                                <Button type="button" variant="outline">
                                    Cancel
                                </Button>
                            </Link>
                        </div>

                        <div class="flex space-x-4">
                            <Button
                                type="button"
                                variant="destructive"
                                @click="deleteDocument"
                                :disabled="form.processing"
                            >
                                <Trash2 class="mr-2 h-4 w-4" />
                                Delete
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                <Save class="mr-2 h-4 w-4" />
                                {{
                                    form.processing
                                        ? 'Saving...'
                                        : 'Save Changes'
                                }}
                            </Button>
                        </div>
                    </div>
                </form>

                <!-- Global Error Alert -->
                <Alert
                    v-if="form.hasErrors && Object.keys(form.errors).length > 0"
                    variant="destructive"
                    class="mt-6"
                >
                    <AlertDescription>
                        There were errors with your submission. Please check the
                        form fields and try again.
                    </AlertDescription>
                </Alert>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
