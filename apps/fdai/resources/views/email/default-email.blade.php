@include('email.blocks-email-header')
<body class="body" style="padding:0; margin:0; display:block; background:#ffffff; -webkit-text-size-adjust:none" bgcolor="#ffffff">
<table align="center" cellpadding="0" cellspacing="0" class="force-full-width" height="100%" >
    <tr>
        <td align="center" valign="top" bgcolor="#ffffff"  width="100%">
            <center>
                <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="600" class="w320">
                    <tr>
                        <td align="center" valign="top">
                            @if(isset($headerText))
                                <table cellpadding="0" cellspacing="0" class="force-full-width" style="margin:0 auto;">
                                    <tr>
                                        <td style="font-size: 40px; text-align:center;">
                                            {{$headerText}}
                                        </td>
                                    </tr>
                                    <br>
                                </table>
                                <br>
                            @endif
                            @if(isset($blockBlue))
                                <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" class="force-full-width" bgcolor="#4dbfbf">
                                    <tr>
                                        <td class="headline">
                                            <br>
                                            {{$blockBlue['titleText']}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>

                                            <center>
                                                <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="60%">
                                                    <tr>
                                                    @if(isset($noBackgroundColor))
                                                        <td>
                                                    @else
                                                        <td style="color:#187272;">
                                                    @endif
                                                    @if(isset($blockBlue['bodyText']))
                                                            <br>
                                                            {!! $blockBlue['bodyText'] !!}
                                                            <br>
                                                            <br>
                                                    @endif
                                                        </td>
                                                    </tr>
                                                </table>
                                            </center>
                                        </td>
                                    </tr>
                                    @if(isset($blockBlue['image']))
                                        <tr>
                                            <td style="text-align: center;">
                                                <img src="{{$blockBlue['image']['imageUrl']}}" width="{{$blockBlue['image']['width']}}" height="{{$blockBlue['image']['height']}}" alt="">
                                                <br>
                                                <br>
                                            </td>
                                        </tr>
                                    @endif
                                    @if(isset($blockBlue['button']))
                                        @include('email.block-blue-button')
                                    @endif
                                    @if(isset($blockBlue['buttons']))
                                        @foreach($blockBlue['buttons'] as $blockBlue['button'])
                                            @include('email.block-blue-button')
                                        @endforeach
                                    @endif
                                </table>
                            @endif
                            @if(isset($blockOrange))
                                @include('email.block-orange')
                            @endif
                            @include('email.block-brown')
                        </td>
                    </tr>
                </table>
            </center>
        </td>
    </tr>
</table>
{{--@include('download-buttons')--}}
</body>
</html>
