<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\UploadedFile;

final class UploadMovieFile implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected array $data,
        protected array $files = []
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $isPaid = $this->data['is_paid'] ?? false;
        $price = $isPaid ? ($this->data['price'] ?? 0) : 0;

        $multipart = [
            ['name' => 'title', 'contents' => $this->data['title']],
            ['name' => 'description', 'contents' => $this->data['description'] ?? ''],
            ['name' => 'genre', 'contents' => $this->data['genre']],
            ['name' => 'channel_id', 'contents' => $this->data['channel_id']],
            ['name' => 'release', 'contents' => $this->data['release']],
            ['name' => 'price', 'contents' => $price],
            ['name' => 'is_paid', 'contents' => $isPaid ? '1' : '0'],
            ['name' => 'publication', 'contents' => ($this->data['publication'] ?? false) ? '1' : '0'],
            ['name' => 'enable_download', 'contents' => ($this->data['enable_download'] ?? false) ? '1' : '0'],
        ];

        // language[]
        if (isset($this->data['language'])) {
            foreach ($this->data['language'] as $lang) {
                $multipart[] = [
                    'name' => 'language[]',
                    'contents' => $lang,
                ];
            }
        }

        // country[]
        if (isset($this->data['country'])) {
            foreach ($this->data['country'] as $country) {
                $multipart[] = [
                    'name' => 'country[]',
                    'contents' => $country,
                ];
            }
        }

        // File fields that generate URL
        $urlFiles = ['trailer', 'thumbnail', 'poster'];
        foreach ($urlFiles as $fileField) {
            if (isset($this->files[$fileField])) {
                $filePath = $this->files[$fileField];
                if (file_exists($filePath)) {
                    // Store the file and get URL
                    $storagePath = 'uploads/' . $fileField . '/' . basename($filePath);
                    \Storage::disk('public')->put($storagePath, file_get_contents($filePath));
                    $url = asset('storage/' . $storagePath);

                    $multipart[] = [
                        'name' => $fileField,
                        'contents' => $url,
                    ];
                }
            }
        }

        // main video file
        if (isset($this->files['file'])) {
            $filePath = $this->files['file']['path'];
            $filename = $this->files['file']['name'];

            if (file_exists($filePath)) {
                $multipart[] = [
                    'name' => 'file',
                    'contents' => fopen($filePath, 'r'),
                    'filename' => $filename,
                ];
            }
        }
//        dd($multipart);
        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->post('http://multiplexplay.com/nodeapi/rest-api/v130/movies', [
                'headers' => [
                    'api-key' => 'ec8590cb04e0e37c6706ab6c',
                ],
                'multipart' => $multipart,
            ]);

            // Handle response
            if ($response->getStatusCode() == 200) {
                \Log::info('Movie created successfully via API');
            } else {
                \Log::error('API Error: ' . $response->getBody());
            }
        } catch (\Exception $e) {
            \Log::error('Exception: ' . $e->getMessage());
        }
    }
}
