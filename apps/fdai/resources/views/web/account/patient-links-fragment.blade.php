<!--            <h5 class="text-center">
                <button href="{{ route('account.back') }}" type="button"  class="btn btn-sm btn-default">
                    <a href="{{ route('account.back') }}">Acting as {{ Auth::user()->display_name }}.  Click here to switch back to your account.</a>
                </button>
            </h5>-->
<h1 class="panel-heading1">
    <i class="fa fa-user"></i> Actions for {{ Auth::user()->display_name }}
</h1>
<a target="_blank"  href="https://patient.quantimo.do/#/app/reminders-manage?refreshUser=true" class="btn btn-primary btn-block" role="button">
    <h3><i class="fa fa-bell"></i> Add a Symptom Rating or Medication Reminder</h3>
</a>
<a target="_blank" href="https://patient.quantimo.do/#/app/history-all?refreshUser=true" class="btn btn-primary btn-block" role="button">
    <h3><i class="fa fa-history"></i> View Symptom and Treatment History</h3>
</a>
<a target="_blank"  href="https://patient.quantimo.do/#/app/measurement-add-search?refreshUser=true" class="btn btn-primary btn-block" role="button">
    <h3> <i class="fa fa-pencil"></i> Record a Measurement</h3>
</a>
<a target="_blank"  href="https://patient.quantimo.do/#/app/charts-search?refreshUser=true" class="btn btn-primary btn-block" role="button">
    <h3><i class="fa fa-line-chart"></i> View Charts</h3>
</a>
<a target="_blank" href="{{ route('account.back') }}" class="btn btn-primary btn-block" role="button">
    <h3><i class="fa fa-sign-out"></i> Switch Back to Your Account</h3>
</a>
