<aside class="main-sidebar" id="sidebar-wrapper">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar user panel (optional) -->
        <div class="user-panel">
            <a href="{{ Auth::user()->getUrl() }}">
                <div class="pull-left image">
                    <img src="{{ Auth::user()->avatar_image }}" class="img-circle" style="max-height: 50px;"
                         alt="User Image"/>
                </div>
                <div class="pull-left info">
                    @if ( Auth::guest() )
                        <p>{{ app_display_name() }}</p>
                    @else
                        <p>{{ Auth::user()->display_name}}</p>
                @endif
                <!-- Status -->
                    <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                </div>
            </a>
        </div>

        <!-- search form (Optional) -->
        <form action="#" method="get" class="sidebar-form">
            <div class="input-group" style="background-color: white;">
                <input id="menu-search-input"
                       type="text"
                       name="q"
                       class="form-control"
                       placeholder="Search..."
                       onkeyup="filterSearchMenu()"/>
                <span class="input-group-btn">
                    <button type='submit' name='search' id='search-btn' class="btn btn-flat">
                        <i class="fa fa-search"></i>
                    </button>
                </span>
            </div>
        </form>
	    {!! \App\Menus\SearchMenu::instance()->getHiddenSearchMenuList() !!}
        <!-- Sidebar Menu -->
        <ul id="sidebar-menu" class="sidebar-menu" data-widget="tree">
	        @php
		        if(!isset($activePage)){$activePage = route_name();}
	        @endphp

	        <li class="{{ Request::is('datalab/userVariables*') ? 'active' : '' }}">
		        <a href="{{ route('datalab.userVariables.index') }}"><i
				        class="fa fa-user"></i><span>User Variables</span></a>
	        </li>

	        <li class="{{ Request::is('datalab/variables*') ? 'active' : '' }}">
		        <a href="{{ route('datalab.variables.index') }}"><i class="fa 
		        fa-users"></i><span>Global Variables</span></a>
	        </li>
	        <li class="{{ Request::is('datalab/aggregateCorrelations*') ? 'active' : '' }}">
		        <a href="{{ route('datalab.aggregateCorrelations.index') }}"><i class="fa fa-users"></i><span>
				        Global Variable Relationships
			        </span></a>
	        </li>
	        <li class="{{ Request::is('datalab/user_variable_relationships*') ? 'active' : '' }}">
		        <a href="{{ route('datalab.user_variable_relationships.index') }}"><i class="fa fa-user"></i><span>
				        User Variable Relationships
			        </span></a>
	        </li>
	        <li class="{{ Request::is('datalab/commonTags*') ? 'active' : '' }}">
		        <a href="{{ route('datalab.commonTags.index') }}"><i class="fa fa-tags"></i><span>Common Tags</span></a>
	        </li>
	        <li class="{{ Request::is('datalab/measurements*') ? 'active' : '' }}">
		        <a href="{{ route('datalab.measurements.index') }}"><i class="fa fa-edit"></i><span>Measurements</span></a>
	        </li>
	        <li class="{{ Request::is('datalab/studies*') ? 'active' : '' }}">
		        <a href="{{ route('datalab.studies.index') }}"><i class="fa fa-book"></i><span>Studies</span></a>
	        </li>
	        <li class="{{ Request::is('datalab/userTags*') ? 'active' : '' }}">
		        <a href="{{ route('datalab.userTags.index') }}"><i class="fa fa-tags"></i><span>User Tags</span></a>
	        </li>
	        <li class="{{ Request::is('datalab/users*') ? 'active' : '' }}">
		        <a href="{{ route('datalab.users.index') }}"><i class="fa fa-users"></i><span>Users</span></a>
	        </li>
	        <li class="{{ Request::is('datalab/applications*') ? 'active' : '' }}">
		        <a href="{{ route('datalab.applications.index') }}"><i class="fa fa-mobile"></i><span>Applications</span></a>
	        </li>
{{--	        <li>--}}
{{--	            <span class="text-capitalize text-xl-left logo-xl">Data Import</span>--}}
{{--	        </li>--}}
	        <li class="{{ Request::is('datalab/connections*') ? 'active' : '' }}">
		        <a href="{{ route('datalab.connections.index') }}"><i class="fa 
		        fa-download"></i><span>API Connections</span></a>
	        </li>
	        <li class="{{ Request::is('datalab/connectorImports*') ? 'active' : '' }}">
		        <a href="{{ route('datalab.connectorImports.index') }}"><i class="fa fa-cloud-download"></i><span>Connector Imports</span></a>
	        </li>
	        <li class="{{ Request::is('datalab/connectors*') ? 'active' : '' }}">
		        <a href="{{ route('datalab.connectors.index') }}"><i
				        class="fa fa-cloud-upload"></i><span>Connectors</span></a>
	        </li>
	        <li class="{{ Request::is('datalab/connectorRequests*') ? 'active' : '' }}">
		        <a href="{{ route('datalab.connectorRequests.index') }}"><i
				        class="fa fa-edit"></i><span>Connector Requests</span></a>
	        </li>
	        <li class="{{ Request::is('datalab/measurementImports*') ? 'active' : '' }}">
		        <a href="{{ route('datalab.measurementImports.index') }}"><i class="fa fa-upload"></i><span>Measurement Imports</span></a>
	        </li>
	        <li class="{{ Request::is('datalab/applications*') ? 'active' : '' }}">
		        <a href="{{ route('datalab.applications.index') }}"><i class="fa fa-mobile"></i><span>Applications</span></a>
	        </li>
	        <li class="{{ Request::is('datalab/collaborators*') ? 'active' : '' }}">
		        <a href="{{ route('datalab.collaborators.index') }}"><i class="fa fa-users"></i><span>Collaborators</span></a>
	        </li>
	        <li class="{{ Request::is('datalab/commonTags*') ? 'active' : '' }}">
		        <a href="{{ route('datalab.commonTags.index') }}"><i class="fa fa-tags"></i><span>Common Tags</span></a>
	        </li>
	        <li class="{{ Request::is('datalab/notifications*') ? 'active' : '' }}">
		        <a href="{{ route('datalab.notifications.index') }}"><i class="fa fa-bell"></i><span>Notifications</span></a>
	        </li>
	        <li class="{{ Request::is('datalab/sentEmails*') ? 'active' : '' }}">
		        <a href="{{ route('datalab.sentEmails.index') }}"><i
				        class="fa fa-mail-forward"></i><span>Sent Emails</span></a>
	        </li>
	        <li class="{{ Request::is('datalab/trackingReminders*') ? 'active' : '' }}">
		        <a href="{{ route('datalab.trackingReminders.index') }}"><i
				        class="fa fa-edit"></i><span>Tracking Reminders</span></a>
	        </li>
	        <li class="{{ Request::is('datalab/trackingReminderNotifications*') ? 'active' : '' }}">
		        <a href="{{ route('datalab.trackingReminderNotifications.index') }}"><i class="fa fa-edit"></i><span>Tracking Reminder Notifications</span></a>
	        </li>
	        <li class="{{ Request::is('datalab/variableCategories*') ? 'active' : '' }}">
		        <a href="{{ route('datalab.variableCategories.index') }}"><i
				        class="fa fa-tags"></i><span>Variable Categories</span></a>
	        </li>
	        <li class="{{ Request::is('datalab/votes*') ? 'active' : '' }}">
		        <a href="{{ route('datalab.votes.index') }}"><i class="fa fa-thumbs-up"></i><span>Votes</span></a>
	        </li>
	        <li class="{{ Request::is('datalab/unitCategories*') ? 'active' : '' }}">
		        <a href="{{ route('datalab.unitCategories.index') }}"><i class="fa fa-edit"></i><span>Unit Categories</span></a>
	        </li>
	        @include('menus.account-menu-items')
{{--	        @include('menus.devops-menu')--}}
{{--	        @include('menus.database-menu')--}}
	        @include('menus.design-menu')
	        @include('menus.developer-menu')

        </ul>
        <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>
