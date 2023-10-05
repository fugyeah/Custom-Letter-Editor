jQuery(document).ready(function($) {
    var isSubmitting = false;

    // Event listener for the Generate button
    $('#generate-message').click(function(e) {
        e.preventDefault();

        // Get the user's name and extra detail
        const senderName = $('#name').val();
        const extra = $('#extra-detail').val();

        // Get the random talking points
        const selectedPoints = getRandomTalkingPoints();

        // Make the AJAX request
        $.ajax({
            url: '/wp-admin/admin-ajax.php',
            type: 'POST',
            action: 'custom_letter_editor_handle_submission',
            data: {
                action: 'generate_message',
                senderName: senderName,
                selectedPoints: selectedPoints,
                extra: extra
            },
            success: function(response) {
                // Display the generated message in the textarea
                $('#generatedMessage').val(response.data);
            },
            error: function(error) {
                console.error("There was an error generating the message:", error);
            }
        });
    });

    // Function to get a random subset of the talking points
    function getRandomTalkingPoints() {
        const talkingPoints = customLetterEditorAdmin.talkingPoints || []; // Fetching from localized script

        const randomIndices = [];
        while (randomIndices.length < 2) {
            const randomIndex = Math.floor(Math.random() * talkingPoints.length);
            if (!randomIndices.includes(randomIndex)) {
                randomIndices.push(randomIndex);
            }
        }
        return randomIndices.map(index => talkingPoints[index]).join(' ');
    }

    $('#custom-letter-form').on('submit', function(event) {
        event.preventDefault();

        if (isSubmitting) {
            console.log('Form is currently being submitted. Please wait...');
            return;
        }

        var formData = $('#custom-letter-form').serialize();
        formData += '&action=custom_letter_editor_send_email&generated_letter=' + encodeURIComponent($('#generatedMessage').val());

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
        $('#generatedMessage').val('');
        $('#custom-letter-form')[0].reset();
        $('#confirm-send-email, #cancel-send-email').hide();
    });
});