@foreach($model->getSideMenus() as $menu)
    @include('side-menu', ['menu' => $menu])
@endforeach