
function Validation() {

        var Name = document.frmOnline.txtName;

        var lastname = document.frmOnline.txtlastname;

        var Email = document.frmOnline.txtEmail;

        var Address1 = document.frmOnline.txtAddress1;
        var Address2 = document.frmOnline.txtAddress2;
        var Phone = document.frmOnline.txtPhone;
        var Conditions = document.frmOnline.e1;
        var chkConditions = document.frmOnline.chkConditions;

        if (Name.value == "") {
            alert("Enter your Name");
            Name.focus();
            return false;

        }

        if (Name.value != "") {
            var ck_name = /^[A-Za-z ]{3,50}$/;
            if (!ck_name.test(Name.value)) {
                alert("You can not enter Numeric values (or) your name should be 3 - 20 characters...");
                Name.focus();
                return false;
            }
        }
        //lastname Validation
        if (lastname.value == "") {
            alert("Enter your last name");
            lastname.focus();
            return false;
        }
        if (lastname.value != "") {
            var ck_name = /^[A-Za-z ]{3,20}$/;
            if (!ck_name.test(lastname.value)) {
                alert("You can not enter Numeric values (or) your name should be 3 - 20 characters...");
                lastname.focus();
                return false;
            }
        }
        //lastname Validation Completed

        //email validation
        if (Email.value == "") {
            alert("Enter your Email_Id");
            txtEmail.focus();
            return false;
        }


        var x = document.forms["frmOnline"]["txtEmail"].value;
        var atpos = x.indexOf("@");
        var dotpos = x.lastIndexOf(".");
        if (atpos < 1 || dotpos < atpos + 2 || dotpos + 2 >= x.length) {
            alert("Not a valid e-mail address");
            txtEmail.focus();
            return false;
        }
        //address validation

        if (Address1.value == "") {
            alert("Enter your address line1");
            txtAddress1.focus();
            return false;
        }
        //address validation

        if (Address2.value == "") {
            alert("Enter your address line1");
            txtAddress2.focus();
            return false;
        }
        if (Conditions.value == "") {

            alert("Please Select Any One");
            Conditions.focus();
            return false;
        }
        //mobile Validation
        if (Phone.value == "") {
            alert("Please Enter your Phone number");
            txtPhone.focus();
            return false;
        }
        if (Phone.value != "") {
            var reg = /^[987][0-9]{9}$/;
            if (reg.test(Phone.value) == false) {
                alert("Plase Enter Correct Phone Number");
                txtPhone.focus();
                return false;
            }
        }
        //Mobile Validation Completed
        //Condtion validtion
        if (chkConditions.checked == false) {
            alert("Please Check the Terms and Conditions");
            chkConditions.focus();
            return false;
        }
        return true;
    }
  
    $(document).ready(function() {
        $('#form-validation').bootstrapValidator({
            fields: {
                firstName: {
                    validators: {
                        notEmpty: {
                            message: 'The first name is required and cannot be empty'
                        }
                    }
                },
                message: {
                    validators: {
                        notEmpty: {
                            message: 'The field is required and cannot be empty'
                        }
                    }
                },
                password: {
                    validators: {

                        notEmpty: {
                            message: 'Please provide a password'
                        }
                    }
                },
                 confirmpassword: {
                validators: {
                    notEmpty: {
                        message: 'The confirm password is required and can\'t be empty'
                    },
                    identical: {
                        field: 'password',
                        message: 'Please enter the same password as above'
                    }
                }
            },
                email: {
                    validators: {
                        notEmpty: {
                            message: 'The email address is required'
                        },
                        emailAddress: {
                            message: 'The input is not a valid email address'
                        }
                    }
                },
                 skill: {
                    validators: {
                        notEmpty: {
                            message: 'The field is required and cannot be empty'
                        }
                    }
                },
                 url: {
                     validators: {
                        notEmpty: {
                            message: 'The web url is required'
                        },
                    uri: {
                        allowLocal: true,
                        message: 'The input is not a valid URL'
                    }
                }
                },
                number: {
                   validators: {
                      notEmpty: {
                            message: 'The phone number is required'
                        },
                    phone: {
                        message: 'The input is not a valid phone number',
                        country: 'GB'
                    }
                }
                },
                 terms: {
                validators: {
                    notEmpty: {
                        message: 'You have to accept the terms and policies'
                    }
                }
            },
                gender: {
                    validators: {
                        notEmpty: {
                            message: 'The gender is required'
                        }
                    }
                }
            },
            submitHandler: function(validator, form, submitButton) {
                var fullName = [validator.getFieldElements('firstName').val(),
                    validator.getFieldElements('lastName').val()
                ].join(' ');
                $('#helloModal')
                    .find('.modal-title').html('Hello ' + fullName).end()
                    .modal();
            }
        });
    });