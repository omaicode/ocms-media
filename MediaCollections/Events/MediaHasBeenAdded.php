<?php

namespace Modules\Media\MediaCollections\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Media\MediaCollections\Models\Media;

class MediaHasBeenAdded
{
    use SerializesModels;

    public Media $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }
}
