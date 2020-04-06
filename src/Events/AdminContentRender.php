<?php

namespace kirillbdev\MediaManager\Events;

class AdminContentRender
{
	public function handle()
	{
		echo view('kirillbdev/media-manager::media-manager')->render();
	}
}