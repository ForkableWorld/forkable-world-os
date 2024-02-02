$(function() {
    $('.export-data li').on('click', function(event) {
        $(".export-data").removeClass('open');
        var output = $(this).data('output');
        $.get("/account/request-export-data/"+output, function(result) {
            if (result.success) {
                $('#status').removeClass('alert-info hide').addClass('alert-success').text(result.message);
            } else {
                $('#status').removeClass('alert-success hide').addClass('alert-info').text(result.message);
            }
        }, 'json');
        return false;
    });
});
