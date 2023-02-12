<?php
namespace Modules\Media\Http\Controllers\Admin;

use ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Contracts\AdminPage;
use Modules\Core\Facades\AdminAsset;
use Modules\Media\Http\Requests\Admin\CreateFolderRequest;
use Modules\Media\Http\Requests\Admin\DeleteRequest;
use Modules\Media\Http\Requests\Admin\ListRequest;
use Modules\Media\Http\Requests\Admin\MoveRequest;
use Modules\Media\Http\Requests\Admin\UploadRequest;
use Modules\Media\Support\Retriever;
use Modules\Media\Support\Uploader;

class MediaController extends Controller
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request         = $request;
    }

    public function index(AdminPage $page)
    {
        AdminAsset::addScript('media', asset('modules/media/js/media.js'));
        AdminAsset::addStyle('media', asset('modules/media/css/media.css'));

        $breadcrumb = [
            [
                'title' => __('media::messages.media'),
                'url'   => '#'
            ]
        ];

        $url = url('/'.config('core.admin_prefix', 'admin').'/media/api');

        return $page->title(__('media::messages.media'))
        ->breadcrumb($breadcrumb)
        ->body('media::index', compact('url'));
    }

    public function list(ListRequest $request)
    {
        $media = app(Retriever::class)
        ->all($request->get('path', '/'))
        ->filter(function($item) use ($request) {
            $display = $request->get('display', 'all');
            if($display != 'all' && !$item['is_dir']) {
                switch($display) {
                    case 'images':
                        return strpos($item['mime_type'], 'image/') !== false;
                    break;
                    case 'video':
                        return strpos($item['mime_type'], 'video/') !== false;
                    break;
                    case 'audio':
                        return strpos($item['mime_type'], 'audio/') !== false;
                    break;
                    case 'documents':
                        return (strpos($item['mime_type'], 'application/') !== false ?: strpos($item['mime_type'], 'text/') !== false);
                    break;
                }
            }

            return true;
        })
        ->when($request->filled('search'), function($items) use ($request) {
            return $items->filter(fn($item) => strpos($item['name'], $request->search) !== false);
        })
        ->sortBy(function($item) use ($request) {
            $order_by = $request->get('orderBy', 'title');

            if($order_by == 'size') {
                return isset($item['size']) ? $item['size'] : 0;
            }

            if($order_by == 'last_modified') {
                return $item['last_modified_at'];
            }

            return $item['name'];
        }, SORT_REGULAR, $request->get('sortBy', 'desc') == 'desc' ? true : false)
        ->values();

        return ApiResponse::data($media);
    }

    public function upload(UploadRequest $request)
    {
        $media = app(Uploader::class)
        ->setSavePath($request->get('path', '/'))
        ->uploadMultiple($request->file('files'));

        return ApiResponse::data($media);
    }

    public function createFolder(CreateFolderRequest $request)
    {
        $path   = $request->get('path', '/');
        $folder = $request->get('folder_name');
        $full_path = rtrim($path, '/').'/'.ltrim($folder, '/');

        app(Uploader::class)->createFolder($full_path);
        
        return ApiResponse::data();
    }

    public function move(MoveRequest $request) 
    {
        $from = $request->get('from', []);
        $to   = $request->get('to', '/');

        foreach($from as $path) {
            app(Uploader::class)->move($path, $to);
        }

        return ApiResponse::data();
    }

    public function destroy(DeleteRequest $request)
    {
        foreach($request->items as $item) {
            app(Uploader::class)->remove($item);
        }

        return ApiResponse::data();
    }
}