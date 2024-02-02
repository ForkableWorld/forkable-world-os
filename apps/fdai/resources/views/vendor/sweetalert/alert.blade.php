@if(config('sweetalert.animation.enable'))
    <link rel="stylesheet" href="{{ config('sweetalert.animatecss') }}">
@endif
<script src="{{ config('sweetalert.cdn') }}"></script>
@if (Session::has('alert.config'))
<script>
    Swal.fire({!! Session::pull('alert.config') !!});
</script>
@endif
