jQuery(document).ready(function($) {
    var isSubmitting = false;

    $('#custom-letter-form').on('submit', function(event) {
        event.preventDefault();

        if (isSubmitting) {
            console.log('Form is currently being submitted. Please wait...');
            return;
        }

        var form = $(this);
        var formData = form.serializeArray();
        var data = {
            action: 'custom_letter_editor_send_email',
            senderName: formData.find(f => f.name === 'username').value,
            senderEmail: formData.find(f => f.name === 'email').value,
            message: $('#message-response').text()
        };

        isSubmitting = true;

        $.ajax({
            type: 'POST',
            url: '/wp-admin/admin-ajax.php',
            data: data,
            success: function(response) {
                console.log('Email sent:', response);
                $('#custom-letter-form').html('<p>Email sent successfully!</p>');
            },
            error: function(xhr, status, error) {
                console.log('Email send error:', xhr.responseText);
            },
            complete: function() {
                isSubmitting = false;
            }
        });
    });
});