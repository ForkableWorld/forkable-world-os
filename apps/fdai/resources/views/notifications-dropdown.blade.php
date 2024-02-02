<li class="nav-item dropdown">
	<a
		class="nav-link" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown"
		aria-haspopup="true" aria-expanded="false"
	>
		<i class="material-icons">notifications</i>
{{--		@if(auth()->user())
			<span class="notification">{{ count(auth()->user()->getUserErrorMessages()) }}</span>
		@endif--}}
		<p class="d-lg-none d-md-block">
			{{ __('Some Actions') }}
		</p>
	</a>
	<div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
{{--		@if(auth()->user())
			@foreach(auth()->user()->getUserErrorMessages() as $error)
				<a class="dropdown-item" href="#">{{ $error->getTruncated(100) }}</a>
			@endforeach
		@endif--}}
		<a class="dropdown-item" href="https://web.quantimo.do/#/app/reminders-inbox">{{ __('Reminder Inbox') }}</a>
	</div>
</li>
