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
                console.log(response);
            },
            error: function(xhr, status, error) {
                // Handle the error response
                console.log(xhr.responseText);
            }
        });
    });
});

