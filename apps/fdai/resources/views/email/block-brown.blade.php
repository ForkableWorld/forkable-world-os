<table cellpadding="0" cellspacing="0" class="force-full-width" bgcolor="#414141" style="margin: 0 auto;">
    @if(isset($blockBrownBodyText))
        <tr>
            @if(isset($noBackgroundColor))
                <td style="color:#100002; font-size:12px; text-align: center;">
            @else
                <td style="background-color:#414141; color:#bbbbbb; font-size:12px; text-align: center;">
            @endif
                    <br> <br> {!! $blockBrownBodyText !!}  <br>
                </td>
        </tr>
    @endif
    <tr>
        @if(isset($noBackgroundColor))
            <td style="text-align: center;">
        @else
            <td style="background-color:#414141; text-align: center;">
        @endif
            {{--@include('email.social')--}}
            <br>
            <br>
        </td>
    </tr>
    <tr>
        @if(isset($noBackgroundColor))
            <td style="font-size:12px; text-align: center;">
        @else
            <td style="color:#bbbbbb; font-size:12px; text-align: center;">
        @endif
        @include('email.email-physical-address-footer')
        </td>
    </tr>
</table>
