<?php

return [
    [
        'id' => 'ocms-menu-media',
        'priority' => 94,
        'parent_id' => null,
        'name' => 'media::messages.media',
        'icon' => 'fas fa-image',
        'url' => route('admin.media.index'),
        'permissions' => ['media.view']
    ]
];
