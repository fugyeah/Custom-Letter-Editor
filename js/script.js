jQuery(document).ready(function($) {
    var isSubmitting = false;

    $('#custom-letter-form').on('submit', function(event) {
        event.preventDefault();

        if (isSubmitting) {
            console.log('Form is currently being submitted. Please wait...');
            return;
        }

        var form = $(this);
        var formData = form.serialize();

        isSubmitting = true;

        generateMessageViaWP(function(generatedMessage) {
            $('#message-response').html('<p>' + generatedMessage + '</p>');
            $('#confirm-send-email, #cancel-send-email').show();
        });
    });

    $('#confirm-send-email').click(function() {
        var formData = $('#custom-letter-form').serialize();
        formData += '&action=custom_letter_editor_send_email&generated_letter=' + encodeURIComponent($('#message-response p').text());

        $.ajax({
            type: 'POST',
            url: customLetterEditorAjax.ajaxUrl,
            data: formData,
            success: function(response) {
                console.log('Email sent:', response);
                $('#custom-letter-form').html('<p>Email sent successfully!</p>');
                $('#confirm-send-email, #cancel-send-email').hide();
            },
            error: function(xhr, status, error) {
                console.log('Email send error:', xhr.responseText);
            },
            complete: function() {
                isSubmitting = false;
            }
        });
    });

    $('#cancel-send-email').click(function() {
        $('#message-response').empty();
        $('#custom-letter-form')[0].reset();
        $('#confirm-send-email, #cancel-send-email').hide();
    });

    function generateMessageViaWP(callback) {
        var formData = $('#custom-letter-form').serializeArray();
        var data = {
            action: "generate_message",
            username: formData.find(f => f.name === 'username').value,
            email: formData.find(f => f.name === 'email').value,
            address: formData.find(f => f.name === 'address').value,
            extra: formData.find(f => f.name === 'extra').value
        };
    
        $.ajax({
            url: "/custom-letter-editor-ajax.php",
            type: "POST",
            data: data,
            success: function(response) {
                // Handle the response here
                if (response.choices && response.choices[0] && response.choices[0].text) {
                    let generatedMessage = response.choices[0].text.trim();
                    callback(generatedMessage);
                } else {
                    console.error("Error generating message:", response);
                    isSubmitting = false;
                }
            },
            error: function(error) {
                console.error("AJAX error:", error);
                isSubmitting = false;
            }
        });
    }
});
