<?php

namespace kirillbdev\MediaManager\Model;

use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManagerStatic;

class Image
{
	private $imagePath;
	private $relativePath;
	private $fileInfo;
	private $cacheRelativePath;
	private $savePath;

	public function __construct($path)
	{
		$this->imagePath = config('idea_cms.image_path');
		$this->relativePath = trim(str_replace($this->imagePath, '', $path), '/');
		$this->fileInfo = pathinfo($path);

		$this->makeCacheDir($this->relativePath);
	}

	public function resize($width, $height, $mode = null)
	{
		$cacheImgPath = $this->savePath . '/' . $this->fileInfo['filename'] . '-' . $width . 'x' . $height . '.' . $this->fileInfo['extension'];

		if (file_exists($this->imagePath . '/' . $cacheImgPath)) {
			return url('image/' . $cacheImgPath);
		}

		$canvas = ImageManagerStatic::canvas($width, $height, '#fff');
		$img = ImageManagerStatic::make($this->imagePath . '/' . $this->relativePath);

		if ('fit' === $mode) {
			$img->fit($width, $height);
		}
		else {
			if ($img->width() > $img->height()) {
				$img->widen($width);
			}
			else {
				$img->heighten($height);
			}
		}

		$canvas->insert($img, 'center');
		$canvas->save($this->imagePath . '/' . $cacheImgPath);

		return $img ? url('image/' . $cacheImgPath) : '';
	}

	private function makeCacheDir($path)
	{
		$cacheDir = str_replace($this->fileInfo['basename'], '', $path);
		$cacheDir = trim($cacheDir, '/');
		$cacheRelativePath = 'cache/' . $cacheDir;

		if ( ! file_exists($this->imagePath . '/' . $cacheRelativePath)) {
			if (File::makeDirectory($this->imagePath . '/' . $cacheRelativePath, 0755, true)) {
				$this->savePath = $cacheRelativePath;
			}
		}
		else {
			$this->savePath = $cacheRelativePath;
		}
	}
}