<?php

declare(strict_types=1);

if (!function_exists('uploaded_file')) {
    /**
     * Create a fake uploaded file for testing.
     *
     * @param string $content The file content
     * @param string $name The file name
     * @param string $mimeType The MIME type
     * @param int|null $size The file size
     * @return \Illuminate\Http\UploadedFile
     */
    function uploaded_file(
        string $content,
        string $name = 'test.txt',
        string $mimeType = 'text/plain',
        ?int $size = null
    ): \Illuminate\Http\UploadedFile {
        $size = $size ?? strlen($content);
        
        return \Illuminate\Http\UploadedFile::fake()->createWithContent(
            $name,
            $content
        );
    }
}