<?php /** @var App\Models\BaseModel[] $models */ ?>
var store = [
    @foreach($models as $c)
      {
        "title": {{ $c->getTitle() }},
        "excerpt": {{ $c->getTagLine() }}
        "categories": {{ json_encode($c->getCategories()) }},
        "tags": {{ json_encode($c->getTags()) }},
        "url": {{ $c->getUrl() }}
      },
    @endforeach
]
