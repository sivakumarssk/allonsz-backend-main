<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware to sanitize file uploads and remove malformed files
 * that can cause issues with Laravel Passport's PSR-7 conversion.
 *
 * This fixes the "The file cannot be opened" error that occurs when
 * camera uploads send files with empty or invalid paths.
 */
class SanitizeFileUploads
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Get all uploaded files
        $files = $request->allFiles();

        if (!empty($files)) {
            $this->sanitizeFiles($request, $files);
        }

        return $next($request);
    }

    /**
     * Recursively sanitize uploaded files
     *
     * @param Request $request
     * @param array $files
     * @param string $prefix
     */
    protected function sanitizeFiles(Request $request, array $files, string $prefix = '')
    {
        foreach ($files as $key => $file) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($file)) {
                // Handle nested file arrays
                $this->sanitizeFiles($request, $file, $fullKey);
            } else {
                // Check if the file is valid
                if ($file instanceof \Illuminate\Http\UploadedFile) {
                    $path = $file->getPathname();

                    // Remove file if path is empty, is a directory, or file doesn't exist
                    if (empty($path) ||
                        is_dir($path) ||
                        !file_exists($path) ||
                        !is_file($path) ||
                        $file->getSize() === 0) {

                        // Log the issue for debugging
                        \Log::warning("SanitizeFileUploads: Removed invalid file upload", [
                            'key' => $fullKey,
                            'path' => $path,
                            'is_dir' => is_dir($path),
                            'exists' => file_exists($path),
                            'original_name' => $file->getClientOriginalName(),
                        ]);

                        // Remove the invalid file from the request
                        $this->removeFileFromRequest($request, $fullKey);
                    }
                }
            }
        }
    }

    /**
     * Remove a file from the request's files array
     *
     * @param Request $request
     * @param string $key
     */
    protected function removeFileFromRequest(Request $request, string $key)
    {
        $files = $request->files->all();

        // Handle nested keys
        $keys = explode('.', $key);
        $this->removeNestedKey($files, $keys);

        $request->files->replace($files);
    }

    /**
     * Remove a nested key from an array
     *
     * @param array $array
     * @param array $keys
     */
    protected function removeNestedKey(array &$array, array $keys)
    {
        $key = array_shift($keys);

        if (empty($keys)) {
            unset($array[$key]);
        } elseif (isset($array[$key]) && is_array($array[$key])) {
            $this->removeNestedKey($array[$key], $keys);
        }
    }
}
