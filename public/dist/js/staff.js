function saveStaff() {
    const form = document.getElementById("StaffForm");
    const formData = new FormData(form);
    const url = form.getAttribute('action'); 

    $.ajax({        
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url: url,
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        type: 'POST',
        dataType: 'json',
        success: function(response) {

            if (response.status === 1) {
                swal("success", "Գործողությունը կատարված է", true, true);
                $('#StaffForm')[0].reset();
                $('.text-danger').text('');
                $('#staffBtn').prop('disabled', true);
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


$(document).on('click', '.btn-edit-staff', function () {
    let id = $(this).data('id');
    if (id) {
        window.location.href = `/admin/staff/${id}/edit`;
    }
});


$(document).on('click', '.btn-delete-staff', function () {
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
                url: `/admin/staff/${id}/delete`,
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


$(function () {
  moment.locale('hy');

  const birthDate = $('#birth_date').val();
  const staffDate = $('#staff_date').val();

  $('#staffBirthDatePicker').datetimepicker({
    format: 'DD.MM.YYYY',
    locale: 'hy',
    showTodayButton: true,
    defaultDate: birthDate ? moment(birthDate, 'DD.MM.YYYY') : moment()
  });

  $('#staffDatePicker').datetimepicker({
    format: 'DD.MM.YYYY',
    locale: 'hy',
    showTodayButton: true,
    defaultDate: staffDate ? moment(staffDate, 'DD.MM.YYYY') : moment()
  });
});



