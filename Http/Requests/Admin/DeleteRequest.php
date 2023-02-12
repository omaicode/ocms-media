<?php

namespace Modules\Media\Http\Requests\Admin;

use Modules\Core\Http\Requests\BaseApiRequest;

class DeleteRequest extends BaseApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'items'   => ['required', 'array'],
            'items.*' => ['required', 'string', 'max:255']
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
