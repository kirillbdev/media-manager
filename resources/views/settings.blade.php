@extends('idea::layouts.admin')

@section('content')
  @php
    idea_begin_form('media-manager-options', [
      'action' => \IdeaCms\Core\Helpers\Backend::url('media-manager/settings')
    ]);

    idea_field('checkbox', 'allow_svg')
      ->setTitle('Разрешить svg')
      ->setValue(1)
      ->setChecked($checked)
      ->render();

    idea_end_form();
  @endphp
@endsection