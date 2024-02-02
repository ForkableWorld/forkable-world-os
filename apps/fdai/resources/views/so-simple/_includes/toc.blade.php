
{{--
Styles are in public/css/toc.css
Make sure to add <link rel="stylesheet" href="{{ qm_asset('css/toc.css') }}"> to <head>
 --}}
<div id="toc-container" class="toc-container"
    style="text-align: left !important; padding-bottom: 30px;">
    <div class="text-lg" style="font-weight: 1000;">Contents</div>
    <div class="js-toc"></div>
</div>
<button onclick="window.scrollToContents()" id="scroll-to-contents-button" title="Go to contents">Contents</button>
<script>
    mybutton = document.getElementById("scroll-to-contents-button");
    // When the user scrolls down 20px from the top of the document, show the button
    window.onscroll = function scrollFunction() {
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            mybutton.style.display = "block";
        } else {
            mybutton.style.display = "none";
        }
    }
    // When the user clicks on the button, scroll to the top of the document
    function scrollToContents() {$('#toc-container')[0].scrollIntoView();}
</script>