// prepare the form when the DOM is ready
$(document).ready(function() {
    $( ".variable-search" ).autocomplete({
        source: "/account/autocomplete/public-variable",
        minLength: 3,
        select: function(event, ui) {
            this.value = "";
            // id can be outcome or predictor
            $("#"+this.id+"-id").val(ui.item.id);
            $("#"+this.id).val(ui.item.value);

            var outcomeVal = $("#outcome").val();
            var predictorVal = $("#predictor").val();

            if (outcomeVal && predictorVal) {
                $("#name").val("Study on the relationship between " + predictorVal + " and " + outcomeVal);
            }

            return false;
        },
        search: function(event, ui) {
            $(this).parent().find(".spinner").show();
        },
        response: function(event, ui) {
            $(this).parent().find(".spinner").hide();
        }
    });

    // bind to the form's submit event
    $('#collaborator-form').submit(function() {
        $('.message').addClass('hide');
        // inside event callbacks 'this' is the DOM element so we first
        // wrap it in a jQuery object and then invoke ajaxSubmit
        $(this).ajaxSubmit({
            dataType:  'json',
            clearForm: true,
            success: function(result) {
                if (result.success) {
                    var lastRow = $('.collaborators tr').last();
                    var newRow = addRow(lastRow, result);
                    lastRow.after('<tr>' + newRow.html() + '</tr>');
                } else {
                    $('.message > div').text(result.message);
                    $('.message').removeClass('hide');
                }
            }
        });

        // !!! Important !!!
        // always return false to prevent standard browser submit and page navigation
        return false;
    });

    $('.collaborators').on('click', '.delete-collaborator', function(event) {
        var id = $(this).find('i').attr('data-id');
        $('#delete').attr('data-id', id);
        $('#confirm-delete').modal('show');
    });

    $('#delete').on('click', function(event) {
        var id = $(this).attr('data-id');
        $(this).attr('data-id', '');

        $.post('/account/apps/delete-collaborator', {id: id}, function(result) {
            if (result.success) {
                $('i[data-id="'+id+'"]').parent().parent().remove();
            }
            $('#confirm-delete').modal('hide');
        });

        return false;
    });

    $("#get-token").on('click', function(){
        var $this = $(this);
        var appId = $this.data("app-id");
        $this.button('loading');
        $.get("/account/apps/"+ appId +"/token", function( response ) {
            $this.button('reset');
            if (response.success == true) {
                $this.replaceWith("<div>"+response.access_token+"</div>")
            }
        }, 'json').fail(function() {
            $this.button('reset');
        });

        return false;
    });

    $('.export-measurements').on('click', 'li', function(event) {
        var $this = $(this);
        var $container = $(event.delegateTarget);
        var $button = $container.find("button");
        $container.removeClass('open');

        var clientId = $container.data("client-id");
        var appId = $container.data("app-id");
        var output = $this.data("output");
        $button.button('loading');

        $.get("/account/apps/" + appId + "/export/" + clientId + "/" + output, function( response ) {
            $button.button('reset');
            alertMessage(response.success, response.message);
        }, 'json').fail(function() {
            $button.button('reset');
        });

        return false;
    });
});

function addRow(lastRow, result) {
    var newRow = lastRow.clone();
    newRow.find('td:eq(0) img').attr('src', result.avatar);
    newRow.find('td:eq(1)').text(result.email);
    newRow.find('td:eq(2)').text('Collaborator');
    newRow.find('td:eq(3)').html('<i data-id="'+ result.collaborator +'" class="fa fa-lg fa-times"></i>');

    return newRow;
}
