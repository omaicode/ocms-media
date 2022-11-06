<?php

namespace Modules\Media\MediaCollections\Exceptions;

use Modules\Media\Interfaces\HasMedia;
use Modules\Media\MediaCollections\File;
use Modules\Media\MediaCollections\MediaCollection;

class FileUnacceptableForCollection extends FileCannotBeAdded
{
    public static function create(File $file, MediaCollection $mediaCollection, HasMedia $hasMedia): self
    {
        $modelType = get_class($hasMedia);

        return new static("The file with properties `{$file}` was not accepted into the collection named `{$mediaCollection->name}` of model `{$modelType}` with id `{$hasMedia->getKey()}`");
    }
}
