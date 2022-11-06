<?php

namespace Modules\Media\MediaCollections;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Modules\Media\MediaCollections\Exceptions\RequestDoesNotHaveFile;
use Modules\Media\Support\RemoteFile;
use Modules\MediaPro\Dto\PendingMediaItem;

class FileAdderFactory
{
    /**
     * @param \Illuminate\Database\Eloquent\Model $subject
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return \Modules\Media\MediaCollections\FileAdder
     */
    public static function create(Model $subject, $file): FileAdder
    {
        /** @var \Modules\Media\MediaCollections\FileAdder $fileAdder */
        $fileAdder = app(FileAdder::class);

        return $fileAdder
            ->setSubject($subject)
            ->setFile($file);
    }

    public static function createFromDisk(Model $subject, string $key, string $disk): FileAdder
    {
        /** @var \Modules\Media\MediaCollections\FileAdder $fileAdder */
        $fileAdder = app(FileAdder::class);

        return $fileAdder
            ->setSubject($subject)
            ->setFile(new RemoteFile($key, $disk));
    }

    public static function createFromRequest(Model $subject, string $key): FileAdder
    {
        return static::createMultipleFromRequest($subject, [$key])->first();
    }

    public static function createMultipleFromRequest(Model $subject, array $keys = []): Collection
    {
        return collect($keys)
            ->map(function (string $key) use ($subject) {
                $search = ['[', ']', '"', "'"];
                $replace = ['.', '', '', ''];

                $key = str_replace($search, $replace, $key);

                if (! request()->hasFile($key)) {
                    throw RequestDoesNotHaveFile::create($key);
                }

                $files = request()->file($key);

                if (! is_array($files)) {
                    return static::create($subject, $files);
                }

                return array_map(fn ($file) => static::create($subject, $file), $files);
            })->flatten();
    }

    public static function createAllFromRequest(Model $subject): Collection
    {
        $fileKeys = array_keys(request()->allFiles());

        return static::createMultipleFromRequest($subject, $fileKeys);
    }

    public static function createForPendingMedia(Model $subject, PendingMediaItem $pendingMedia): FileAdder
    {
        /** @var \Modules\Media\MediaCollections\FileAdder $fileAdder */
        $fileAdder = app(FileAdder::class);

        return $fileAdder
            ->setSubject($subject)
            ->setFile($pendingMedia->temporaryUpload)
            ->setName($pendingMedia->name)
            ->setOrder($pendingMedia->order);
    }
}
