if (window.__PAYMENT_JS_LOADED__) {
  console.warn('payment.js already loaded — skipped');
} else {
  window.__PAYMENT_JS_LOADED__ = true;

  window.statusMap   ||= { paid:'Վճարված', pending:'Սպասման մեջ', refunded:'Վերադարձված', failed:'Սխալ' };
  window.methodMap   ||= { cash:'Կանխիկ',  card:'Անկանխիկ', online:'Առցանց' };
  window.monthShortHY ||= ['Հուն','Փետ','Մար','Ապր','Մայ','Հուն','Հուլ','Օգս','Սեպ','Հոկ','Նոյ','Դեկ'];
  window.money       ||= (n => Number(n || 0).toLocaleString('hy-AM', { maximumFractionDigits: 0 }));

  function currentSchoolId() {
    return $('#school_id').length ? ($('#school_id').val() || '') : undefined;
  }

  function renderSummary(arr){
    const wrap = document.getElementById('summary');
    if (!wrap) return;
    wrap.innerHTML = (arr || []).map((sum, i) => `
      <div class="col-6 col-sm-4 col-md-2 col-lg-1 mb-2">
        <div class="card p-2 text-center">
          <div class="text-muted small">${monthShortHY[i] || ''}</div>
          <div class="sum" style="font-weight:700">${money(sum)}</div>
        </div>
      </div>
    `).join('');
  }


  function savePayment() {
    const form = document.getElementById("singlePaymentForm");
    const formData = new FormData(form);    
    const schoolId = ($('#addPaymentModal').find('#school_id').val() ?? window.currentUserRoleSchoolId) ;
    if (schoolId !== undefined) formData.append('school_id', schoolId);

    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url: `/admin/payment/add`,
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      type: 'POST',
      dataType: 'json',
      success: async function(response) {
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
          $('#singlePaymentBtn').prop('disabled', true);
          if ($.fn.DataTable.isDataTable('#paymentTbl')) {
            $('#paymentTbl').DataTable().ajax.reload(null, false);
          }
        }
      },
      error: function(xhr) {
        if (xhr.status === 422) {
          $('.text-danger').text('');
          let errors = xhr.responseJSON.errors;
          $.each(errors, function(field, messages) {
            $(`.error_${field}`).text(messages[0]);         
          });
        } else {
          swal("error", "Something went wrong!", true, true)
        }
      }
    });
  }
  window.savePayment = savePayment; 

$(document).on('submit', '#singlePaymentForm', function(e) {
    e.preventDefault();
    savePayment();
});

  $(function () {
    moment.locale('hy');
    const paymentDate = $('#paid_at').val();
    const paymentEditPaidDate = $('#edit_paid_at').val();

    $('#paymentDatePicker').datetimepicker({
      format: 'DD.MM.YYYY',
      locale: 'hy',
      showTodayButton: true,
      defaultDate: paymentDate ? moment(paymentDate, 'DD.MM.YYYY') : moment()
    });

    $('#paymentEditDatePicker').datetimepicker({
      format: 'DD.MM.YYYY',
      locale: 'hy',
      showTodayButton: true,
      defaultDate: paymentEditPaidDate ? moment(paymentEditPaidDate, 'DD.MM.YYYY') : moment()
    });

    $('#paymentDatePicker, #paymentEditDatePicker').on('show.datetimepicker', function () {
      $('.bootstrap-datetimepicker-widget').css({ 'width': '360px', 'min-width': '360px' });
    });
  });



  function loadFiltersAndMaybeInitTables() {
    const schoolId = currentSchoolId(); 
    $.ajax({
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      url: `/admin/payment/filters`,
      type: 'GET',
      dataType: 'json',
      data: { school_id: schoolId ?? '' }, 
      success: function (res) {
        // === Տարի ===
        const $year = $('#year').empty();
        if (res.years && res.years.length) {
          res.years.forEach(y => $year.append(`<option value="${y}">${y}</option>`));
          $year.val(res.years[0]);
        } else {
          const y = new Date().getFullYear();
          $year.append(`<option value="${y}">${y}</option>`).val(y);
        }

        // === Խումբ ===
        const $group = $('#group_id').empty().append('<option value="">Բոլորը</option>');
        (res.groups || []).forEach(g => $group.append(`<option value="${g.id}">${g.name}</option>`));

        // === Վճարման տարբերակ ===
        const $method = $('#method').empty().append('<option value="">Բոլորը</option>');
        (res.methods || []).forEach(s => $method.append(`<option value="${s}">${methodMap[s] || s}</option>`));

        // === Վճարման կարգավիճակ ===
        const $status = $('#status').empty().append('<option value="">Բոլորը</option>');
        (res.statuses || []).forEach(s => $status.append(`<option value="${s}">${statusMap[s] || s}</option>`));

        $(document).trigger('payment:filtersLoaded');

        if ($.fn.DataTable.isDataTable('#paymentTbl')) {
          $('#paymentTbl').DataTable().ajax.reload();
        }
      }
    });
  }

  $(function(){
    if (!$('#school_id').length) {
      loadFiltersAndMaybeInitTables();
      return;
    }

    $.ajax({
      url: '/admin/payment/getSchools',
      type: 'GET',
      success: function (schools) {
        const $school = $('#school_id');
        $school.empty().append('<option value="" selected>Բոլորը</option>');
        (schools || []).forEach(school => $school.append(`<option value="${school.id}">${school.name}</option>`));

        loadFiltersAndMaybeInitTables();
      }
    });

    $('#school_id').off('change.payment').on('change.payment', loadFiltersAndMaybeInitTables);
  });

  
  $(document).off('click.payment', '#paymentBtn').on('click.payment', '#paymentBtn', function () { 
    if (window.currentUserRole === 'super-admin' || window.currentUserRole === 'super-accountant'){
      $.ajax({
        url: '/admin/payment/getSchools',
        type: 'GET',
        success: function (schools) {
          const $modalSchool = $('#addPaymentModal').find('#school_id');
          $modalSchool.empty().append('<option value="" selected disabled>Ընտրել</option>');
          (schools || []).forEach(school => $modalSchool.append(`<option value="${school.id}">${school.name}</option>`));

        }
      });
    }else{
      schoolId = window.currentUserRoleSchoolId ?? 'undefined';
      $.ajax({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        url: `/admin/payment/getGroups${schoolId !== undefined ? ('?school_id=' + schoolId) : ''}`,
        type: 'GET',
        dataType: 'json',
        success: function (response) {
          const $select = $('#groups').empty().append('<option value="" disabled selected>Ընտրել</option>');
          (response || []).forEach(group => {
            $select.append($('<option>', { value: group.id, text: group.name }));
          });
          $select.prop('disabled', false);
        }
      });
    }
  });

  $(document).on('change', '#addPaymentModal #school_id', function () {
      const sid = $(this).val();
      $.ajax({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        url: `/admin/payment/getGroups${sid !== undefined ? ('?school_id=' + sid) : ''}`,
        type: 'GET',
        dataType: 'json',
        success: function (response) {
          const $select = $('#groups').empty().append('<option value="" disabled selected>Ընտրել</option>');
          (response || []).forEach(group => {
            $select.append($('<option>', { value: group.id, text: group.name }));
          });
          $select.prop('disabled', false);
        }
      });
  });

  $(document).on('change', '#addPaymentModal #students_list', function () {
      const studId = $(this).val();
      $.ajax({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        url: `/admin/payment/getStudentData/${studId}`,
        type: 'GET',
        dataType: 'json',
        success: function (response) {
          let amount = 0;        
           if (response.student_debts && Number(response.student_debts) > 0) {
              amount = Number(response.student_debts);
            }

          $('#singlePaymentForm').find('#amount').val(amount); 
          if(response.student_prepayment > 0){
            $('#singlePaymentForm').find('#infoBlock').css('display', 'block');
            $('#singlePaymentForm').find('#infoBlockContent').text(`Տվյալ ուսանողը ունի հավելավճար՝ ${money(response.student_prepayment).toLocaleString('hy-AM')} դրամ`);
          }else{
             $('#singlePaymentForm').find('#infoBlock').css('display', 'none');
          }
        }
      });
  });



  $(document).off('change.payment', '#groups').on('change.payment', '#groups', function () {
    const groupId = $(this).val();
    $.ajax({
      url : `/admin/payment/getStudents/${groupId}`,
      type: 'GET',
      success: function (response) {
        const $select = $('#students_list').empty().append('<option value="" disabled selected>Ընտրել</option>');
        (response || []).forEach(student => {
          $select.append($('<option>', { value: student.id, text: student.full_name }));
        });
        $('#students_list').prop('disabled', false);
      }
    });
  });

  $(document).off('click.payment', '#paymentTbl .view-history').on('click.payment', '#paymentTbl .view-history', function (e) {
    e.preventDefault();
    e.stopPropagation();
    const id  = $(this).data('id');
    const sid = $(this).data('school-id') ?? currentSchoolId();
    const base = `/admin/payment/student/${encodeURIComponent(id)}`;
    const url  = sid ? `${base}?school_id=${encodeURIComponent(sid)}` : base;
    window.location.assign(url);
  });

  // $('#addPaymentModal').off('hidden.bs.modal.payment').on('hidden.bs.modal.payment', function () {
  //   const $m = $(this);
  //   const f = $m.find('#singlePaymentForm')[0];
  //   if (f) f.reset();
  //   $m.find('.text-danger').empty();
  //   $m.find('#groups').val('');
  //   $m.find('#students_list').prop('disabled', true).empty().append('<option value="" disabled selected>Ընտրել</option>');
  //   $m.find('#pay_method').val('');
  //   $m.find('#pay_status').val('');

  //   const $dp = $m.find('#paymentDatePicker');
  //   if ($dp.data('DateTimePicker')) {
  //     $dp.data('DateTimePicker').clear();
  //     $dp.data('DateTimePicker').date(moment());
  //   } else {
  //     $m.find('#paid_at').val('');
  //   }

  //   $('#singlePaymentForm').find('#infoBlock').css('display', 'none');
  //   $('#singlePaymentForm').find('#infoBlockContent').text('');

  // });

  $('#addPaymentModal').off('hidden.bs.modal.payment').on('hidden.bs.modal.payment', function () {
    const $m = $(this);
    const f = $m.find('#singlePaymentForm')[0];
    if (f) f.reset();
    $m.find('.text-danger').empty();
    $m.find('#groups').val('');
    $m.find('#students_list').prop('disabled', true).empty().append('<option value="" disabled selected>Ընտրել</option>');
    $m.find('#pay_method').val('');
    $m.find('#pay_status').val('');
  
    $('#singlePaymentForm').find('#infoBlock').css('display', 'none');
    $('#singlePaymentForm').find('#infoBlockContent').text('');

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

  $('#paymentTbl').off('xhr.dt.payment').on('xhr.dt.payment', function (_e, _settings, json) {
    if (json && Array.isArray(json.summary)) renderSummary(json.summary);
  });

  $('#btnRefresh, #year, #group_id, #status').off('change.payment click.payment').on('change.payment click.payment', function(){
    if ($.fn.DataTable.isDataTable('#paymentTbl')) {
      $('#paymentTbl').DataTable().ajax.reload();
    }
  });

  $(function(){
    if (!$('#year option').length) {
      loadFiltersAndMaybeInitTables();
    } else {
      $(document).trigger('payment:filtersLoaded');
      if ($.fn.DataTable.isDataTable('#paymentTbl')) {
        $('#paymentTbl').DataTable().ajax.reload();
      }
    }
  });

}


$(function(){
  let today = moment();
  let startYear = today.month() >= 8 ? today.year() : today.year() - 1;

  let start = moment(`${startYear}-09-01`, "YYYY-MM-DD");
  let end = moment(`${startYear + 1}-05-31`, "YYYY-MM-DD");

  $('#range_from').val(start.format('YYYY-MM-DD'));
  $('#range_to').val(end.format('YYYY-MM-DD'));

  $('#filter_range_date').daterangepicker({
    autoUpdateInput: true, 
    locale: {
      format: 'DD.MM.YYYY',
      applyLabel: 'Կիրառել',
      cancelLabel: 'Չեղարկել',
      fromLabel: 'Սկիզբ',
      toLabel: 'Վերջ',
      daysOfWeek: ['Կ', 'Ե', 'Ե', 'Չ', 'Հ', 'Ու', 'Շ'],
      monthNames: [
        'Հունվար', 'Փետրվար', 'Մարտ', 'Ապրիլ', 'Մայիս', 'Հունիս',
        'Հուլիս', 'Օգոստոս', 'Սեպտեմբեր', 'Հոկտեմբեր', 'Նոյեմբեր', 'Դեկտեմբեր'
      ],
      firstDay: 1
    },
    startDate: start,
    endDate: end      
  }, function(start, end, label) {
    $('#filter_range_date').val(start.format('DD.MM.YYYY') + ' - ' + end.format('DD.MM.YYYY'));

    $('#range_from').val(start.format('YYYY-MM-DD'));
    $('#range_to').val(end.format('YYYY-MM-DD')).trigger('change');
  });
})