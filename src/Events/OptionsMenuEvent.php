<?php

namespace kirillbdev\MediaManager\Events;

use IdeaCms\Core\Helpers\Backend;

class OptionsMenuEvent
{
    public function handle(&$children)
    {
        $children[] = [
            'name' => 'Менеджер изображений',
            'url' => Backend::url('media-manager/settings')
        ];
    }
}