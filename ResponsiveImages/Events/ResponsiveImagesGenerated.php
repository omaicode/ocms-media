<?php

namespace Modules\Media\ResponsiveImages\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Media\MediaCollections\Models\Media;

class ResponsiveImagesGenerated
{
    use SerializesModels;

    public Media $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }
}
