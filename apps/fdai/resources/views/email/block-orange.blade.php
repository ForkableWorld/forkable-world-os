<table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="100%" bgcolor="#f5774e">
    @if(isset($blockOrange['titleText']))
        <tr>
            @if(isset($noBackgroundColor))
                <td class="headline">
            @else
                <td style="background-color:#f5774e;" class="headline">
            @endif
                <br>
                {{$blockOrange['titleText']}}
            </td>
        </tr>
    @endif
    @if(isset($blockOrange['bodyText']))
        <tr>
            <td>
                <center>
                    <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="60%">
                        <tr>
                        @if(isset($noBackgroundColor))
                            <td style="color:#250002;">
                        @else
                            <td style="color:#933f24;">
                        @endif
                                <br>
                                {!! $blockOrange['bodyText'] !!}
                                <br><br>
                            </td>
                        </tr>
                    </table>
                </center>
            </td>
        </tr>
    @endif
    @if(isset($blockOrange['image']))
        <tr>
            <td>
                <img src="{{$blockOrange['image']['imageUrl']}}" width="{{$blockOrange['image']['width']}}" height="{{$blockOrange['image']['height']}}" alt="">
                <br><br>
            </td>
        </tr>
    @endif
    <tr>
        <td>
            <div><!--[if mso]>
                <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word"
                             href="{{$blockOrange['button']['link']}}" style="height:50px;v-text-anchor:middle;width:200px;" arcsize="8%" stroke="f" fillcolor="#ac4d2f">
                    <w:anchorlock/>
                    <center>
                <![endif]-->
                @if(isset($noBackgroundColor))
                    <a href="{{$blockOrange['button']['link']}}"
                       style="border-radius:4px;color:#2a0008;display:inline-block;font-family: Helvetica, Arial,
                   sans-serif;font-size:16px;font-weight:bold;line-height:50px;text-align:center;text-decoration:none;width:200px;-webkit-text-size-adjust:none;">
                        {{$blockOrange['button']['text']}}
                    </a>
                @else
                    <a href="{{$blockOrange['button']['link']}}"
                       style="background-color:#ac4d2f;border-radius:4px;color:#ffffff;display:inline-block;font-family: Helvetica, Arial,
                   sans-serif;font-size:16px;font-weight:bold;line-height:50px;text-align:center;text-decoration:none;width:200px;-webkit-text-size-adjust:none;">
                        {{$blockOrange['button']['text']}}
                    </a>
                @endif
                <!--[if mso]>
                </center>
                </v:roundrect>
                <![endif]-->
            </div>
            <br>
            <br>
        </td>
    </tr>
</table>