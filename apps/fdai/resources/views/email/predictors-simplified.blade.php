@include('email.blocks-email-header')
<body class="body" style="padding:0; margin:0; display:block; background:#ffffff; -webkit-text-size-adjust:none" bgcolor="#ffffff">
<table align="center" cellpadding="0" cellspacing="0" class="force-full-width" height="100%" >
    <tr>
        <td align="center" valign="top" bgcolor="#ffffff"  width="100%">
            <center>
                <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="600" class="w320">
                    <tr>
                        <td align="center" valign="top">
                            <table cellpadding="0" cellspacing="0" class="force-full-width" style="margin:0 auto;">
                                <tr>
                                    <td style="font-size: 40px; text-align:center;">
                                        {{$headerText}}
                                    </td>
                                </tr>
                            </table>
                            <br>
                            <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" class="force-full-width" >

                                <tr>
                                    <td>

                                        <center>
                                            <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="60%">
                                                <tr>
                                                    <td style="color: rgb(65, 64, 66);">
                                                        <br>
                                                        {!! $blockBlue['bodyText'] !!}
                                                    </td>
                                                </tr>
                                            </table>
                                        </center>

                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <img src="https://s3-us-west-1.amazonaws.com/qmimages/cute-robot-transparent.png"
                                             width="224" height="240" alt="robot picture">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="headline-black">
                                        <br>
                                        {{$blockBlue['titleText']}}
                                    </td>
                                </tr>


                                @foreach ($correlations as $correlation)
                                    <tr>
                                        <td>
                                            <br>
                                            <img src="{{$correlation->getStudyImages()->getGaugeImage()}}" alt="gauge image">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="headline-black">
                                            <a href="{{$correlation->getStudyLinks()->getStudyLinkDynamic()}}&existingUser=true"
                                               style="color:rgb(65, 64, 66); !important;"
                                               target="_blank">
                                                {{$correlation->getStudyText()->getPredictorExplanation()}}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <img src="{{$correlation->getStudyImages()->getCauseVariableImageUrl()}}" alt="cause image">
                                            <img src="{{$correlation->getStudyImages()->getEffectVariableImageUrl()}}" alt="effect image">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <center>
                                                <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="80%">
                                                    <tr>
                                                        <td style="color:rgb(65, 64, 66) !important;" >
                                                            @if($correlation->getStudyText()->getValuePredictingHighOutcomeExplanation())
                                                                <br>
                                                                {{$correlation->getStudyText()->getValuePredictingHighOutcomeExplanation()}}
                                                                <br> <br>
                                                                {{$correlation->getStudyText()->getValuePredictingLowOutcomeExplanation()}}
                                                                <br>
                                                            @else
                                                                <br>
                                                                {{$correlation->getStudyText()->getStudyAbstract()}}
                                                                <br>
                                                            @endif
                                                        </td>
                                                    </tr>

                                                </table>
                                            </center>
                                        </td>
                                    </tr>
                                    <br>
                                    <br>
                                    <div><!--[if mso]>
                                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word"
                                                     href="{{$correlation->getStudyLinks()->getStudyLinkStatic()}}" style="height:50px;v-text-anchor:middle;width:200px;" arcsize="8%" stroke="f" fillcolor="#178f8f">
                                            <w:anchorlock/>
                                            <center>
                                        <![endif]-->
                                        <a href="{{$correlation->getStudyLinks()->getStudyLinkStatic()}}"
                                           style="background-color:#178f8f;border-radius:4px;color:#ffffff;display:inline-block;font-family:Helvetica, Arial, sans-serif;font-size:16px;font-weight:bold;line-height:50px;text-align:center;text-decoration:none;width:200px;-webkit-text-size-adjust:none;">
                                            Go To Study
                                        </a>
                                        <!--[if mso]>
                                        </center>
                                        </v:roundrect>
                                        <![endif]-->
                                    </div>
                                    <br><br><br>
                                @endforeach
                                @include('email.block-blue-button')
                            </table>

                            @include('email.block-orange')
                            @include('email.block-brown')
                        </td>
                    </tr>
                </table>
            </center>
        </td>
    </tr>
</table>
@include('download-buttons')

</body>
</html>
