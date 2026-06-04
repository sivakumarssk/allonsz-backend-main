<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogIncomingFiles
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('post') || $request->isMethod('put') || $request->isMethod('patch')) {
            try {
                $rawFiles = [];
                foreach ($_FILES as $field => $info) {
                    if (is_array($info['name'] ?? null)) {
                        foreach ($info['name'] as $idx => $name) {
                            $rawFiles[] = [
                                'field' => $field.'['.$idx.']',
                                'name' => $name,
                                'type' => $info['type'][$idx] ?? null,
                                'tmp_name' => $info['tmp_name'][$idx] ?? null,
                                'error' => $info['error'][$idx] ?? null,
                                'size' => $info['size'][$idx] ?? null,
                            ];
                        }
                    } else {
                        $rawFiles[] = [
                            'field' => $field,
                            'name' => $info['name'] ?? null,
                            'type' => $info['type'] ?? null,
                            'tmp_name' => $info['tmp_name'] ?? null,
                            'error' => $info['error'] ?? null,
                            'size' => $info['size'] ?? null,
                        ];
                    }
                }

                $contentLength = $request->server('CONTENT_LENGTH');
                $contentType = $request->server('CONTENT_TYPE');

                Log::info('LogIncomingFiles: request multipart snapshot', [
                    'method' => $request->method(),
                    'path' => $request->path(),
                    'content_length' => $contentLength,
                    'content_type' => $contentType,
                    'post_max_size' => ini_get('post_max_size'),
                    'upload_max_filesize' => ini_get('upload_max_filesize'),
                    'max_file_uploads' => ini_get('max_file_uploads'),
                    'memory_limit' => ini_get('memory_limit'),
                    'php_ini_loaded' => php_ini_loaded_file(),
                    'raw_files' => $rawFiles,
                ]);

                if (!empty($rawFiles)) {
                    foreach ($rawFiles as $file) {
                        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                            Log::warning('LogIncomingFiles: file with upload error', [
                                'field' => $file['field'],
                                'error_code' => $file['error'],
                                'error_label' => $this->errorLabel($file['error']),
                                'name' => $file['name'],
                                'size' => $file['size'],
                            ]);
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('LogIncomingFiles: snapshot exception', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $next($request);
    }

    private function errorLabel($code): string
    {
        switch ((int) $code) {
            case UPLOAD_ERR_OK: return 'OK';
            case UPLOAD_ERR_INI_SIZE: return 'UPLOAD_ERR_INI_SIZE (file > upload_max_filesize)';
            case UPLOAD_ERR_FORM_SIZE: return 'UPLOAD_ERR_FORM_SIZE (file > MAX_FILE_SIZE in form)';
            case UPLOAD_ERR_PARTIAL: return 'UPLOAD_ERR_PARTIAL (only partial file received)';
            case UPLOAD_ERR_NO_FILE: return 'UPLOAD_ERR_NO_FILE (no file sent)';
            case UPLOAD_ERR_NO_TMP_DIR: return 'UPLOAD_ERR_NO_TMP_DIR (missing tmp dir)';
            case UPLOAD_ERR_CANT_WRITE: return 'UPLOAD_ERR_CANT_WRITE (cannot write to disk)';
            case UPLOAD_ERR_EXTENSION: return 'UPLOAD_ERR_EXTENSION (php extension stopped upload)';
            default: return 'UNKNOWN';
        }
    }
}
