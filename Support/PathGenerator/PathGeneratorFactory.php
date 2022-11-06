<?php

namespace Modules\Media\Support\PathGenerator;

use Modules\Media\MediaCollections\Exceptions\InvalidPathGenerator;
use Modules\Media\MediaCollections\Models\Media;

class PathGeneratorFactory
{
    public static function create(Media $media): PathGenerator
    {
        $pathGeneratorClass = self::getPathGeneratorClass($media);

        static::guardAgainstInvalidPathGenerator($pathGeneratorClass);

        return app($pathGeneratorClass);
    }

    protected static function guardAgainstInvalidPathGenerator(string $pathGeneratorClass): void
    {
        if (! class_exists($pathGeneratorClass)) {
            throw InvalidPathGenerator::doesntExist($pathGeneratorClass);
        }

        if (! is_subclass_of($pathGeneratorClass, PathGenerator::class)) {
            throw InvalidPathGenerator::doesNotImplementPathGenerator($pathGeneratorClass);
        }
    }

    protected static function getPathGeneratorClass(Media $media)
    {
        $defaultPathGeneratorClass = config('media.path_generator');

        foreach (config('media.custom_path_generators', []) as $modelClass => $customPathGeneratorClass) {
            if (is_a($media->model_type, $modelClass, true)) {
                return $customPathGeneratorClass;
            }
        }

        return $defaultPathGeneratorClass;
    }
}
