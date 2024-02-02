<footer class="footer main-footer">
    <div class="container-fluid">
        <nav class="float-left">
            {!! \App\Menus\GeneralMenu::instance()->getHorizontalList() !!}
        </nav>
    </div>
</footer>
<footer class="main-footer" style="max-height: 100px;text-align: center; margin-left: 0;">
    <strong>Copyright © 2020 <a href="#">{{ app_display_name() }}</a>.</strong> All rights reserved.
</footer>