<?php /** @var App\Models\WpPost $wpPost */ ?>
@extends('layouts.admin-lte-app', ['title' => $wpPost->post_title ])

@section('content')
    @include('model-header')
    <!-- Wide card with share menu button -->
    <style>
        .demo-card-wide.mdl-card {
            width: 100%;
        }
        .demo-card-wide > .mdl-card__title {
            color: #fff;
            height: 300px;
            background: url('{{ $wpPost->image }}') center / cover;
        }
        .demo-card-wide > .mdl-card__menu {
            color: #fff;
        }
    </style>
   <div class="content">

       <div class="demo-card-wide mdl-card mdl-shadow--2dp">
           <div class="mdl-card__title">
               <h2 class="mdl-card__title-text">{{ $wpPost->post_title  }}</h2>
           </div>
           <div class="mdl-card__supporting-text">
               {{ $wpPost->excerpt  }}
           </div>
           <div class="mdl-color-text--grey-700 mdl-card__supporting-text">
               {!! $wpPost->post_content  !!}
           </div>
           <div class="mdl-card__actions mdl-card--border">
               <a class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect">
                   Get Started
               </a>
           </div>
           <div class="mdl-card__menu">
               <button class="mdl-button mdl-button--icon mdl-js-button mdl-js-ripple-effect">
                   <i class="material-icons">share</i>
               </button>
           </div>
       </div>
        <div class="box box-primary">
            <div class="box-body">
                <div class="row" style="padding-left: 20px">
                    @include('datalab.wp_posts.show_fields')
                    <a href="{{ route('datalab.posts.index') }}" class="btn btn-default">Back</a>
                </div>
            </div>
        </div>
    </div>
@endsection
