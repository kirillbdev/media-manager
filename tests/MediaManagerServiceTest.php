<?php

namespace kirillbdev\MediaManager\tests;

use IdeaCms\Core\Tests\IdeaTestCase;
use kirillbdev\MediaManager\Services\MediaManagerService;

class MediaManagerServiceTest extends IdeaTestCase
{
    /**
     * @var MediaManagerService
     */
    private $mediaManagerService;

    public function createApplication()
    {
        $app = parent::createApplication();

        $this->mediaManagerService = new MediaManagerService();

        return $app;
    }

    public function testTranslitOnCreateDirOrUpload()
    {
        $filename = 'какой-то файл.jpg';

        $this->assertEquals('kakoi-to-fail.jpg', $this->mediaManagerService->prepareFilename($filename));

        $filename = 'просто_папка   Для вывода';

        $this->assertEquals('prosto_papka-dlya-vyvoda', $this->mediaManagerService->prepareFilename($filename));

        $filename = ' какой-то файл с пробелами .jpg';

        $this->assertEquals('kakoi-to-fail-s-probelami.jpg', $this->mediaManagerService->prepareFilename($filename));
    }
}