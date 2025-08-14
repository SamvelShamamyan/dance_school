function savePayment() {
    const form = document.getElementById("singlePaymentForm");
    const formData = new FormData(form);
    const schoolId = (window.currentUserRole === 'super-admin') ? $('#school_id').val() : null;
    formData.append('school_id',schoolId);

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
    $.ajax({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        url: `/admin/payment/filters`,
        type: 'GET', 
        dataType: 'json',
        success: function (response) {
            
            // ======= Year =======
            let $year = $('#year');
            $year.empty();

            if (response.years && response.years.length) {
                $.each(response.years, function (index, y) {
                    $year.append($('<option>', {
                        value: y,
                        text: y
                    }));
                });
                $year.val(response.years[0]); // choos last year
            } else {
                const y = new Date().getFullYear();
                $year.append($('<option>', { value: y, text: y })).val(y);
            }

            // ======= Group`s =======
            let $group = $('#group_id');
            $group.empty().append($('<option>', { value: '', text: 'Բոլորը' }));

            if (response.groups && response.groups.length) {
                $.each(response.groups, function (index, group) {
                    $group.append($('<option>', {
                        value: group.id,
                        text: group.name
                    }));
                });
            }

            // ======= Status =======
            let $status = $('#status');
            $status.empty().append($('<option>', { value: '', text: 'Բոլորը' }));

            const statusMap = {
                paid: 'Վճարված',
                pending: 'Սպասման մեջ',
                refunded: 'Հետ վերադարձ',
                failed: 'Սխալ'
            };

            if (response.statuses && response.statuses.length) {
                $.each(response.statuses, function (index, s) {
                    $status.append($('<option>', {
                        value: s,
                        text: statusMap[s] || s
                    }));
                });
            }

            $(document).trigger('payment:filtersLoaded');

        }
        
    });

    function initPaymentTable() {
        $('#paymentTbl').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/admin/payment/getData',
                data: function (d) {
                    d.year = $('#year').val();
                    d.group_id = $('#group_id').val();
                    d.status = $('#status').val();
                }
            },
            columns: [
                { data: 'student_name', name: 'student_name' },
                { data: 'amount', name: 'amount' },
                { data: 'paid_at', name: 'paid_at' },
                { data: 'status', name: 'status' }
            ]
        });
    }

    $('#btnRefresh, #year, #group_id, #status').on('change click', function () {
        $('#paymentTbl').DataTable().ajax.reload();
    });

});


$(document).on('click', '#paymentBtn', function () {

    const schoolId = (window.currentUserRole === 'super-admin') ? $('#school_id').val() : null;

  // const schoolId = 1
    $.ajax({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        url: `/admin/payment/getGroups${schoolId ? '?school_id=' + schoolId : ''}`,
        type: 'GET',
        dataType: 'json',
        success: function (response) {            

            let $select = $('#groups');
            $select.empty().append('<option value="" disabled selected>Ընտրել</option>');
        
            $.each(response, function (index, group) {
                $select.append(
                    $('<option>', {
                        value: group.id,
                        text: group.name
                    })
                );
            });            
        }
    });
});


$('#groups').on('change', function () {
    let groupId = $(this).val(); 

    const schoolId = (window.currentUserRole === 'super-admin') ? $('#school_id').val() : null;

    $.ajax({
        url: `/admin/payment/getStudents/${groupId}/${schoolId ? '?school_id=' + schoolId : ''}`, 
        type: 'GET',
        data: { group_id: groupId },
        success: function (response) {
            let $select = $('#students_list');  
                
            $select.empty(); 
            $select.append('<option value="" disabled selected>Ընտրել</option>');

            $.each(response, function (index, student) {
                $select.append(
                    $('<option>', {
                        value: student.id,
                        text: student.full_name
                    })
                );
            }); 
            $('#students_list').prop('disabled', false);           
        }
    });
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
    $('.bootstrap-datetimepicker-widget').css({
      'width': '360px',
      'min-width': '360px'
    });
  });

});

// === Պատմություն (History) ===
$(document).on('click', '#paymentTbl .view-history', function () {
  const name = $(this).data('name');
  const id   = $(this).data('id');

  const year   = $('#year').val()   || '';
  const status = $('#status').val() || '';
  const group  = $('#group_id').val() || '';
  //  const moreUrl = `/admin/payment/student/${id}`;
  const schoolId = (window.currentUserRole === 'super-admin') ? $('#school_id').val() : null;
  // const moreUrl = `/admin/payment/student/${id}?school_id=${schoolId}`;

  const moreUrl = (schoolId)
  ? `/admin/payment/student/${id}?school_id=${schoolId}`
  : `/admin/payment/student/${id}`;

  $.ajax({
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    url: '/admin/payment/history',
    type: 'POST',
    dataType: 'json',
    data: {
      student_id: id,
      year: year,
      group_id: group,
      status: status,
      school_id: schoolId,
    },
    success: function (res) {
      const rows = (res && res.data) ? res.data : [];

      const monthShortHY = ['Հուն','Փետ','Մար','Ապր','Մայ','Հուն','Հուլ','Օգս','Սպ','Հոկ','Նոյ','Դեկ'];
      const money = n => Number(n||0).toLocaleString('hy-AM', { maximumFractionDigits: 0 });

      const methodMap = { cash:'Կանխիկ', card:'Անկանխիկ', online:'Առցանց' };
      const statusMap = { paid:'Վճարված', pending:'Սպասման մեջ', refunded:'Վերադարձված', failed:'Սխալ' };

      const html = rows.length ? rows.map(r => {
      const d = new Date(r.paid_at);

      const dateStr = moment(r.paid_at).format('DD.MM.YYYY');

      const badge = monthShortHY[d.getMonth()] || '';
      const method = methodMap[r.method] || r.method || '';
      const statusL = statusMap[r.status] || r.status || '';
      const comment = r.comment ? ' · ' + r.comment : '';

        return `
          <div class="list-group-item d-flex justify-content-between align-items-center">
            <div>
              <div class="font-weight-bold">${dateStr} · <span class="text-success">${money(r.amount)}</span></div>
              <div class="small text-muted">${method} · ${statusL}${comment}</div>
            </div>
            <div class="d-flex align-items-center">
              <span class="badge badge-light text-dark mr-2">${badge}</span>
              <button class="btn btn-sm btn-outline-primary history-edit mr-2"
                      data-id="${r.id}"
                      data-paid="${moment(r.paid_at).format('DD.MM.YYYY')}"
                      data-amount="${r.amount}"
                      data-method="${r.method}"
                      data-status="${r.status}"
                      data-comment="${r.comment ? r.comment.replace(/"/g,'&quot;') : ''}">
                <i class="fas fa-edit"></i>
              </button>
              <button class="btn btn-sm btn-outline-danger history-delete" data-id="${r.id}">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        `;
      }).join('') : `
        <div class="list-group-item text-center text-muted">Վճարումներ չկան</div>
      `;

      const modalId = 'historyModal';
      const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Վճարումների պատմություն — ${name}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Փակել">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body p-0">
                <div class="list-group list-group-flush">
                  ${html}
                </div>
              </div>
              <div class="modal-footer">
                <a class="btn btn-primary" href="${moreUrl}">Տեսնել ավելին</a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Փակել</button>
              </div>
            </div>
          </div>
        </div>`;
      $('body').append(modalHtml);
      const $m = $('#'+modalId);
      $m.on('hidden.bs.modal', function(){ $(this).remove(); });
      $m.modal('show');
    },
    error: function () {
      swal('error', 'Չհաջողվեց բեռնել պատմությունը', true, true);
    }
  });
});


$(document).on('click', '.history-edit', function(){
    $('#payment_id').val($(this).data('id'));
    $('#edit_paid_at').val($(this).data('paid'));
    $('#edit_amount').val($(this).data('amount'));
    $('#edit_method').val($(this).data('method'));
    $('#edit_status').val($(this).data('status'));
    $('#edit_comment').val($(this).data('comment'));
    $('#editPaymentModal').modal('show');
    const schoolId = (window.currentUserRole === 'super-admin') ? $('#school_id').val() : null;
    $('#edit_school_id').val(schoolId);


    const openEdit = () => {
    $('#editPaymentModal').modal('show');

    $('#editPaymentModal').one('shown.bs.modal', function () {
      if ($('#paymentEditDatePicker').data('DateTimePicker')) {
        $('#paymentEditDatePicker').data('DateTimePicker')
          .date(moment($('#edit_paid_at').val(), 'DD.MM.YYYY'));
      }
    });
  };

  if ($('#historyModal').is(':visible')) {
    $('#historyModal').one('hidden.bs.modal', openEdit).modal('hide');
  } else {
    openEdit();
  }

});

$('#editPaymentForm').on('submit', function(e){
    e.preventDefault();
    const id = $('#payment_id').val();
    const data = $(this).serialize();

    $.ajax({
        url: `/admin/payment/update/${id}`,
        type: 'POST',
        data: data,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(res){
            if(res.status === 1){
                $('#editPaymentModal').modal('hide');
                $('#paymentTbl').DataTable().ajax.reload(null, false);
                swal('success', 'Թարմացվել է', true, true);
            }
        },
        error: function(xhr){
            if (xhr.status === 422) {
                $('.text-danger').text('');
                $.each(xhr.responseJSON.errors, function(key, val){
                    $(`.error_${key}`).text(val[0]);
                });
            }
        }
    });
});

$(document).on('click', '.history-delete', function () {
    let id = $(this).data('id');
    let el = this;

    const schoolId = (window.currentUserRole === 'super-admin') ? $('#school_id').val() : null;


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
                url: `/admin/payment/${id}/delete${schoolId ? '?school_id=' + schoolId : ''}`,
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


// $('#school').on('change', function () {
//     let groupId = $(this).val(); 
//     $.ajax({
//         url: `/admin/payment/getStudents/${groupId}`, 
//         type: 'GET',
//         data: { group_id: groupId },
//         success: function (response) {
//             let $select = $('#students_list');  
                
//             $select.empty(); 
//             $select.append('<option value="" disabled selected>Ընտրել</option>');

//             $.each(response, function (index, student) {
//                 $select.append(
//                     $('<option>', {
//                         value: student.id,
//                         text: student.full_name
//                     })
//                 );
//             }); 
//             $('#students_list').prop('disabled', false);           
//         }
//     });
// });


if (window.currentUserRole === 'super-admin') {
  $.ajax({
    url: '/admin/payment/getSchools',
    type: 'GET',
    success: function (schools) {
      const $school = $('#school_id');
      $school.empty().append('<option value="" disabled selected>Ընտրել դպրոց</option>');

      schools.forEach(school => {
        $school.append(`<option value="${school.id}">${school.name}</option>`);
      });

      $school.on('change', function () {
        const schoolId = $(this).val();

        $.ajax({
          url: `/admin/payment/getGroupsBySchool/${schoolId}`,
          type: 'GET',
          success: function (groups) {
            const $group = $('#group_id');
            $group.empty().append('<option value="">Բոլորը</option>');

            groups.forEach(group => {
              $group.append(`<option value="${group.id}">${group.name}</option>`);
            });

            $('#paymentTbl').DataTable().ajax.reload();
          }
        });
      });
    }
  });
}

//super-admin
$('#school_id').on('change', function () {
  const schoolId = $(this).val();

  $.ajax({
    url: '/admin/payment/filters',
    type: 'GET',
    data: { school_id: schoolId },
    success: function (res) {
      // === Տարի ===
      const $year = $('#year');
      $year.empty();
      if (res.years && res.years.length) {
        res.years.forEach(y => {
          $year.append(`<option value="${y}">${y}</option>`);
        });
        $year.val(res.years[0]);
      }

      // === Խումբ ===
      const $group = $('#group_id');
      $group.empty().append('<option value="">Բոլորը</option>');
      if (res.groups && res.groups.length) {
        res.groups.forEach(g => {
          $group.append(`<option value="${g.id}">${g.name}</option>`);
        });
      }

      // === Վճարման կարգավիճակ ===
      const $status = $('#status');
      const statusMap = {
        paid: 'Վճարված',
        pending: 'Սպասման մեջ',
        refunded: 'Հետե վերադարձ',
        failed: 'Սխալ'
      };

      $status.empty().append('<option value="">Բոլորը</option>');
      if (res.statuses && res.statuses.length) {
        res.statuses.forEach(s => {
          $status.append(`<option value="${s}">${statusMap[s] || s}</option>`);
        });
      }

      // reload table
      $('#paymentTbl').DataTable().ajax.reload();
    }
  });
});



$('#addPaymentModal').on('hidden.bs.modal', function () {
  const $m = $(this);

  const f = $m.find('#singlePaymentForm')[0];
  if (f) f.reset();

  $m.find('.text-danger').empty();

  $m.find('#groups').val('');
  $m.find('#students_list')
    .prop('disabled', true)
    .empty()
    .append('<option value="" disabled selected>Ընտրել</option>');
  $m.find('#pay_method').val('');
  $m.find('#pay_status').val('');

  const $dp = $m.find('#paymentDatePicker');
  if ($dp.data('DateTimePicker')) {
    $dp.data('DateTimePicker').clear();              
    $dp.data('DateTimePicker').date(moment());      
  } else {
    $m.find('#paid_at').val('');
  }
});
