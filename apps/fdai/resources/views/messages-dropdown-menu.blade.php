<!-- Messages: style can be found in dropdown.less-->
<li class="dropdown messages-menu">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <i class="fa fa-envelope-o"></i>
        <span id="messages-count" class="label label-success">4</span>
    </a>
    <ul class="dropdown-menu">
        <li class="header">You have 4 messages</li>
        <li>
            <!-- inner menu: contains the actual data -->
            <ul id="messages-menu" class="menu">
                <li><!-- start message -->
                    <a href="#">
                        <div class="pull-left">
                            <img src="{{ Auth::user()->avatar_image }}" class="img-circle" alt="User Image">
                        </div>
                        <h4>
                            Sender Name
                            <small><i class="fa fa-clock-o"></i> 5 mins</small>
                        </h4>
                        <p>Message Excerpt</p>
                    </a>
                </li><!-- end message -->
                ...
            </ul>
        </li>
        <li class="footer"><a href="{{ route('datalab.messages.index', []) }}">See All Messages</a></li>
    </ul>
</li>