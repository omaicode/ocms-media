<?php

namespace Modules\Media\Support;

use Modules\Media\MediaCollections\Exceptions\FunctionalityNotAvailable;
use Modules\MediaPro\Models\TemporaryUpload;

class MediaLibraryPro
{
    public static function ensureInstalled()
    {
        if (! self::isInstalled()) {
            throw FunctionalityNotAvailable::mediaLibraryProRequired();
        }
    }

    public static function isInstalled(): bool
    {
        return class_exists(TemporaryUpload::class);
    }
}
