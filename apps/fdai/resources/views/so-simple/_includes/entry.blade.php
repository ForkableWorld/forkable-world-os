<?php /** @var \App\Studies\QMStudy $entry */ ?>
<article class="entry h-entry">
  <header class="entry-header">
    <h3 class="entry-title p-name">
        <a href="{{ $entry->getUrl() }}" rel="bookmark">{{  $entry->getTitleAttribute()  }}</a>
    </h3>
      <img class="entry-image u-photo" src="{{ $entry->getImage() }}" alt="">
  </header>

    <div class="entry-excerpt p-summary">
        {{ $entry->getTagLine() }}
    </div>
</article>
