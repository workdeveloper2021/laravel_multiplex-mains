<?php

namespace App\Jobs;

use App\Models\Movie;
use App\Services\VideoUploadService;
use Illuminate\Bus\Queueable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use MongoDB\Laravel\Eloquent\Model as Eloquent;

class UploadMongoVideoToNodeApi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public UploadedFile $video;
    public string $movieId;

    public function __construct(UploadedFile $video, string $movieId)
    {
        $this->video = $video;
        $this->movieId = $movieId;
    }

/*******  1e8db6fe-74e6-4794-85fb-c84ce8252369  *******//*************  ✨ Windsurf Command ⭐  *************/
    public function handle(VideoUploadService $uploader)
    {
        $url = $uploader->uploadToNodeAPI($this->video);

        if ($url) {
            Movie::where('_id', $this->movieId)->update([
                'video_url' => $url,
                'video_status' => 'uploaded',
            ]);
        } else {
            Movie::where('_id', $this->movieId)->update([
                'video_status' => 'failed',
            ]);
        }
    }
}
