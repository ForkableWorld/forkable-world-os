<p class="mt-8 text-center text-lg text-80">
    {!!
        \App\Menus\FooterMenu::instance()->getTailwindHorizontalLinks()
    !!}
</p>
<p class="mt-8 text-center text-xs text-80">
    <a href="{{ home_page() }}" class="text-primary dim no-underline">{{ app_display_name() }}</a>
    <span class="px-1">&middot;</span>
    &copy; {{ date('Y') }}
</p>
