$(document).on('click', '.btn-check-other-offers', function () {
    const id = $(this).data('id');
    if (id) {
        window.location.href = `/admin/otherOffers/check/${id}`;
    }
});

$(document).on('click', '.btn-edit-other-offers', function () {
    const id = $(this).data('id');
    if (id) {
        window.location.href = `/admin/otherOffers/${id}/edit/`;
    }
});

$(document).on('click', '.btn-delete-other-offers', function () {
    const id = $(this).data('id');
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
                url: `/admin/otherOffers/delete/${id}`,
                cache: false,
                contentType: false,
                processData: false,
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    if (response && response.status === 2) {                                     
                      showInfo("info", "", response.message, () => {
                            location.reload();
                        });                     
                    } else if (response && response.status === 1) {
                       swal("success", response.message, true, true);
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