<?php
/** @var \App\Models\User $user */
$user = Auth::user();
?>

        <!-- Navbar Right Menu -->
<div class="navbar-custom-menu">
    <!-- // add this dropdown // -->
    <ul class="nav navbar-nav">
        <li class="dropdown messages-menu"
            title="You Have {{ \App\Types\QMStr::abbreviateNumber($user->number_of_correlations) }} Discoveries">
            <a href="{{ \App\Models\UserVariableRelationship::generateDataLabIndexUrl() }}" onclick="showLoader()">
                <i class="{{ \App\Models\UserVariableRelationship::FONT_AWESOME }}"></i>
                {{--                <span id="user_variable_relationships-count" class="label label-danger">
                                    {{ \App\Utils\StringHelper::abbreviateNumber($user->number_of_correlations) }}
                                </span>--}}
            </a>
        </li>
        <li class="dropdown messages-menu"
            title="You Have {{ \App\Types\QMStr::abbreviateNumber($user->number_of_user_variables) }} Variables">
            <a href="{{ \App\Models\UserVariable::generateDataLabIndexUrl() }}" onclick="showLoader()">
                <i class="{{ \App\Models\UserVariable::FONT_AWESOME }}"></i>
                {{--                <span id="variables-count" class="label label-info">
                                    {{ \App\Utils\StringHelper::abbreviateNumber($user->number_of_user_variables) }}
                                </span>--}}
            </a>
        </li>
        <li class="dropdown messages-menu"
            title="You Have {{ \App\Types\QMStr::abbreviateNumber($user->number_of_tracking_reminders) }} Tracking Reminders">
            <a href="{{ \App\Models\TrackingReminder::generateDataLabIndexUrl() }}" onclick="showLoader()">
                <i class="{{ \App\Models\TrackingReminder::FONT_AWESOME }}"></i>
                {{--                <span id="variables-count" class="label label-warning">
                                    {{ \App\Utils\StringHelper::abbreviateNumber($user->number_of_tracking_reminders) }}
                                </span>--}}
            </a>
        </li>
        <li class="dropdown messages-menu"
            title="You've Connected {{ \App\Types\QMStr::abbreviateNumber($user->number_of_connections) }} Data Sources">
            <a href="{{ \App\Models\Connection::generateDataLabIndexUrl() }}" onclick="showLoader()">
                <i class="{{ \App\Models\Connection::FONT_AWESOME }}"></i>
                {{--                <span id="variables-count" class="label label-success">
                                    {{ \App\Utils\StringHelper::abbreviateNumber($user->number_of_connections) }}
                                </span>--}}
            </a>
        </li>
        <li class="dropdown messages-menu"
            title="Go To Inbox">
            <a href="{{ \App\Utils\IonicHelper::getInboxUrl() }}" target="_blank">
                <i class="{{ \App\UI\FontAwesome::INBOX_SOLID }}"></i>
                {{--                <span id="variables-count" class="label label-success">
                                    {{ \App\Utils\StringHelper::abbreviateNumber($user->number_of_connections) }}
                                </span>--}}
            </a>
        </li>
        <li id="search-icon" class="dropdown messages-menu" title="Search">
            @if( $useSearchModal )
                <a href="#" onclick="toggleModal()" title="Search">
                    <i class="{{ \App\UI\FontAwesome::SEARCH_SOLID }}"></i>
                </a>
            @else
                <a href="{{ \App\Utils\IonicHelper::getSearchUrl() }}"
                   target="_blank"
                   title="Search">
                    <i class="{{ \App\UI\FontAwesome::SEARCH_SOLID }}"></i>
                </a>
            @endif
        </li>
        @isadmin <!-- Still need to implement these -->
        {{--            @include('messages-dropdown-menu')--}}
        {{--            @include('notifications-dropdown-menu')--}}
        {{--            @include('tasks-dropdown-menu')--}}
        @endisadmin
        <!-- User Account Menu -->
        <li class="dropdown user user-menu">
            <!-- Menu Toggle Button -->
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <!-- The user image in the navbar-->
                <img src="{{ $user->avatar_image}}"
                     class="user-image" alt="User Image"/>
                <!-- hidden-xs hides the username on small devices so only the image appears. -->
                {{--                <span class="hidden-xs">{{ $user->display_name }}</span> user name takes up lots of space --}}
            </a>
            <ul class="dropdown-menu">
                <!-- The user image in the menu -->
                <li class="user-header">
                    <img src="{{ $user->avatar_image }}"
                         class="img-circle" alt="User Image"/>
                    <p>
                        {{ $user->display_name }}
                        <small>Member since {{ $user->created_at->format('M. Y') }}</small>
                    </p>
                </li>
                <!-- Menu Footer-->
                <li class="user-footer">
                    <div class="pull-left">
                        <a href="{{ $user->getUrl() }}" class="btn btn-default btn-flat">Profile</a>
                    </div>
                    <div class="pull-right">
                        <a href="{{ qm_url('logout') }}" class="btn btn-default btn-flat">
                            Sign out
                        </a>
                    </div>
                </li>
            </ul>
        </li>
    </ul>
</div>
