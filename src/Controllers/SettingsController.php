<?php

namespace kirillbdev\MediaManager\Controllers;

use IdeaCms\Core\Helpers\OptionStorage;
use Illuminate\Routing\Controller;
use kirillbdev\MediaManager\Services\OptionsService;

class SettingsController extends Controller
{
    public function settings()
    {
        $data['checked'] = (int)OptionStorage::getOption('media_manager_allow_svg', 0);

        document()->setTitle('Настройки | Менеджер изображений');
        document()->setHeading('Настройки');

        return view('kirillbdev/media-manager::settings', $data);
    }

    public function saveSettings(OptionsService $optionsService)
    {
        $optionsService->saveOptions(request()->all());

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'Настройки успешно сохранены.'
            ]
        ]);
    }
}