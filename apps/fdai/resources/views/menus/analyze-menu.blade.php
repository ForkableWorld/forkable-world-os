<li id="analyze-menu" class="treeview">
    <a href="#"><i class="fa fa-line-chart"></i> <span>Analyze</span>
        <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
    </a>
    <ul class="treeview-menu">
        <li class="{{ Request::is('datalab/aggregateCorrelations*') ? 'active' : '' }}">
            <a href="{{ route('datalab.aggregateCorrelations.index') }}"><i class="fa fa-line-chart"></i><span>Global Variable Relationships</span></a>
        </li>
        <li class="{{ Request::is('datalab/commonTags*') ? 'active' : '' }}">
            <a href="{{ route('datalab.commonTags.index') }}"><i class="fa fa-tags"></i><span>Common Tags</span></a>
        </li>
        <li class="{{ Request::is('datalab/user_variable_relationships*') ? 'active' : '' }}">
            <a href="{{ route('datalab.user_variable_relationships.index') }}"><i class="fa fa-line-chart"></i><span>VariableRelationships</span></a>
        </li>
        <li class="{{ Request::is('datalab/measurements*') ? 'active' : '' }}">
            <a href="{{ route('datalab.measurements.index') }}"><i class="fa fa-edit"></i><span>Measurements</span></a>
        </li>
        <li class="{{ Request::is('datalab/wpPosts*') ? 'active' : '' }}">
            <a href="{{ route('datalab.posts.index') }}"><i class="fa fa-edit"></i><span>Posts</span></a>
        </li>
        <li class="{{ Request::is('datalab/studies*') ? 'active' : '' }}">
            <a href="{{ route('datalab.studies.index') }}"><i class="fa fa-book"></i><span>Studies</span></a>
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
    </ul>
</li>
