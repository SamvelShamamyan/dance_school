function saveRoom() {
    const form = document.getElementById("roomForm");
    const formData = new FormData(form);
    const url = form.getAttribute('action'); 
    $.ajax({        
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url:  url,
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        type: 'POST',
        dataType: 'json',
        success: async function(response) {
            if (response.status === 1) {
                await swal("success", "Գործողությունը կատարված է", true, true);
                $('#roomForm')[0].reset();
                // $('.text-danger').text(''); 
                showFieldErrors(form, {});
                $('#roomFormBtn').prop('disabled', true);
                window.location.href = response.redirect;
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                $.each(errors, function(field, messages) {
                    $(`.error_${field}`).text(messages[0])
                });
            } else {
                swal("error", "Something went wrong!", true, true)
            }
        }
    });
}

$(document).on('submit', '#roomForm', function(e) {
    e.preventDefault();
    saveRoom();
});

