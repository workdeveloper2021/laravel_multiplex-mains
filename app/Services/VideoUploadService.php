<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use CURLFile;

class VideoUploadService
{
    public function uploadToNodeAPI(UploadedFile $file): ?string
    {
        $curlFile = new CURLFile(
            $file->getRealPath(),
            $file->getMimeType(),
            $file->getClientOriginalName()
        );

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => 'http://localhost:3000/nodeapi/rest-api/v130/movies/upload',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => ['video' => $curlFile],
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            \Log::error("Upload failed: " . $error);
            return null;
        }

        $decoded = json_decode($response, true);
        return $decoded['url'] ?? null;
    }
}
