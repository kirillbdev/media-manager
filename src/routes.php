<?php

Route::namespace('\kirillbdev\MediaManager\Controllers')
  ->middleware([ 'web', 'idea_backend' ])
  ->group(function () {

    Route::get('media-manager/getImages', 'MediaManagerController@open');
    Route::post('admin/media-manager/upload', 'MediaManagerController@upload');
    Route::post('admin/media-manager/delete', 'MediaManagerController@delete');
    Route::post('admin/media-manager/createDirectory', 'MediaManagerController@createDirectory');
    Route::post(
      'admin/media-manager/rename',
      'MediaManagerController@rename'
    );

  });