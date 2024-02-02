<table align="center" cellpadding="0" cellspacing="0" class="force-full-width" height="100%" >
    <tr>
        <td align="center" valign="top" bgcolor="#ffffff"  width="100%">
            <center>
                <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" width="600" class="w320">
                    <tr>
                        <td align="center" valign="top">
                            <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" class="force-full-width" bgcolor="#4dbfbf">
                                <tr>
                                    <td>
                                        <br>
                                        <img src="{{$correlation->getStudyImages()->getGaugeImage()}}" alt="gauge image">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="headline">
                                        <a href="{{$correlation->getStudyLinks()->getStudyLinkDynamic()}}"
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
                                                    <td style="color:#187272;">
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
                                    <tr>
                                        <td style="color:#bbbbbb; font-size:18px;">
                                            <br>
                                            <a href="{{$correlation->getStudyLinks()->getStudyLinkStatic()}}" target="_blank"> Go to Study </a> |
                                            <a href="{{$correlation->getStudyLinks()->getStudyLinkFacebook()}}" target="_blank"> Share on Facebook </a> |
                                            <a href="{{$correlation->getStudyLinks()->getStudyLinkTwitter()}}" target="_blank"> Share on Twitter </a>
                                            <br><br><br>
                                        </td>
                                    </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </center>
        </td>
    </tr>
</table>