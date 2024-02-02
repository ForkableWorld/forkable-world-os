<li id="import-menu" class="treeview">
    <a href="#"><i class="fa fa-cloud-download"></i> <span>Import</span>
        <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
    </a>
    <ul class="treeview-menu">
        <li class="{{ Request::is('datalab/applications*') ? 'active' : '' }}">
            <a href="{{ route('datalab.applications.index') }}"><i class="fa fa-mobile"></i><span>Applications</span></a>
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
        <li class="{{ Request::is('datalab/connectorRequests*') ? 'active' : '' }}">
            <a href="{{ route('datalab.connectorRequests.index') }}"><i
                        class="fa fa-edit"></i><span>Connector Requests</span></a>
        </li>
        <li class="{{ Request::is('datalab/measurementImports*') ? 'active' : '' }}">
            <a href="{{ route('datalab.measurementImports.index') }}"><i class="fa fa-upload"></i><span>Measurement Imports</span></a>
        </li>
    </ul>
</li>