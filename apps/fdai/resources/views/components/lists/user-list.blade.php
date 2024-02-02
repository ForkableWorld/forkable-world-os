@foreach($users as $applicationUser)
    <tr>
        {{--<td> {{ $key + 1 }}</td>--}}
        <td class="avatar"> <a target='_blank' href="{{$applicationUser->avatar}}"><img src="{!! $applicationUser->avatar !!}" alt="img" class="img-circle img-responsive" height="35" width="35"/></a> </td>
        @if($application->user_id == Auth::user()->ID)
            <td><a target='_blank' href="https://patient.quantimo.do/#/app/history-all?accessToken={{$applicationUser->access_token}}&doNotRemember=true">{{ $applicationUser->user_login }}</a></td>
            <td><a target='_blank' href="https://patient.quantimo.do/#/app/history-all?accessToken={{$applicationUser->access_token}}&doNotRemember=true">{{ $applicationUser->user_email }}</a></td>
            <td><a target='_blank' href="https://patient.quantimo.do/#/app/history-all?accessToken={{$applicationUser->access_token}}&doNotRemember=true">{{ $applicationUser->display_name }}</a></td>
        @else
            <td>{{ $applicationUser->user_login }}</td>
            <td><a target='_blank' href="mailto:{{ $applicationUser->user_email }}">{{ $applicationUser->user_email }}</a></td>
            <td>{{ $applicationUser->display_name }}</td>
        @endif
        {{--<td><a href="{{ route('act-as/physician', ['clientId' => $application->id, 'userId' => $applicationUser->ID]) }}">{{ $applicationUser->user_email }}</a></td>--}}
    </tr>
@endforeach
