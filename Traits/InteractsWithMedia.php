<?php
namespace Modules\Media\Traits;

use Illuminate\Support\Str;
use Modules\Media\Support\Retriever;

trait InteractsWithMedia
{
    public string $media_save_path     = 'default';
    public ?string $fall_back_media_url = null;

    protected function mediaRetriever()
    {
        return app(Retriever::class);
    }

    public function getMedia(string $column)
    {
        $this->registerMediaSavePath();
        return $this->mediaRetriever()->findById($this->{$column});
    }

    public function getMediaUrl(string $column)
    {
        $this->registerMediaSavePath();
        $retriever = $this->mediaRetriever();

        if(isset($this->{$column})) {
            if(!Str::isUuid($this->{$column}) && strlen($this->{$column}) > 0) {
                return $this->mediaRetriever()->fileUrl($this->{$column});
            }

            $media = $this->getMedia($column);
            if($media && $retriever->fileExists($media->file_name)) {
                return $retriever->fileUrl($media->file_name);
            }
        }

        return $this->fall_back_media_url;
    }

    public function registerMediaSavePath()
    {
        
    }

    public function useFallbackUrl(string $url): self
    {
        $this->fall_back_media_url = $url;
        return $this;
    }

    public function setMediaSavePath(string $name): self
    {
        $this->media_save_path = $name;
        return $this;
    }
}