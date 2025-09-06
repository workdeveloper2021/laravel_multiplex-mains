<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\VideoUploadService;

class UploadVideoAndAttachJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public UploadedFile $video;
    public string $modelClass;
    public string $modelId;
    public string $field;

    /**
     * @param UploadedFile $video        // The video file
     * @param string $modelClass         // Fully-qualified class name of the model (e.g., App\Models\Movie)
     * @param string $modelId            //U MongoDB _id as string
     * @param string $field              // Field name to store video RL (e.g., 'video_url')
     */
    public function __construct(UploadedFile $video, string $modelClass, string $modelId, string $field = 'video_url')
    {
        $this->video = $video;
        $this->modelClass = $modelClass;
        $this->modelId = $modelId;
        $this->field = $field;
    }

    public function handle(VideoUploadService $uploader)
    {
        $url = $uploader->uploadToNodeAPI($this->video);

        if (!$url) {
            return; // Optionally handle failure (logging, retries, etc.)
        }

        $model = ($this->modelClass)::where('_id', $this->modelId)->first();

        if ($model) {
            $model->{$this->field} = $url;
            $model->save();
        }
    }
}
