<li id="database-menu" class="treeview">
    <a href="#"><i class="fa fa-database"></i> <span>DB</span>
        <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
    </a>
    <ul class="treeview-menu">
        <li>
            <a href="http://quantimodo2.asuscomm.com:8082/" target="_blank"><i class="fab fa-digital-ocean"></i>
                <span>Digital Ocean</span>
            </a>
        </li>
        @include('menus.items.slow-queries-menu-item')
        @include('menus.items.nginx-amplify-menu-item')
    </ul>
</li>