<li id="models-menu" class="treeview">
    <a href="#"><i class="fa fa-link"></i> <span>Models</span>
        <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
    </a>
    <ul class="treeview-menu">
        <li class="{{ Request::is('datalab/aggregateCorrelations*') ? 'active' : '' }}">
            <a href="{{ route('datalab.aggregateCorrelations.index') }}"><i class="fa fa-line-chart"></i><span>Global Variable Relationships</span></a>
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
        <li class="{{ Request::is('datalab/connections*') ? 'active' : '' }}">
            <a href="{{ route('datalab.connections.index') }}"><i class="fa fa-download"></i><span>Connections</span></a>
        </li>
        <li class="{{ Request::is('datalab/connectorImports*') ? 'active' : '' }}">
            <a href="{{ route('datalab.connectorImports.index') }}"><i class="fa fa-cloud-download"></i><span>Connector Imports</span></a>
        </li>
        <li class="{{ Request::is('datalab/connectors*') ? 'active' : '' }}">
            <a href="{{ route('datalab.connectors.index') }}"><i
                        class="fa fa-cloud-upload"></i><span>Connectors</span></a>
        </li>
        <li class="{{ Request::is('datalab/user_variable_relationships*') ? 'active' : '' }}">
            <a href="{{ route('datalab.user_variable_relationships.index') }}"><i class="fa fa-line-chart"></i><span>VariableRelationships</span></a>
        </li>
        <li class="{{ Request::is('datalab/measurements*') ? 'active' : '' }}">
            <a href="{{ route('datalab.measurements.index') }}"><i class="fa fa-edit"></i><span>Measurements</span></a>
        </li>
        <li class="{{ Request::is('datalab/measurementExports*') ? 'active' : '' }}">
            <a href="{{ route('datalab.measurementExports.index') }}"><i class="fa fa-download"></i><span>Measurement Exports</span></a>
        </li>
        <li class="{{ Request::is('datalab/measurementImports*') ? 'active' : '' }}">
            <a href="{{ route('datalab.measurementImports.index') }}"><i class="fa fa-upload"></i><span>Measurement Imports</span></a>
        </li>
        <li class="{{ Request::is('datalab/notifications*') ? 'active' : '' }}">
            <a href="{{ route('datalab.notifications.index') }}"><i class="fa fa-bell"></i><span>Notifications</span></a>
        </li>
        <li class="{{ Request::is('datalab/wpPosts*') ? 'active' : '' }}">
            <a href="{{ route('datalab.posts.index') }}"><i class="fa fa-edit"></i><span>Posts</span></a>
        </li>
        <li class="{{ Request::is('datalab/purchases*') ? 'active' : '' }}">
            <a href="{{ route('datalab.purchases.index') }}"><i class="fa fa-cart-plus"></i><span>Purchases</span></a>
        </li>
        <li class="{{ Request::is('datalab/sentEmails*') ? 'active' : '' }}">
            <a href="{{ route('datalab.sentEmails.index') }}"><i
                        class="fa fa-mail-forward"></i><span>Sent Emails</span></a>
        </li>
        <li class="{{ Request::is('datalab/studies*') ? 'active' : '' }}">
            <a href="{{ route('datalab.studies.index') }}"><i class="fa fa-book"></i><span>Studies</span></a>
        </li>
        <li class="{{ Request::is('datalab/subscriptions*') ? 'active' : '' }}">
            <a href="{{ route('datalab.subscriptions.index') }}"><i class="fa fa-cart-plus"></i><span>Subscriptions</span></a>
        </li>
        <li class="{{ Request::is('datalab/trackingReminders*') ? 'active' : '' }}">
            <a href="{{ route('datalab.trackingReminders.index') }}"><i
                        class="fa fa-edit"></i><span>Tracking Reminders</span></a>
        </li>
        <li class="{{ Request::is('datalab/trackingReminderNotifications*') ? 'active' : '' }}">
            <a href="{{ route('datalab.trackingReminderNotifications.index') }}"><i class="fa fa-edit"></i><span>Tracking Reminder Notifications</span></a>
        </li>
        <li class="{{ Request::is('datalab/userTags*') ? 'active' : '' }}">
            <a href="{{ route('datalab.userTags.index') }}"><i class="fa fa-tags"></i><span>User Tags</span></a>
        </li>
        <li class="{{ Request::is('datalab/userVariables*') ? 'active' : '' }}">
            <a href="{{ route('datalab.userVariables.index') }}"><i
                        class="fa fa-line-chart"></i><span>User Variables</span></a>
        </li>
        <li class="{{ Request::is('datalab/users*') ? 'active' : '' }}">
            <a href="{{ route('datalab.users.index') }}"><i class="fa fa-users"></i><span>Users</span></a>
        </li>
        <li class="{{ Request::is('datalab/variables*') ? 'active' : '' }}">
            <a href="{{ route('datalab.variables.index') }}"><i class="fa fa-line-chart"></i><span>Variables</span></a>
        </li>
        <li class="{{ Request::is('datalab/variableCategories*') ? 'active' : '' }}">
            <a href="{{ route('datalab.variableCategories.index') }}"><i
                        class="fa fa-tags"></i><span>Variable Categories</span></a>
        </li>
        <li class="{{ Request::is('datalab/votes*') ? 'active' : '' }}">
            <a href="{{ route('datalab.votes.index') }}"><i class="fa fa-thumbs-up"></i><span>Votes</span></a>
        </li>
    </ul>
</li>
