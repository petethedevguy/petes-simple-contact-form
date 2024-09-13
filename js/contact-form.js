jQuery(document).ready(function($) {
    $('#scf_contact_form').on('submit', function(e) {
        e.preventDefault(); // Prevent the form from reloading the page

        var form = $(this);
        var errorMessage = $('#scf_error_message');
        var successMessage = $('#scf_success_message');

        errorMessage.text(''); // Clear previous error messages
        successMessage.hide(); // Hide the success message initially

        $.ajax({
            type: 'POST',
            url: scf_ajax_object.ajax_url, // Use localized AJAX URL
            data: form.serialize() + '&action=scf_form_submit&nonce=' + scf_ajax_object.nonce, // Append action and nonce
            dataType: 'json', // Expect JSON response
            success: function(response) {
                if (response.success) {
                    // On success, fade out the form and fade in the success message
                    form.fadeOut(500, function() {
                        successMessage.fadeIn(500);
                        successMessage.text(response.data); // Display the success message
                    });
                } else {
                    // Display error message below the submit button
                    errorMessage.text(response.data);
                }
            },
            error: function() {
                // Handle unexpected errors
                errorMessage.text('An unexpected error occurred. Please try again.');
            }
        });
    });
});
