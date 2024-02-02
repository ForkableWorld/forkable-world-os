  $(document).ready(function() {
        var $validator = $("#commentForm").validate({
            rules: {
                emailfield: {
                    required: true,
                    email: true,
                    minlength: 3
                },
                namefield: {
                    required: true,
                    minlength: 3
                },
                passwordfield: {
                    required: true,
                    minlength: 3
                },
                fnamefield: {
                    required: true,
                    minlength: 3
                },
                lnamefield: {
                    required: true,
                    minlength: 3
                },
                cityfield: {
                    required: true,
                    minlength: 3
                },
                linefield: {
                    required: true,
                    minlength: 3
                },
                statefield: {
                    required: true,
                    minlength: 3
                },
                phonefield: {
                    required: true,
                    number: true,
                    minlength: 10
                },
                phone1field: {
                    required: true,
                    number: true,
                    minlength: 10
                },
                postcodefield: {
                    required: true,
                    minlength: 3
                }
            }
        });
        $('#rootwizard').bootstrapWizard({
            'tabClass': 'nav nav-pills',
            'onNext': function(tab, navigation, index) {
                var $valid = $("#commentForm").valid();
                if (!$valid) {
                    $validator.focusInvalid();
                    return false;
                }
            }
        });
        window.prettyPrint && prettyPrint()
    });
    $(document).ready(function() {
        $('#rootwizard').bootstrapWizard();
        window.prettyPrint && prettyPrint()
    });
    $(document).ready(function() {
        $('#rootwizard1').bootstrapWizard({
            'nextSelector': '.button-next',
            'previousSelector': '.button-previous',
            'firstSelector': '.button-first',
            'lastSelector': '.button-last'
        });
    });