<?php

namespace Modules\Media\Support\UrlGenerator;

use DateTimeInterface;
use Modules\Media\Conversions\Conversion;
use Modules\Media\MediaCollections\Models\Media;
use Modules\Media\Support\PathGenerator\PathGenerator;

interface UrlGenerator
{
    public function getUrl(): string;

    public function getPath(): string;

    public function setMedia(Media $media): self;

    public function setConversion(Conversion $conversion): self;

    public function setPathGenerator(PathGenerator $pathGenerator): self;

    public function getTemporaryUrl(DateTimeInterface $expiration, array $options = []): string;

    public function getResponsiveImagesDirectoryUrl(): string;
}
