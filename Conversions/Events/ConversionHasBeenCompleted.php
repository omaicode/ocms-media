<?php

namespace Modules\Media\Conversions\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Media\Conversions\Conversion;
use Modules\Media\MediaCollections\Models\Media;

class ConversionHasBeenCompleted
{
    use SerializesModels;

    public Media $media;

    public Conversion $conversion;

    public function __construct(Media $media, Conversion $conversion)
    {
        $this->media = $media;

        $this->conversion = $conversion;
    }
}
