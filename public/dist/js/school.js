function saveSchool() {
    const form = document.getElementById("schoolForm");
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
                $('#schoolForm')[0].reset();
                // $('.text-danger').text('');
                showFieldErrors(form, {});
                $('#schoolFormBtn').prop('disabled', true);
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

$(document).on('submit', '#schoolForm', function(e) {
    e.preventDefault();
    saveSchool();
});

$(document).on('click', '.btn-edit-school', function () {
    let id = $(this).data('id');
    if (id) {
        window.location.href = `/admin/school/${id}/edit`;
    }
});


$(document).on('click', '.btn-delete-school', function () {
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
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: `/admin/school/${id}/delete`,
                cache: false,
                contentType: false,
                processData: false,
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    if (response && response.status === -2) {
                        swal("error", response.message);
                    } else if (response && response.status === 1) {
                       swal("success", response.message, true, true);
                        $(el).closest('tr').remove();
                    } else {
                        Swal.fire("error", "Չհաջողվեց ջնջել։", "error");
                    }
                },
                 error: function (xhr) {
                    Swal.fire(xhr.responseJSON.message, " ", "error");
                    console.error(xhr.responseJSON);
                }
            });
        }
    });
});

