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