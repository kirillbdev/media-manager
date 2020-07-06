<?php

namespace kirillbdev\MediaManager\Controllers;

use Illuminate\Http\UploadedFile;
use kirillbdev\MediaManager\Model\Attachment;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use kirillbdev\MediaManager\Services\MediaManagerService;

class MediaManagerController extends Controller
{
    /**
     * Текущая директория изображений.
     *
     * @var string
     */
    private $directory = '/';
    private $dbItems;
    private $syncId = [];

    private $basePath;
    private $baseUrl;

    private $logs = [];

    /**
     * @var MediaManagerService
     */
    private $mediaManagerService;

    /**
     * MediaManagerController constructor.
     * @param Request $request
     */
    public function __construct(Request $request, MediaManagerService $mediaManagerService)
    {
        $this->mediaManagerService = $mediaManagerService;
        $this->basePath = config('idea_cms.media_manager_base_path');
        $this->baseUrl = config('idea_cms.media_manager_base_url');

        if ($request->input('directory') && $request->input('directory') !== '/') {
            $this->directory = $request->input('directory');
        }
    }

    /**
     * Вывод списка изображений в указанной директории.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function open(Request $request)
    {
        $data['files'] = [];
        $this->dbItems = Attachment::where('path', substr($this->directory, 1))->get();

        $files = glob($this->basePath . rtrim($this->directory, '/') . "/*");

        foreach ($files as $file) {

            $info = pathinfo($file);

            if (File::isDirectory($file)) {
                array_unshift($data['files'], [
                    'name' => $info['basename'],
                    'base_name' => $info['filename'],
                    'type' => 'directory',
                    'extension' => '',
                ]);
            } else {
                if ($this->directory === '/') {
                    $relativeUrl = '/' . implode('/', [
                            $this->baseUrl,
                            $info['basename']
                        ]);
                } else {
                    $relativeUrl = '/' . implode('/', [
                            $this->baseUrl,
                            trim($this->directory, '/'),
                            $info['basename']
                        ]);
                }

                $data['files'][] = [
                    'id' => $this->syncFileWithDb($info),
                    'name' => $info['basename'],
                    'base_name' => $info['filename'],
                    'extension' => $info['extension'],
                    'type' => 'file',
                    'preview' => $relativeUrl,
                    'url' => $relativeUrl
                ];
            }

        }

        foreach ($this->dbItems as $attachment) {
            if (!in_array($attachment->id, $this->syncId)) {
                $attachment->delete();
            }
        }

        return $data;
    }

    /**
     * POST
     * Асинхрованная подгрузка изображений из front-end
     *
     * @param Request $request
     * @return array
     */
    public function upload(Request $request)
    {
        $files = $request->file('file');

        foreach ($files as $file) {
            $this->uploadFile($file);
        }

        return response()->json([
            'success' => true,
            'errors' => $this->logs
        ]);
    }

    public function createDirectory(Request $request)
    {
        $dirName = $this->mediaManagerService->prepareFilename($request->input('name'));

        File::makeDirectory($this->basePath . $this->directory . '/' . $dirName);

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * POST
     * Асинхрованное удаление изображения
     *
     * @param Request $request
     * @return array
     */
    public function delete(Request $request)
    {
        if (!$request->input('name')) {
            return [
                'success' => false
            ];
        }

        if (File::exists($this->basePath . $this->directory . '/' . $request->input('name'))) {
            if (is_dir($this->basePath . $this->directory . '/' . $request->input('name'))) {
                File::deleteDirectory($this->basePath . $this->directory . '/' . $request->input('name'));
                Attachment::where('path', trim($this->directory . '/' . $request->input('name'), '/'))->delete();
            } else {
                File::delete($this->basePath . $this->directory . '/' . $request->input('name'));
                Attachment::where([
                    ['path', trim($this->directory, '/')],
                    ['name', $request->input('name')]
                ])->delete();
            }

            return [
                'success' => true
            ];
        }

        return [
            'success' => false
        ];
    }

    public function rename(Request $request)
    {
        if (!$request->input('old_name') || !$request->input('new_name')) {
            return [
                'success' => false
            ];
        }

        if (File::exists($this->basePath . $this->directory . '/' . $request->input('old_name'))) {
            File::move(
                $this->basePath . $this->directory . '/' . $request->input('old_name'),
                $this->basePath . $this->directory . '/' . $request->input('new_name')
            );

            Attachment::where('path', trim($this->directory, '/'))
                ->where('name', $request->input('old_name'))
                ->update([
                    'name' => $request->input('new_name')
                ]);
        }

        return [
            'success' => true
        ];
    }

    private function syncFileWithDb($file)
    {
        if (strlen($file['dirname']) > strlen($this->basePath)) {
            $relPath = substr($file['dirname'], strlen(public_path('image/uploads')) + 1);
        } else {
            $relPath = '';
        }

        $attachment = Attachment::where('path', $relPath)
            ->where('name', $file['basename'])
            ->first();

        if (!$attachment) {
            $id = Attachment::create([
                'path' => $relPath,
                'name' => $file['basename']
            ])->id;
        } else {
            $id = $attachment->id;
        }

        $this->syncId[] = $id;

        return $id;
    }

    /**
     * @param UploadedFile $file
     */
    private function uploadFile($file)
    {
        if ($file->isValid() && in_array($file->extension(), [ 'jpg', 'png', 'jpeg' ])) {
            $fileName = $this->mediaManagerService->prepareFilename($file->getClientOriginalName());

            $file->move($this->basePath . $this->directory, $fileName);
        }
        else {
            $this->logs[] = 'Файл ' . $file->getClientOriginalName() . ' поврежден или имеет недопустимый формат.';
        }
    }
}