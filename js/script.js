// Add your JavaScript code here

jQuery(document).ready(function($) {
    // AJAX form submission
    $('form').on('submit', function(event) {
        event.preventDefault();

        var form = $(this);
        var formData = form.serialize();

        $.ajax({
            type: 'POST',
            url: customLetterEditorAjax.ajaxUrl,
            data: formData,
            success: function(response) {
                // Handle the success response
                if(response.success) {
                    // Display the generated letter
                    var generatedLetter = response.data.generated_letter;
                    alert('Your letter has been generated and sent: ' + generatedLetter);
                    // Disable the form to prevent multiple submissions
                    form.find(':input').prop('disabled', true);
                } else {
                    // Display the error message
                    alert(response.data.message);
                }
            },
            error: function(xhr, status, error) {
                // Handle the error response
                alert('An error occurred: ' + xhr.responseText);
            }
        });
    });
});
