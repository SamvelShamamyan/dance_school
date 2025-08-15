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



$(function () {
  const dzEl   = document.getElementById('staff-dropzone');
  const formEl = document.getElementById('StaffForm');
  const saveBtn= document.getElementById('staffBtn');

  if (!dzEl || !formEl || !saveBtn) return;
  if (typeof Dropzone === 'undefined') { console.error('Dropzone not loaded'); return; }

  const dz = new Dropzone(dzEl, {
    url: formEl.action,
    paramName: "files",           
    uploadMultiple: true,
    parallelUploads: 5,
    autoProcessQueue: false,
    maxFilesize: 10,               // MB
    maxFiles: 5,
    acceptedFiles: "image/*,.pdf",
    addRemoveLinks: true,
    clickable: '#staff-dropzone',
    previewsContainer: '#staff-dropzone',
    hiddenInputContainer: document.body,
    headers: { "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content }
  });

  dz.on("sendingmultiple", function (files, xhr, formData) {
    $('#StaffForm').serializeArray().forEach(({name, value}) => formData.append(name, value));
    $('.text-danger').text('');
    $('#staffBtn').prop('disabled', true);
  });

  dz.on("successmultiple", function (files, response) {
    if (response && response.status === 1) {
      swal("success", "Գործողությունը կատարված է", true, true);
      formEl.reset();
      dz.removeAllFiles(true);
      $('#removed-files').empty(); 
      $('#staffBtn').prop('disabled', true);
    } else {
      $('#staffBtn').prop('disabled', false);
      swal("error", "Something went wrong!", true, true);
    }
  });

  dz.on("errormultiple", function (files, xhrResp, xhr) {
    $('#staffBtn').prop('disabled', false);
    try {
      const res = xhr && xhr.response ? JSON.parse(xhr.response) : xhrResp;
      if (xhr && xhr.status === 422 && res && res.errors) {
        for (const field in res.errors) $(`.error_${field}`).text(res.errors[field][0]);
      } else {
        swal("error", (res && res.message) ? res.message : "Upload failed", true, true);
      }
    } catch {
      swal("error", "Upload failed", true, true);
    }
  });

  saveBtn.addEventListener('click', function () {
    if (dz.getAcceptedFiles().length > 0) {
      dz.processQueue();         
    } else {
      saveStaff();              
    }
  });
});



$(document).on('click', '.js-mark-remove', function () {
  const id   = $(this).data('id');
  const card = $(this).closest('[data-file-id]');
  const wrap = $('#removed-files');

  let input = wrap.find(`input[name="removed_files[]"][value="${id}"]`);

  if (input.length) {
    input.remove();
    $(this).removeClass('btn-danger').addClass('btn-outline-danger').text('Ջնջել');
    card.removeClass('border-danger bg-light');
  } else {
    $('<input>', {type:'hidden', name:'removed_files[]', value:id}).appendTo(wrap);
    $(this).removeClass('btn-outline-danger').addClass('btn-danger').text('Չեղարկել');
    card.addClass('border-danger bg-light');
  }
});