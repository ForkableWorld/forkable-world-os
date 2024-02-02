<?php /** @var \App\Studies\QMStudy $page */ ?>
@php($page = $page ?? $post ?? $model ?? null)

@if($page->getTags() )
    <div style="padding-top: 10px;">
	    <div>
		    <h3 class="page-taxonomies-title">
			    Tags
		    </h3>
	    </div>
        <div>
            @foreach($page->getTags() as $tag)
                <div class="justify-center items-center m-1 font-medium py-1 px-2 bg-white rounded-full text-gray-700 bg-gray-100 border border-gray-300 " 
                     style="display: inline-block;">
                    <span class="text-xs font-normal leading-none max-w-full flex-initial">
                        {{ $tag }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
@endif
