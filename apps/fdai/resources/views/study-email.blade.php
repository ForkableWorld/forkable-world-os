<?php /** @var \App\Studies\QMStudy $study */
$studyHtml = $study->getStudyHtml();
 ?>
@include('email-css-style-tag')
{!! $study->getTitleGaugesTagLineHeader(true, true) !!}
<h1 style='text-align: center;'>See attached study for more info!</h1>
<div style="text-align: center; margin: auto;">
    {!! $study->getStaticStudyButtonHtml() !!}
</div>
<div style="text-align: center; margin: auto;"> {!! \App\UI\ImageHelper::getHappyRobotHTML() !!}</div>
<h2 style='text-align: center; margin: auto;'>The more data you feed me, the smarter I'll get!</h2>";
@include('download-buttons')