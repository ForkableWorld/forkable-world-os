<?php /** @var App\Models\BaseModel $v */ ?>
@extends('layouts.admin-lte-app', ['title' => "" ])
@php($v = \App\Models\Variable::findInMemoryOrDB(86858))
@section('content')
    {!!  $v->getDeleteValueButton(\App\Models\Variable::FIELD_IMAGE_URL)->getLink() !!}


    {!!  $v->getChangeValueButton(\App\Models\Variable::FIELD_IMAGE_URL)->getLink() !!}

@endsection
