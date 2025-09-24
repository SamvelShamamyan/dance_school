

function checkAttendancesSave() {
    const form = document.getElementById("checkAttendancesForm");
    const formData = new FormData(form);
    // const url = form.getAttribute('action'); 
    $.ajax({        
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url:  `/admin/studentAttendances/add`,
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        type: 'POST',
        dataType: 'json',
        success: async function(response) {
            if (response.status === 1) {
                await swal("success", "Գործողությունը կատարված է", true, true);
                $('#checkAttendancesForm')[0].reset();
                $('.text-danger').text('');
                $('#checkAttendancesBtn').prop('disabled', true);
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



$(function () {
  moment.locale('hy');
  const checkAttendances = $('#check_attendances').val();
  $('#checkAttendancesDatePicker').datetimepicker({
    format: 'DD.MM.YYYY',
    locale: 'hy',
    showTodayButton: true,
    defaultDate: checkAttendances ? moment(checkAttendances, 'DD.MM.YYYY') : moment()
  });
    $('#checkAttendancesDatePicker').on('show.datetimepicker', function () {
        $('.bootstrap-datetimepicker-widget').css({ 'width': '360px', 'min-width': '360px' });
    });
});
