<?php

namespace kirillbdev\MediaManager\Services;

use IdeaCms\Core\Contracts\ImageServiceInterface;
use Illuminate\Support\Facades\DB;
use kirillbdev\MediaManager\Model\Image;

class ImageService implements ImageServiceInterface
{
  public function getImage($path, $width = null, $height = null, $mode = null)
  {
    if ( ! $path) {
      return '';
    }

    if ($width && ! $height) {
      $height = $width;
    }
    elseif ($height && ! $width) {
      $width = $height;
    }

    if ((int)$path > 0) {
      $attachment = $this->getAttachmentById((int)$path);

      if ($attachment) {
        $path = trim($attachment->path . '/' . $attachment->name, '/');
      }
      else {
        return '';
      }
    }

    if ( ! file_exists(config('idea_cms.media_manager_base_path') . '/' . trim($path, '/'))) {
      return '';
    }

    $ext = pathinfo(config('idea_cms.media_manager_base_path') . '/' . trim($path, '/'), PATHINFO_EXTENSION);

    if ('svg' !== $ext && $width && $height) {
      $image = new Image(config('idea_cms.media_manager_base_path') . '/' . trim($path, '/'));

      return $image->resize($width, $height, $mode);
    }

    return str_replace(' ', '%20', url(config('idea_cms.media_manager_base_url') . '/' . trim($path, '/')));
  }

  private function getAttachmentById($id)
  {
    $attachment = DB::table('attachments')
      ->find($id);

    return $attachment;
  }

  private function getAttachmentPreviewById($id)
  {
    $attachment = $this->getAttachmentById($id);

    return $attachment ? ('/' . config('idea_cms.media_manager_base_url') . '/' . $attachment->path . '/' . $attachment->name) : null;
  }
}