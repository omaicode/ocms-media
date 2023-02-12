<?php

namespace Modules\Media\Entities;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table    = 'media';
    protected $fillable = [
        'uuid',
        'name',
        'file_name',
        'mime_type',
        'disk',
        'size',
        'order_column'
    ];
}
