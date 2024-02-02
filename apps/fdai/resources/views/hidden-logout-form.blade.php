<form id="logout-form"
      action="{{ qm_url('logout') }}"
      method="POST"
      style="display: none;">
    {{ qm_csrf_field() }}
</form>
