$(function() {
    // autocomplete for user variables
    $( ".variable-search" ).autocomplete({
        source: "/account/autocomplete/user-variable",
        minLength: 3,
        select: function(event, ui) {
            this.value = "";
            // id can be outcome or predictor
            $("#"+this.id+"-id").val(ui.item.id);
            $("#"+this.id+"-text").text(ui.item.value);

            return false;
        }
    });
});
