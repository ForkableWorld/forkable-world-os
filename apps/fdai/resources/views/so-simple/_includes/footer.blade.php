<footer id="footer" class="site-footer">
  @include('so-simple/_includes/footer-custom')

    <div class="social-icons">
        @foreach(\App\Menus\FooterMenu::buttons() as $button)
            <a class="social-icon" href="{{ $button->getUrl() }}">
                <i class="{{ $button->getFontAwesome() }} fa-2x" title="{{ $button->getTooltip() }}"></i>
            </a>
        @endforeach
    </div>

  <div class="copyright">
      <p>&copy; 2021
          <a href="{{ home_page() }}" rel="nofollow">
              {{ app_display_name() }}
          </a>
      </p>
  </div>
</footer>
@include('components.buttons.chat-button')
