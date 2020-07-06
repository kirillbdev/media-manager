<?php

namespace kirillbdev\MediaManager\Services;

use IdeaCms\Core\Helpers\OptionStorage;

class OptionsService
{
    public function saveOptions($data)
    {
        OptionStorage::saveOption('media_manager_allow_svg', $data['allow_svg'] ?? 0);
    }
}