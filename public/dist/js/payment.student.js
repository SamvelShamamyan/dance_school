function savePayent() {
    const form = document.getElementById("singlePaymentForm");
    const formData = new FormData(form);
    const $tbl = $('#studentPaymentTbl');
    const SCHOOL_ID = $tbl.data('school-id');
    formData.append('school_id', SCHOOL_ID)
    $.ajax({        
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url: `/admin/payment/add`,
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        type: 'POST',
        dataType: 'json',
        success:async function(response) {
            if (response.status === 1) {     
              await Swal.fire({
                  title: "Գործողությունը կատարված է",
                  text: "",
                  icon: "success",
                  confirmButtonText: "Տպել կտրոնը",
                  cancelButtonText: 'Փակել',
                  showCancelButton: true,
                }).then((result) => {
                  if (result.isConfirmed && response.id) {
                    const url = `/admin/payment/receipt/${response.id}?print=1`;
                    window.open(url, '_blank', 'noopener');
                  }
                  setTimeout(() => {
                    location.reload();
                  }, 500);
                }); 

                $('#singlePaymentForm')[0].reset();
                $('.text-danger').text('');
                $('#singlePaymentBtn').prop('disabled', true);
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                $('.text-danger').text('');
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

function updatePayment() {
    const form = document.getElementById("editPaymentForm");
    const formData = new FormData(form);
    const $tbl = $('#studentPaymentTbl');
    const SCHOOL_ID = $tbl.data('school-id');
    const id = $('#payment_id').val();
    formData.append('school_id', SCHOOL_ID);
    formData.append('id', id);
    $.ajax({        
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url: `/admin/payment/update/${id}`,
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        type: 'POST',
        dataType: 'json',
        success: function(response) {

            if (response.status === 1) {
                swal("success", "Գործողությունը կատարված է", true, true);
                $('#editPaymentForm')[0].reset();
                $('.text-danger').text('');
                $('#editPaymentBtn').prop('disabled', true);
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                $('.text-danger').text('');
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
  const $tbl = $('#studentPaymentTbl');
  if (!$tbl.length) return;

  const STUDENT_ID = $tbl.data('student-id');
  const SCHOOL_ID = $tbl.data('school-id');
  const studentPaymentUrl = `/admin/payment/student/filters/${STUDENT_ID}` + (SCHOOL_ID ? `?school_id=${SCHOOL_ID}` : '');

  moment.locale('hy');

  if ($('#paymentDatePicker').length){
    $('#paymentDatePicker').datetimepicker({
      format:'DD.MM.YYYY', locale:'hy', showTodayButton:true, defaultDate: moment()
    });
  }
  if ($('#paymentEditDatePicker').length){
    $('#paymentEditDatePicker').datetimepicker({
      format:'DD.MM.YYYY', locale:'hy', showTodayButton:true, defaultDate: moment()
    });
  }
  $('#paymentDatePicker, #paymentEditDatePicker').on('show.datetimepicker', function () {
    $('.bootstrap-datetimepicker-widget').css({ width:'360px', 'min-width':'360px' });
  });

  $.ajax({
    url: studentPaymentUrl,
    type: 'GET',
    dataType: 'json',
    success: function (response) {

      const $year = $('#year').empty();
      const years = (response.years && response.years.length) ? response.years : [new Date().getFullYear()];
      years.forEach(y => $year.append(new Option(y, y)));
      $year.val(years[0]); 

      const methodMap = { cash:'Կանխիկ',  card:'Անկանխիկ', online:'Առցանց' };
      const $method = $('#method').empty().append(new Option('Բոլորը',''));
      (response.methods || []).forEach(s => $method.append(new Option(methodMap[s] || s, s)));

      const statusMap = { paid:'Վճարված', pending:'Սպասման մեջ', refunded:'Վերադարձված', failed:'Սխալ' };
      const $status = $('#status').empty().append(new Option('Բոլորը',''));
      (response.statuses || []).forEach(s => $status.append(new Option(statusMap[s] || s, s)));

      $(document).trigger('payment:filtersLoaded');
    }
  });

  $('#btnRefresh, #year, #status, #method').on('change click', function(){
    if ($.fn.DataTable.isDataTable('#studentPaymentTbl')) {
      $('#studentPaymentTbl').DataTable().ajax.reload();
    }
  });
});


$(document).on('click', '.act-edit', function(){
  $('#editPaymentModal').find('#payment_id').val($(this).data('id'));
  $('#editPaymentModal').find('#paid_at').val($(this).data('paid'));
  $('#editPaymentModal').find('#amount').val($(this).data('amount'));
  $('#editPaymentModal').find('#method').val($(this).data('method'));
  $('#editPaymentModal').find('#status').val($(this).data('status'));
  $('#editPaymentModal').find('#comment').val($(this).data('comment'));
  const $tbl = $('#studentPaymentTbl');
  const SCHOOL_ID = $tbl.data('school-id');
  $('#editPaymentModal').find('#school_id').val(SCHOOL_ID);
  $('#editPaymentModal').modal('show');

  setTimeout(function(){
    const dp = $('#paymentEditDatePicker').data('DateTimePicker');
    if (dp) dp.date(moment($('#editPaymentModal').find('#paid_at').val(), 'DD.MM.YYYY'));
  }, 150);
});

$(document).on('click', '.act-del', function () {
    let id = $(this).data('id');
    let el = this;

    const $tbl = $('#studentPaymentTbl');
    const SCHOOL_ID = $tbl.data('school-id');

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
                url: `/admin/payment/${id}/delete${SCHOOL_ID ? '?school_id=' + SCHOOL_ID : ''}`,
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

$(document).on('click', '.act-notification', function () {
    let id = $(this).data('id');
    let el = this;

    const $tbl = $('#studentPaymentTbl');
    const SCHOOL_ID = $tbl.data('school-id');

    Swal.fire({
        title: "Դուք ցանկանում եք ուղարկել վճարման ծանուցում՞",
        showDenyButton: true,
        showCancelButton: true,
        confirmButtonText: "Այո",
        showCancelButton: false,
        denyButtonText: `Ոչ`,
        icon: "info",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                url: `/admin/payment/student/sendEmailNotification/${id}`,
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
                  }
                },
                error: function (xhr) {
                  Swal.fire("Սխալ",  xhr.responseJSON.message, "error");
                  console.error(xhr.responseJSON);
                }
            });
        }
    });
});

if ($('#paymentDatePicker').length){
  $('#paymentDatePicker').datetimepicker({
    format: 'DD.MM.YYYY',
    locale: 'hy',
    showTodayButton: true,
    useCurrent: false
  });
}

$('#addPaymentModal').on('hidden.bs.modal', function(){
  const f = $('#singlePaymentForm')[0];
  if (f) f.reset();

  $('.text-danger').text('');

  const $dpWrap = $('#paymentDatePicker');
  const dp = $dpWrap.data('DateTimePicker');
  const today = moment();

  if (dp) dp.date(today);
  $dpWrap.find('input').val(today.format('DD.MM.YYYY'));
});


$('#addPaymentModal').on('shown.bs.modal', function () {
  const $dpWrap = $('#paymentDatePicker');
  const dp = $dpWrap.data('DateTimePicker');

  const $input = $dpWrap.find('input');
  if (!$input.val()) {
    const today = moment();
    if (dp) dp.date(today);
    $input.val(today.format('DD.MM.YYYY'));
  }
});
