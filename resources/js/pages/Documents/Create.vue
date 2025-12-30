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
import AuthenticatedLayout from '@/layouts/auth/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { AlertCircle, ArrowLeft, FileText, Upload } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    maxFileSize?: number;
    allowedMimeTypes?: string[];
}>();

const maxFileSize = props.maxFileSize ?? 10240; // 10MB in KB
const allowedMimeTypes = props.allowedMimeTypes ?? [
    'application/pdf',
    'image/jpeg',
    'image/jpg',
    'image/png',
    'image/tiff',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
];

const fileInput = ref<HTMLInputElement>();
const selectedFile = ref<File | null>(null);
const filePreview = ref<string>('');
const dragOver = ref(false);

const form = useForm({
    title: '',
    type: '',
    file: null as File | null,
});

const handleFileSelect = (file: File) => {
    if (!allowedMimeTypes.includes(file.type)) {
        form.setError(
            'file',
            'Invalid file type. Please upload a PDF, image, or Office document.',
        );
        return;
    }

    if (file.size > maxFileSize * 1024) {
        form.setError(
            'file',
            `File size must be less than ${maxFileSize / 1024}MB.`,
        );
        return;
    }

    selectedFile.value = file;
    form.file = file;

    // Generate preview for images
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
            filePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    } else {
        filePreview.value = '';
    }

    // Auto-fill title if not already set
    if (!form.title) {
        form.title = file.name.replace(/\.[^/.]+$/, '');
    }

    form.clearErrors('file');
};

const onFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files?.[0]) {
        handleFileSelect(target.files[0]);
    }
};

const onDrop = (event: DragEvent) => {
    event.preventDefault();
    dragOver.value = false;

    if (event.dataTransfer?.files?.[0]) {
        handleFileSelect(event.dataTransfer.files[0]);
    }
};

const onDragOver = (event: DragEvent) => {
    event.preventDefault();
    dragOver.value = true;
};

const onDragLeave = () => {
    dragOver.value = false;
};

const removeFile = () => {
    selectedFile.value = null;
    form.file = null;
    filePreview.value = '';
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const submit = () => {
    form.post(route('documents.store'), {
        onSuccess: () => {
            // Reset form after successful submission
            form.reset();
            removeFile();
        },
    });
};

const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>

<template>
    <Head title="Create Document" />

    <AuthenticatedLayout>
        <div class="container mx-auto px-4 py-8">
            <div class="mx-auto max-w-2xl">
                <!-- Header -->
                <div class="mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold tracking-tight">
                                Create Document
                            </h1>
                            <p class="text-muted-foreground">
                                Upload and categorise your document
                            </p>
                        </div>
                        <Link :href="route('documents.index')">
                            <Button variant="outline" size="sm">
                                <ArrowLeft class="mr-2 h-4 w-4" />
                                Back to Documents
                            </Button>
                        </Link>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- File Upload Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center">
                                <Upload class="mr-2 h-5 w-5" />
                                Upload File
                            </CardTitle>
                            <CardDescription>
                                Select a file to upload. Supported formats: PDF,
                                images, and Office documents.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div
                                class="rounded-lg border-2 border-dashed p-6 text-center transition-colors"
                                :class="[
                                    dragOver
                                        ? 'border-primary bg-primary/5'
                                        : 'border-muted-foreground/25',
                                    form.hasErrors && form.errors.file
                                        ? 'border-destructive'
                                        : '',
                                ]"
                                @drop="onDrop"
                                @dragover="onDragOver"
                                @dragleave="onDragLeave"
                            >
                                <input
                                    ref="fileInput"
                                    type="file"
                                    class="hidden"
                                    @change="onFileChange"
                                    :accept="allowedMimeTypes.join(',')"
                                />

                                <div v-if="!selectedFile" class="space-y-4">
                                    <Upload
                                        class="mx-auto h-12 w-12 text-muted-foreground"
                                    />
                                    <div>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            @click="fileInput?.click()"
                                        >
                                            Choose File
                                        </Button>
                                        <p
                                            class="mt-2 text-sm text-muted-foreground"
                                        >
                                            or drag and drop file here
                                        </p>
                                    </div>
                                    <p class="text-xs text-muted-foreground">
                                        Maximum file size:
                                        {{ formatFileSize(maxFileSize * 1024) }}
                                    </p>
                                </div>

                                <div v-else class="space-y-4">
                                    <!-- File Preview -->
                                    <div v-if="filePreview" class="mx-auto">
                                        <img
                                            :src="filePreview"
                                            :alt="selectedFile.name"
                                            class="max-h-32 rounded border"
                                        />
                                    </div>
                                    <div v-else class="flex justify-center">
                                        <FileText
                                            class="h-12 w-12 text-muted-foreground"
                                        />
                                    </div>

                                    <!-- File Info -->
                                    <div>
                                        <p class="truncate font-medium">
                                            {{ selectedFile.name }}
                                        </p>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{
                                                formatFileSize(
                                                    selectedFile.size,
                                                )
                                            }}
                                            • {{ selectedFile.type }}
                                        </p>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex justify-center space-x-2">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            @click="fileInput?.click()"
                                        >
                                            Change File
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="destructive"
                                            @click="removeFile"
                                        >
                                            Remove
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            <Alert
                                v-if="form.errors.file"
                                variant="destructive"
                                class="mt-4"
                            >
                                <AlertCircle class="h-4 w-4" />
                                <AlertDescription>{{
                                    form.errors.file
                                }}</AlertDescription>
                            </Alert>
                        </CardContent>
                    </Card>

                    <!-- Document Information Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Document Information</CardTitle>
                            <CardDescription>
                                Add details to help organise and find your
                                document later.
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
                        </CardContent>
                    </Card>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-4">
                        <Link :href="route('documents.index')">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                        <Button
                            type="submit"
                            :disabled="form.processing || !form.file"
                        >
                            {{
                                form.processing
                                    ? 'Creating...'
                                    : 'Create Document'
                            }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
