$(document).ready(function() {
    $(".ionic-link").on("click", function(e){
        var $this = $(this);
        $("#iframe").attr("src", $this.attr("ionic-link"));

        $(".navigation .active").removeClass("active");
        $this.parent().addClass("active");
        return false;
    });
});

// $(document).ready(function() {
//     $('#iframe').on('load', function() {
//         $(this).css('height', 600);
//         $(this).contents().find('.buttons.buttons-left.header-item').remove();
//         // try after 2 seconds to find correct height
//         setTimeout(fixHeight, 2000);
//
//         // try after 5 seconds to be perfectly sure all the xhr content loaded
//         setTimeout(fixHeight, 5000);
//     });
// });
//
// function fixHeight() {
//     var height = 0;
//     var iframe = $('#iframe');
//     iframe.contents().find(".card").each(function(index, element) {
//         height += element.scrollHeight;
//     });
//     iframe.css('height', height + 100);
//     iframe.contents().find('.overflow-scroll').css('overflow-y', 'hidden');
// }