function saveStaff() {
    const form = document.getElementById("StaffForm");
    const formData = new FormData(form);
    const url = form.getAttribute('action'); 

    const phone_1 = $('#phone_1').cleanVal();
    const phone_2 = $('#phone_2').cleanVal();

    formData.set('phone_1', phone_1);
    formData.set('phone_2', phone_2 );

    $.ajax({        
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url: url,
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        type: 'POST',
        dataType: 'json',
        success: async function(response) {

            if (response.status === 1) {
              await swal("success", "Գործողությունը կատարված է", true, true);
                $('.text-danger').text('');
                $('#StaffForm')[0].reset();
                // $('.text-danger').text('');
                showFieldErrors(form, {}); 
                $('#staffBtn').prop('disabled', true);
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

$(document).on('submit', '#StaffForm', function(e) {
    e.preventDefault();
    saveStaff();
});


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



// $(function () {
//   const dzEl   = document.getElementById('staff-dropzone');
//   const formEl = document.getElementById('StaffForm');
//   const saveBtn= document.getElementById('staffBtn');

//   if (!dzEl || !formEl || !saveBtn) return;
//   if (typeof Dropzone === 'undefined') { console.error('Dropzone not loaded'); return; }

//   const dz = new Dropzone(dzEl, {
//     url: formEl.action,
//     paramName: "files",           
//     uploadMultiple: true,
//     parallelUploads: 5,
//     autoProcessQueue: false,
//     maxFilesize: 10,               // MB
//     maxFiles: 5,
//     acceptedFiles: "image/*,.pdf",
//     addRemoveLinks: true,
//     clickable: '#staff-dropzone',
//     previewsContainer: '#staff-dropzone',
//     hiddenInputContainer: document.body,
//     headers: { "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content },
//     dictRemoveFile: 'Հեռացնել',
//   });

//   dz.on("sendingmultiple", function (files, xhr, formData) {
//     $('#StaffForm').serializeArray().forEach(({name, value}) => formData.append(name, value));
//     $('.text-danger').text('');
//     $('#staffBtn').prop('disabled', true);
//   });

//   dz.on("successmultiple", function (files, response) {
//     if (response && response.status === 1) {
//       swal("success", "Գործողությունը կատարված է", true, true);
//       formEl.reset();
//       dz.removeAllFiles(true);
//       $('#removed-files').empty(); 
//       $('#staffBtn').prop('disabled', true);
//     } else {
//       $('#staffBtn').prop('disabled', false);
//       swal("error", "Something went wrong!", true, true);
//     }
//   });

//   dz.on("errormultiple", function (files, xhrResp, xhr) {
//     $('#staffBtn').prop('disabled', false);
//     try {
//       const res = xhr && xhr.response ? JSON.parse(xhr.response) : xhrResp;
//       if (xhr && xhr.status === 422 && res && res.errors) {
//         for (const field in res.errors) $(`.error_${field}`).text(res.errors[field][0]);
//       } else {
//         swal("error", (res && res.message) ? res.message : "Upload failed", true, true);
//       }
//     } catch {
//       swal("error", "Upload failed", true, true);
//     }
//   });

//   saveBtn.addEventListener('click', function () {
//     if (dz.getAcceptedFiles().length > 0) {
//       dz.processQueue();         
//     } else {
//       saveStaff();              
//     }
//   });
// });


$(function () {
  const dzElStud = document.getElementById('staff-dropzone');
  const formEl   = document.getElementById('StaffForm');
  const saveBtn  = document.getElementById('staffBtn');

  if (!dzElStud || !formEl || !saveBtn) return;
  if (typeof Dropzone === 'undefined') { console.error('Dropzone not loaded'); return; }

  function normalizeDzError(errorMessage, xhr) {
    if (xhr && xhr.response) {
      try {
        const res = JSON.parse(xhr.response);
        if (res?.errors) return Object.values(res.errors).flat().join('\n');
        if (res?.message) return res.message;
      } catch (_) {}
    }
    if (typeof errorMessage === 'object' && errorMessage) {
      return errorMessage.message || 'Սխալ վավերացման ժամանակ';
    }
    return String(errorMessage || 'Upload failed');
  }

  const dzStud = new Dropzone(dzElStud, {
    url: formEl.action,
    paramName: "files",
    uploadMultiple: true,
    parallelUploads: 5,
    autoProcessQueue: false,
    maxFilesize: 10,
    maxFiles: 5,
    acceptedFiles: "image/*,.pdf",
    addRemoveLinks: true,
    clickable: '#staff-dropzone',
    previewsContainer: '#staff-dropzone',
    hiddenInputContainer: document.body,
    headers: { "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content },
    dictRemoveFile: 'Հեռացնել',
  });

  // dzStud.on("sendingmultiple", function (files, xhr, formData) {
  //   $('#StaffForm').serializeArray().forEach(({name, value}) => formData.append(name, value));
  //   $('.text-danger').text('');
  //   $('#staffBtn').prop('disabled', true);
  // });

  dzStud.on("sendingmultiple", function (files, xhr, formData) {
    $('#StaffForm').serializeArray().forEach(({ name, value }) => {
      formData.set(name, value);
    });

    const clean = v => (v || '').replace(/\D/g, '');
    const p1 = (typeof $('#phone_1').cleanVal === 'function')
      ? $('#phone_1').cleanVal()
      : clean(formData.get('phone_1'));

    const p2 = (typeof $('#phone_2').cleanVal === 'function')
      ? $('#phone_2').cleanVal()
      : clean(formData.get('phone_2'));

    formData.set('phone_1', p1);
    formData.set('phone_2', p2);

    $('.text-danger').text('');
    $('#staffBtn').prop('disabled', true);
  });

  dzStud.on("successmultiple", function (files, response) {
    const res = (typeof response === 'string') ? JSON.parse(response) : response;

    if (res && res.status === 1) {
      (async () => {
        await swal("success", "Գործողությունը կատարված է", true, true);
        if (res.redirect) {
          window.location.href = res.redirect;  
          return; 
        }
        formEl.reset();
        dzStud.removeAllFiles(true);
        $('#removed-files').empty();
        $('#staffBtn').prop('disabled', true);
      })();
    } else {
      $('#staffBtn').prop('disabled', false);
      swal("error", "Something went wrong!", true, true);
    }
  });

  dzStud.on('error', function (file, errorMessage, xhr) {
    const msg = normalizeDzError(errorMessage, xhr);
    const el = file.previewElement?.querySelector('[data-dz-errormessage]');
    if (el) el.textContent = msg;
  });

  dzStud.on("errormultiple", function (files, errorMessage, xhr) {
    $('#staffBtn').prop('disabled', false);

    try {
      const res = xhr && xhr.response ? JSON.parse(xhr.response) : null;
      if (xhr?.status === 422 && res?.errors) {
        for (const field in res.errors) {
          $(`.error_${field}`).text(res.errors[field][0]);
        }
      }
    } catch (_) {}

    if (xhr && xhr.status === 422) {
      files.forEach(f => {
        f.status = Dropzone.QUEUED;
        if (f.previewElement) {
          f.previewElement.classList.remove('dz-error');
          const msgEl = f.previewElement.querySelector('[data-dz-errormessage]');
          if (msgEl) msgEl.textContent = '';
        }
      });
      return;
    }

    const msg = normalizeDzError(errorMessage, xhr);
    files.forEach(f => {
      const el = f.previewElement?.querySelector('[data-dz-errormessage]');
      if (el) el.textContent = msg;
    });
    swal("error", msg, true, true);
  });

  saveBtn.addEventListener('click', function () {
    $('.text-danger').text('');

    dzStud.getFilesWithStatus(Dropzone.ERROR).forEach(f => {
      f.status = Dropzone.QUEUED;
      if (f.previewElement) {
        f.previewElement.classList.remove('dz-error');
        const el = f.previewElement.querySelector('[data-dz-errormessage]');
        if (el) el.textContent = '';
      }
    });

    if (dzStud.getAcceptedFiles().length > 0) {
      dzStud.processQueue();
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

