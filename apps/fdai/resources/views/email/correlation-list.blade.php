<table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="100%" bgcolor="#f5774e">
    @foreach ($correlations as $correlation)
        <tr>
            <td class="headline">
                <a href="{{$correlation->getStudyLinks()->getStudyLinkDynamic()}}{{$urlParameters}}"
                   target="_blank">
                    {{$correlation->getStudyText()->getPredictorExplanation()}}
                </a>
            </td>
        </tr>
        <tr>
            <td>
                <center>
                    <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="80%">
                        <tr>
                            <td style="color:#187272;">

                                {{$correlation->getStudyText()->getValuePredictingHighOutcomeExplanation()}}
                                {{$correlation->getStudyText()->getValuePredictingLowOutcomeExplanation()}}
                                @if(!$correlation->getStudyText()->getValuePredictingLowOutcomeExplanation())
                                    {{$correlation->getStudyText()->getStudyAbstract()}}
                                @endif
                                <br>
                                {{--@include('email.button-study')--}}
                            </td>
                        </tr>
                    </table>
                </center>
            </td>
        </tr>
    @endforeach
    <tr>
        <td style="background-color:#f5774e;" class="headline">
            <br>
            {{$correlation->getStudyText()->getPredictorExplanation()}}
        </td>
    </tr>
    <tr>
        <td>

            <center>
                <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="60%">
                    <tr>
                        <td style="color:#933f24;">
                            <br>
                            {{$correlation->getStudyText()->getValuePredictingHighOutcomeExplanation()}}
                            {{$correlation->getStudyText()->getValuePredictingLowOutcomeExplanation()}}
                            <br><br>
                        </td>
                    </tr>
                </table>
            </center>

        </td>
    </tr>
    <tr>
        <td>
            <div><!--[if mso]>
                <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word"
                             href="{{$correlation->getStudyLinks()->getStudyLinkStatic()}}{{$urlParameters}}"
                             style="height:50px;v-text-anchor:middle;width:200px;"
                             arcsize="8%"
                             stroke="f"
                             fillcolor="#ac4d2f">
                    <w:anchorlock/>
                    <center>
                <![endif]-->
                <a href="{{$correlation->getStudyLinks()->getStudyLinkStatic()}}{{$urlParameters}}"
                   style="background-color:#ac4d2f;border-radius:4px;color:#ffffff;display:inline-block;font-family: Helvetica, Arial, sans-serif;font-size:16px;font-weight:bold;line-height:50px;text-align:center;text-decoration:none;width:200px;-webkit-text-size-adjust:none;">
                    Go To Study</a>
                <!--[if mso]>
                </center>
                </v:roundrect>
                <![endif]--></div>
            <br>
            <br>
        </td>
    </tr>
</table>