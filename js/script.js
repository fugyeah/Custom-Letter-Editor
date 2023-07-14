jQuery(document).ready(function($) {
    var isSubmitting = false; // Flag to check if form is currently being submitted

    // AJAX form submission
    $('#custom-letter-form').on('submit', function(event) {
        event.preventDefault();

        // If form is currently being submitted, prevent further submissions
        if (isSubmitting) {
            console.log('Form is currently being submitted. Please wait...');
            return;
        }

        var form = $(this);
        var formData = form.serialize();
formData += '&custom_letter_editor_submission_nonce=' + $('#custom_letter_editor_nonce').val();

        // Set flag to true to indicate form is being submitted
        isSubmitting = true;

        $.ajax({
            type: 'POST',
            url: customLetterEditorAjax.ajaxUrl,
            data: formData,
            success: function(response) {
                // Log the response to the console
                console.log('AJAX response:', response);

                // Handle the success response
                if (response.success) {
                    // Display the generated letter
                    var generatedLetter = response.data.generated_letter;

                    // Insert the generated letter into the widget
                    $('#generated-letter-widget').html('<h2>Generated Letter</h2><p>' + generatedLetter + '</p>');

                    // Ask for confirmation
                    if (window.confirm('Do you want to send this letter?')) {
                        // User clicked OK, send the email
                        formData += '&action=custom_letter_editor_send_email&generated_letter=' + encodeURIComponent(generatedLetter);
                        formData += '&custom_letter_editor_email_nonce=' + $('#custom_letter_editor_email_nonce').val();

                        $.ajax({
                            type: 'POST',
                            url: customLetterEditorAjax.ajaxUrl,
                            data: formData,
                            success: function(response) {
                                console.log('Email sent:', response);
                                // Replace the form content with a success message
                                $('#custom-letter-form').html('<p>Email sent successfully!</p>');
                            },
                            error: function(xhr, status, error) {
                                console.log('Email send error:', xhr.responseText);
                            },
                        });
                    } else {
                            location.reload();
                    }
                } else {
                    // Handle error
                    alert('An error occurred: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                // Handle the error response
                console.log('AJAX error:', xhr.responseText);
            },
            complete: function() {
                // Set flag to false to allow further submissions
                isSubmitting = false;
            }
        });
    });
});
