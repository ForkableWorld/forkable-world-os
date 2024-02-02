function format(state) {
    if (!state.id) return state.text; // optgroup
    return '&nbsp;&nbsp;' + state.text;
}

$("#select2_sample4").select2({
    placeholder: "Select a Country",
    allowClear: true,
    formatResult: format,
    formatSelection: format,
    escapeMarkup: function(m) {
        return m;
    }
});

$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    $("#invoice_type").val($(e.target).attr("href"));
})

$("#purchase_confirm").on('hidden.bs.modal', function (e) {
    $("#invoice_type").val("");
})

//open the modal and set the needed variables
$(".purchase").on('click', function(e) {
    $("#plan_id").val($(this).data("plan-id"));
    $("#plan_name").val($(this).data("plan-name"));
    $("#plan_price").val($(this).data("plan-price"));
    $("#order > div").text($(this).data("plan-name") + " $" + $(this).data("plan-price"));
    $("#invoice_type").val("#individual");
    $("#purchase_confirm").modal("show");

    e.preventDefault();
});

$('#continueButton').on('click', function(e) {
    // Open Checkout with further options
    handler.open({
        name: 'Quantimodo Dev Console',
        description: $("#plan_name").val(),
        amount: $("#plan_price").val() * 100
    });
    e.preventDefault();
});

$(".downgradePlan").on('click', function(e) {
    $("#plan_id").val($(this).data("plan-id"));
    $("#subscribeForm").submit();

    return false;
});

// Close Checkout on page navigation
$(window).on('popstate', function() {
    handler.close();
});

