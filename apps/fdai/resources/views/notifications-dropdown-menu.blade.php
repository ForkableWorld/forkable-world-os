<!-- Notifications: style can be found in dropdown.less -->
<li class="dropdown notifications-menu">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <i class="fa fa-bell-o"></i>
        <span id="notifications-count" class="label label-warning">10</span>
    </a>
    <ul class="dropdown-menu">
        <li class="header">You have 10 notifications</li>
        <li>
            <!-- inner menu: contains the actual data -->
            <ul id="notifications-menu" class="menu">
                <li>
                    <a href="#">
                        <i class="ion ion-ios-people info"></i> Notification title
                    </a>
                </li>
                ...
            </ul>
        </li>
        <li class="footer"><a href="{{ route('datalab.notifications.index', []) }}">View all</a></li>
    </ul>
</li>