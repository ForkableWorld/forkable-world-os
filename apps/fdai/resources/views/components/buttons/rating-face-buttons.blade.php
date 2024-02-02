@php
    use App\UI\ImageHelper;
    $baseUrl = ImageHelper::BASE_URL . "rating/100/face_rating_button_100";
@endphp
<a class="mcnButton " title="Open Reminder Inbox"
   href="{{ \App\Utils\IonicHelper::getIntroUrl(['existingUser' => true]) }}"
   target="_blank"
   style="text-decoration: none;">
    <div id="sectionRate" class="rating-section" style='margin: auto;'>
        <img src="{{ $baseUrl }}_depressed.png"
             style='width: 18%; display: inline-block;'
             id="buttonMoodDepressed"><span>&nbsp;</span>
        <img src="{{ $baseUrl }}_sad.png"
             style='width: 18%; display: inline-block;'
             id="buttonMoodSad"><span>&nbsp;</span>
        <img src="{{ $baseUrl }}_ok.png"
             style='width: 18%; display: inline-block;'
             id="buttonMoodOk"><span>&nbsp;</span>
        <img src="{{ $baseUrl }}_happy.png"
             style='width: 18%; display: inline-block;'
             id="buttonMoodHappy"><span>&nbsp;</span>
        <img src="{{ $baseUrl }}_ecstatic.png"
             style='width: 18%; display: inline-block;'
             id="buttonMoodEcstatic">
    </div>
</a>