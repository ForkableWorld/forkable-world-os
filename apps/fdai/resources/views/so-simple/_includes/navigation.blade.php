<?php /** @var \App\Buttons\QMButton[] $buttons */ ?>
  <div class="navigation-wrapper">
    <a href="#menu-toggle" id="menu-toggle">Menu</a>
    <nav id="primary-nav" class="site-nav animated drop">
      <ul>
        @foreach($buttons as $button)
          <li>
              <a href="{{ $button->getUrl() }}">
                  {!! $button->getFontAwesomeHtml() !!}&nbsp;
                  {{ $button->getTitleAttribute() }}
              </a>
          </li>
        @endforeach
      </ul>
    </nav>
  </div><!-- /.navigation-wrapper -->
<script>
    $(document).ready(function() {
        // main menu toggle
        var toggleButton = document.getElementById("menu-toggle");
        var menu = document.getElementById("primary-nav");
        if (toggleButton && menu) {
            toggleButton.addEventListener("click", function() {
                menu.classList.toggle("js-menu-is-open");
            });
        }
        // initialize smooth scroll
        // Not sure what smooth scroll is for? $("a").smoothScroll({ offset: -20 });
        // add lightbox class to all image links
        $("a[href$='.jpg'], a[href$='.png'], a[href$='.gif']").attr("data-lity", "");
    });
</script>
