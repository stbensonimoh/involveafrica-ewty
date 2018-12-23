(function () {
    'use strict';
    window.addEventListener('load', function () {
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        let forms = document.getElementsByClassName('needs-validation');
        // Loop over them and prevent submission
        let validation = Array.prototype.filter.call(forms, function (form) {
            form.addEventListener('submit', function (event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });


    }, false);
})();
$(document).ready(function () {
    // DOM is fully loaded

    // Capitalize the first letter of First Name
    $('#firstName').on('change', function (e) {
        let $this = $(this),
            val = $this.val();
        regex = /\b[a-z]/g;

        val = val.charAt(0).toUpperCase() + val.substr(1);
    });

    // Capitalize the first letter of Last Name
    $('#lastName').on('change', function (e) {
        let $this = $(this),
            val = $this.val();
        regex = /\b[a-z]/g;

        val = val.charAt(0).toUpperCase() + val.substr(1);
    });

    // change the email to lowercase
    $('#email').on('change', function (e) {
        let $this = $(this),
            val = $this.val();
        val = val.toLowerCase();
    });

    $('form').submit(function (event) {
        event.preventDefault();
        if (form.checkValidity() === true) {
            // Make the submit button load
            $('button').removeClass('btn-danger');
            $('button').toggleClass('btn-primary');
            $('button').html('Loading <span class="spinner"></span><i class="fa fa-spinner fa-spin"></i></span>');

            // Put the Form Data into letiables
            let firstName = $.trim(document.getElementById('firstName').value);
            let lastName = $.trim(document.getElementById('lastName').value);
            let email = $.trim(document.getElementById('email').value);
            let phone = $.trim(document.getElementById('phone').value);
            let gender = document.querySelector('input[name="gender"]:checked').value;
            let state = document.getElementById('state').value;

            let dataString = `firstName=${firstName}&lastName=${lastName}&email=${email}&phone=${phone}&gender=${gender}&state=${state}`;

            // Check to See if the user has already done the survey
            $.ajax({
                type: 'POST',
                url: 'submit.php',
                data: dataString,
                success: function (result) {
                    if (result == 'user_exists') {
                        swal("Already Registered", "You have already registered for the event.", "error");
                        setTimeout(function () {
                            window.location = '//involveafri.org';
                        }, 3000);
                    } else if (result == 'success') {
                        swal("Submission Successful", "Your survey was successfully submitted.", "success");
                        setTimeout(function () {
                            window.location = '//involveafri.org';
                        });
                    }
                }
            });

        }
    });

});