function saveUser() {
    const form = document.getElementById("UserForm");
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
        success: function(response) {

            if (response.status === 1) {
                swal("success", "Գործողությունը կատարված է", true, true);
                $('#UserForm')[0].reset();
                // $('.text-danger').text('');
                $('#userFormBtn').props('disabled', true);
                showFieldErrors(form, {}); 
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


$(document).on('click', '.btn-edit-user', function () {
    let id = $(this).data('id');
    if (id) {
        window.location.href = `/admin/user/${id}/edit`;
    }
});

$(document).on('click', '.btn-delete-user', function () {
    let id = $(this).data('id');
    let el = this;

    Swal.fire({
        title: "Դուք համոզված եք՞",
        showDenyButton: true,
        showCancelButton: true,
        confirmButtonText: "Այո",
        showCancelButton: false,
        denyButtonText: `Ոչ`
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                url: `/admin/group/${id}/delete`,
                cache: false,
                contentType: false,
                processData: false,
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    if (response && response.status === -2) {
                        swal("error", response.message);
                    } else if (response && response.status === 1) {
                       swal("success", "Գործողությունը կատարված է", true, true);
                        $(el).closest('tr').remove();
                    } else {
                        Swal.fire("Սխալ", "Չհաջողվեց ջնջել։", "error");
                    }
                },
                 error: function (xhr) {
                    Swal.fire("Սխալ", "Խնդրում ենք կրկին փորձել։", "error");
                    console.error(xhr.responseJSON);
                }
            });
        }
    });
});


$(document).ready(function() {
    $('#role_name').on('change', function() {
        if ($(this).val() === 'super-accountant') {
           $('#school_id').prop('disabled', true);
        } else {
            $('#school_id').prop('disabled', false);
        }
    });
});
