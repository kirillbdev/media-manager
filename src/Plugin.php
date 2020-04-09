<?php

namespace kirillbdev\MediaManager;

use IdeaCms\Core\Base\PluginBase;
use IdeaCms\Core\Contracts\ImageServiceInterface;
use kirillbdev\MediaManager\Services\ImageService;

class Plugin extends PluginBase
{
  public function navigation()
  {
    return [];
  }

  public function information()
  {
    return [
      'name' => 'kirillbdev/media-manager',
      'title' => 'Плагин для удобного управления медиа файлами системы.',
      'author' => 'Kirill Babinec',
      'version' => '1.0.0'
    ];
  }

  public function boot()
  {
    app()->bind(ImageServiceInterface::class, ImageService::class);

    if (idea()->isAdmin()) {
	    document()->addStyle('media-manager-css', idea()->asset('css/media-manager.min.css'));
    	document()->addScript('media-manager-js', idea()->asset('js/media-manager.js'));
    }
  }

  public function publishes()
  {
  	return [
  		__DIR__ . '/../public/css/media-manager.min.css' => public_path('css/media-manager.min.css'),
		  __DIR__ . '/../public/js/media-manager.js' => public_path('js/media-manager.js'),
	  ];
  }

  public function migrations()
  {
    return __DIR__ . '/../migrations';
  }

  protected function routes()
  {
    return __DIR__ . '/routes.php';
  }

  protected function views()
  {
    return __DIR__ . '/views';
  }
}