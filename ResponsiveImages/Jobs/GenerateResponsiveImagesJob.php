<?php

namespace Modules\Media\ResponsiveImages\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Media\MediaCollections\Models\Media;
use Modules\Media\ResponsiveImages\ResponsiveImageGenerator;

class GenerateResponsiveImagesJob implements ShouldQueue
{
    use InteractsWithQueue;
    use SerializesModels;
    use Queueable;

    protected Media $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    public function handle(): bool
    {
        /** @var \Modules\Media\ResponsiveImages\ResponsiveImageGenerator $responsiveImageGenerator */
        $responsiveImageGenerator = app(ResponsiveImageGenerator::class);

        $responsiveImageGenerator->generateResponsiveImages($this->media);

        return true;
    }
}
