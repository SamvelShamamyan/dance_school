function savePayent() {
    const form = document.getElementById("singlePaymentForm");
    const formData = new FormData(form);
    $.ajax({        
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url: `/admin/payment/add`,
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        type: 'POST',
        dataType: 'json',
        success: function(response) {

            if (response.status === 1) {
                swal("success", "Գործողությունը կատարված է", true, true);
                $('#singlePaymentForm')[0].reset();
                $('.text-danger').text('');
                $('#singlePaymentBtn').prop('disabled', true);
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
  const $tbl = $('#studentPaymentTbl');
  if (!$tbl.length) return;

  const STUDENT_ID = $tbl.data('student-id');

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
    url: `/admin/payment/student/filters/${STUDENT_ID}`,
    type: 'GET',
    dataType: 'json',
    success: function (response) {

      const $year = $('#year').empty();
      const years = (response.years && response.years.length) ? response.years : [new Date().getFullYear()];
      years.forEach(y => $year.append(new Option(y, y)));
      $year.val(years[0]); 

      // const statusMap = { paid:'Վճարված', pending:'Սպասման մեջ', refunded:'Վերադարձված', failed:'Սխալ' };
      const statusMap = { paid:'Վճարված', pending:'Սպասման մեջ'};
      const $status = $('#status').empty().append(new Option('Բոլորը',''));
      (response.statuses || []).forEach(s => $status.append(new Option(statusMap[s] || s, s)));

      $(document).trigger('payment:filtersLoaded');
    }
  });

  $('#btnRefresh, #year, #status').on('change click', function(){
    if ($.fn.DataTable.isDataTable('#studentPaymentTbl')) {
      $('#studentPaymentTbl').DataTable().ajax.reload();
    }
  });
});

// ===== Edit open
$(document).on('click', '.act-edit', function(){
  $('#payment_id').val($(this).data('id'));
  $('#edit_paid_at').val($(this).data('paid'));
  $('#edit_amount').val($(this).data('amount'));
  $('#edit_method').val($(this).data('method'));
  $('#edit_status').val($(this).data('status'));
  $('#edit_comment').val($(this).data('comment'));
  $('#editPaymentModal').modal('show');

  setTimeout(function(){
    const dp = $('#paymentEditDatePicker').data('DateTimePicker');
    if (dp) dp.date(moment($('#edit_paid_at').val(), 'DD.MM.YYYY'));
  }, 150);
});

// ===== Edit submit
$('#editPaymentForm').on('button', function(e){
  e.preventDefault();
  const id = $('#payment_id').val();
  const data = $(this).serialize();
  $.ajax({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    url: `/admin/payment/update/${id}`,
    type: 'POST',
    data: data,
    success: function(res){
      if (res && res.status === 1){
          $('#editPaymentModal').modal('hide');
          $('#paymentTbl').DataTable().ajax.reload(null, false);
          swal('success', 'Թարմացվել է', true, true);
      }
    },
    error: function(xhr){
      $('.text-danger').text('');
      if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors){
        $.each(xhr.responseJSON.errors, function (key, val) {
          $(`.error_${key}`).text(val[0]);
        });
      }
    }
  });
});

$(document).on('click', '.act-del', function () {
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
                url: `/admin/payment/${id}/delete`,
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

// ===== Reset add-modal
$('#addPaymentModal').on('hidden.bs.modal', function(){
  const f = $('#singlePaymentForm')[0];
  if (f) f.reset();
  $('.text-danger').text('');
  const dp = $('#paymentDatePicker').data('DateTimePicker');
  if (dp) dp.date(moment());
});