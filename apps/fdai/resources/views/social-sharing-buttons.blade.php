@php
    $iconBaseUrl = \App\UI\ImageHelper::BASE_URL . "sharing/color-round";
    $liStyle = $liStyle ?? "display: inline;";
@endphp
<style type="text/css">
    ul.share-buttons{
        list-style: none;
        padding: 0;
        margin: 4rem;
    }
    ul.share-buttons li{
        display: inline;
    }
    ul.share-buttons .sr-only{
        position: absolute;
        clip: rect(1px 1px 1px 1px);
        clip: rect(1px, 1px, 1px, 1px);
        padding: 0;
        border: 0;
        height: 1px;
        width: 1px;
        overflow: hidden;
    }
</style>
<div class="mt-6 p-8">
    <ul class="share-buttons" style='text-align: center;'>
        <li style='{{ $liStyle }}'>
            <a
                href="{!!\App\Buttons\Sharing\FacebookSharingButton::getFacebookShareLink($url)!!}"
                title="Share on Facebook"
                target="_blank"
            ><img style="display: inline-block" alt="Share on Facebook" src="{{ $iconBaseUrl }}/Facebook.png"/></a>
        </li>
        <li style='{{ $liStyle }}'>
            <a
                href="{!!\App\Buttons\Sharing\TwitterSharingButton::getTwitterShareLink($url, $shortTitle)!!}"
                target="_blank"
                title="Tweet"
            ><img style="display: inline-block" alt="Tweet" src="{{ $iconBaseUrl }}/Twitter.png"/></a>
        </li>
        <li style='{{ $liStyle }}'>
            <a
                href="https://www.tumblr.com/share?v=3&u={{ $url}}&quote={{ $shortTitle}}&s="
                target="_blank"
                title="Post to Tumblr"
            ><img style="display: inline-block" alt="Post to Tumblr" src="{{ $iconBaseUrl }}/Tumblr.png"/></a>
        </li>
        <li style='{{ $liStyle }}'>
            <a
                href="https://pinterest.com/pin/create/button/?url={{ $url}}&media={{ $imagePreview}}&description={!!urlencode($briefDescription)!!}"
                target="_blank"
                title="Pin it"
            ><img style="display: inline-block" alt="Pin it" src="{{ $iconBaseUrl }}/Pinterest.png"/></a>
        </li>
        <li style='{{ $liStyle }}'>
            <a href="{!!\App\Buttons\Sharing\RedditSharingButton::getRedditUrlLink($shortTitle, $url)!!}"
               target="_blank" 
               title="Submit to Reddit">
                <img style="display: inline-block" alt="Submit to Reddit"
                     src="{{ $iconBaseUrl }}/Reddit.png"/>
            </a>
        </li>
        <li style='{{ $liStyle }}'>
            <a
                href="{!!\App\Buttons\Sharing\LinkedInButton::getLinkedInShareLink($url, $shortTitle, $briefDescription)!!}"
                target="_blank"
                title="Share on LinkedIn"
            ><img style="display: inline-block" alt="Share on LinkedIn" src="{{ $iconBaseUrl }}/LinkedIn.png"/></a>
        </li>
        <li style='{{ $liStyle }}'>
            <a href="{{ $emailUrl }}" target="_blank" title="Send email"><img
                    style="display: inline-block"
                    alt="Send email"
                    src="{{ $iconBaseUrl }}/Email.png"
                /></a>
        </li>
    </ul>
</div>
