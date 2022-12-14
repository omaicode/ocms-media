<?php
namespace Modules\Media\Entities;

use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Spatie\Image\Manipulations;
use Modules\Media\Conversions\Conversion;
use Modules\Media\Interfaces\HasMedia;
use Modules\Media\Traits\InteractsWithMedia;
use Modules\Media\MediaCollections\Models\Media;
use Modules\Media\Exceptions\CouldNotAddUpload;
use Modules\Media\Exceptions\TemporaryUploadDoesNotBelongToCurrentSession;

class UploadSession extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = [];

    public static ?Closure $manipulatePreview = null;

    public static ?string $disk = null;

    public function scopeOld(Builder $builder): void
    {
        $builder->where('created_at', '<=', Carbon::now()->subDay()->toDateTimeString());
    }

    public function registerMediaConversions(Media $media = null): void
    {
        if (! config('media.generate_thumbnails_for_temporary_uploads')) {
            return;
        }

        $conversion = $this
            ->addMediaConversion('preview')
            ->nonQueued();

        $previewManipulation = $this->getPreviewManipulation();

        $previewManipulation($conversion);
    }

    public static function previewManipulation(Closure $closure): void
    {
        static::$manipulatePreview = $closure;
    }

    protected function getPreviewManipulation(): Closure
    {
        return static::$manipulatePreview ?? function (Conversion $conversion) {
            $conversion->fit(Manipulations::FIT_CROP, 300, 300);
        };
    }

    protected static function getDiskName(): string
    {
        return static::$disk ?? config('media.disk_name');
    }

    public static function findByMediaUuid(?string $mediaUuid): ?UploadSession
    {
        $mediaModelClass = config('media.media_model');

        /** @var Media $media */
        $media = $mediaModelClass::query()
            ->where('uuid', $mediaUuid)
            ->first();

        if (! $media) {
            return null;
        }

        $temporaryUpload = $media->model;

        if (! $temporaryUpload instanceof UploadSession) {
            return null;
        }

        return $temporaryUpload;
    }

    public static function findByMediaUuidInCurrentSession(?string $mediaUuid): ?UploadSession
    {
        if (! $temporaryUpload = static::findByMediaUuid($mediaUuid)) {
            return null;
        }

        if (config('media.enable_temporary_uploads_session_affinity', true)) {
            if ($temporaryUpload->session_id !== session()->getId()) {
                return null;
            }
        }

        return $temporaryUpload;
    }

    public static function createForFile(
        UploadedFile $file,
        string $sessionId,
        string $uuid,
        string $name
    ): self {
        /** @var \Spatie\MediaLibraryPro\Models\TemporaryUpload $temporaryUpload */
        $temporaryUpload = static::create([
            'session_id' => $sessionId,
        ]);

        if (static::findByMediaUuid($uuid)) {
            throw CouldNotAddUpload::uuidAlreadyExists();
        }

        $temporaryUpload
            ->addMedia($file)
            ->setName($name)
            ->withProperties(['uuid' => $uuid])
            ->toMediaCollection('default', static::getDiskName());

        return $temporaryUpload->fresh();
    }

    public function moveMedia(HasMedia $toModel, string $collectionName, string $diskName, string $fileName): Media
    {
        if (config('media.enable_temporary_uploads_session_affinity', true)) {
            if ($this->session_id !== session()->getId()) {
                throw TemporaryUploadDoesNotBelongToCurrentSession::create();
            }
        }

        $media = $this->getFirstMedia();

        $temporaryUploadModel = $media->model;
        $uuid = $media->uuid;

        $newMedia = $media->move($toModel, $collectionName, $diskName, $fileName);
        $newMedia->update(compact('uuid'));

        $temporaryUploadModel->delete();

        return $newMedia;
    }
}
