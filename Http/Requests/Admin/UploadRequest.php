<?php

namespace Modules\Media\Http\Requests\Admin;

use Modules\Core\Http\Requests\BaseApiRequest;

class UploadRequest extends BaseApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'model' => 'required|string|max:50',
            'file'  => 'required|file|mimes:jpg,jpeg,png,gif,bmp,mp4,mov,avi|max:5120'
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
