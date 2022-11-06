<?php
namespace Modules\Media\Http\Controllers\Admin;

use ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Media\Http\Requests\Admin\ListRequest;
use Modules\Media\Http\Requests\Admin\UploadRequest;
use Modules\Media\MediaCollections\MediaRepository;
use Modules\Media\Entities\UploadSession;
use Modules\Media\Interfaces\HasMedia;

class MediaController extends Controller
{
    protected $request;
    protected $mediaRepository;

    public function __construct(Request $request, MediaRepository $mediaRepository)
    {
        $this->request         = $request;
        $this->mediaRepository = $mediaRepository;
    }

    protected function getModel(): HasMedia
    {
        $model_class = $this->request->model;
        $user        = $this->request->user();
        $model       = UploadSession::firstOrCreate([
            'model_type'=> $model_class,
            'user_id'   => $user->id,
        ]);

        return $model;
    }

    public function list(ListRequest $request)
    {
        $model = $this->getModel();
        $media = $model
        ->getMedia(
            $request->get('collection', 'default'), 
            function($item) use ($request) {
                if($request->filled('search')) {
                    return strpos($item->file_name, $request->search) !== false;
                }

                return true;
            }
        )
        ->sortByDesc('id')
        ->values();

        return ApiResponse::data($media);
    }

    public function upload(UploadRequest $request)
    {
        $model = $this->getModel();
        $media = $model->addMediaFromRequest('file')
        ->withCustomProperties([
            'model' => $request->model
        ])
        ->toMediaCollection($request->get('collection', 'default'));

        return ApiResponse::data($media);
    }
}