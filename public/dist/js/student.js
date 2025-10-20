function saveStudent() {
    const form = document.getElementById("StudentForm");
    const formData = new FormData(form);
    const url = form.getAttribute('action'); 

    const phone_1 = $('#phone_1').cleanVal();
    const phone_2 = $('#phone_2').cleanVal();

    formData.set('phone_1', phone_1);
    formData.set('phone_2', phone_2 );

    $.ajax({        
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
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
                $('#StudentForm')[0].reset();
                // $('.text-danger').text('');
                showFieldErrors(form, {}); 
                $('#studentBtn').prop('disabled', true);
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

$(document).on('submit', '#StudentForm', function(e) {
    e.preventDefault();
    saveStudent();
});

$(document).on('click', '.btn-edit-student', function () {
    let id = $(this).data('id');
    if (id) {
        window.location.href = `/admin/student/${id}/edit`;
    }
});


$(document).on('click', '.btn-delete-student', function () {
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
                url: `/admin/student/${id}/delete`,
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
                        Swal.fire("srror", "Չհաջողվեց ջնջել։", "error");
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
  const studentDate = $('#created_date').val();

  $('#studentBirthDatePicker').datetimepicker({
    format: 'DD.MM.YYYY',
    locale: 'hy',
    showTodayButton: true,
    defaultDate: birthDate ? moment(birthDate, 'DD.MM.YYYY') : moment()
  });

  $('#studentDatePicker').datetimepicker({
    format: 'DD.MM.YYYY',
    locale: 'hy',
    showTodayButton: true,
    defaultDate: studentDate ? moment(studentDate, 'DD.MM.YYYY') : moment()
  });
});


// $(function () {
//   const dzElStud   = document.getElementById('student-dropzone');
//   const formEl = document.getElementById('StudentForm');
//   const saveBtn = document.getElementById('studentBtn');

//   if (!dzElStud || !formEl || !saveBtn) return;
//   if (typeof Dropzone === 'undefined') { console.error('Dropzone not loaded'); return; }

//   const dzStud = new Dropzone(dzElStud, {
//     url: formEl.action,
//     paramName: "files",           
//     uploadMultiple: true,
//     parallelUploads: 5,
//     autoProcessQueue: false,
//     maxFilesize: 10,               // MB
//     maxFiles: 5,
//     acceptedFiles: "image/*,.pdf",
//     addRemoveLinks: true,
//     clickable: '#student-dropzone',
//     previewsContainer: '#student-dropzone',
//     hiddenInputContainer: document.body,
//     headers: { "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content },
//     dictRemoveFile: 'Հեռացնել',
//   });

//   dzStud.on("sendingmultiple", function (files, xhr, formData) {
//     $('#StudentForm').serializeArray().forEach(({name, value}) => formData.append(name, value));
//     $('.text-danger').text('');
//     $('#studentBtn').prop('disabled', true);
//   });

//   dzStud.on("successmultiple", function (files, response) {
//     const res = (typeof response === 'string') ? JSON.parse(response) : response;

//     if (res && res.status === 1) {
//       (async () => {
//         await swal("success", "Գործողությունը կատարված է", true, true);
//         if (res.redirect) {
//           window.location.href = res.redirect;  
//         }
//       })();

//       formEl.reset();
//       dzStud.removeAllFiles(true);
//       $('#removed-files').empty();
//       $('#studentBtn').prop('disabled', true);
//     } else {
//       $('#studentBtn').prop('disabled', false);
//       swal("error", "Something went wrong!", true, true);
//     }
//   });


//   dzStud.on("errormultiple", function (files, xhrResp, xhr) {
//     $('#studentBtn').prop('disabled', false);
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
//     if (dzStud.getAcceptedFiles().length > 0) {
//       dzStud.processQueue();         
//     } else {
//       saveStudent();              
//     }
//   });
// });


$(function () {
  const dzElStud = document.getElementById('student-dropzone');
  const formEl   = document.getElementById('StudentForm');
  const saveBtn  = document.getElementById('studentBtn');

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
    clickable: '#student-dropzone',
    previewsContainer: '#student-dropzone',
    hiddenInputContainer: document.body,
    headers: { "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content },
    dictRemoveFile: 'Հեռացնել',
  });

  // dzStud.on("sendingmultiple", function (files, xhr, formData) {
  //   $('#StudentForm').serializeArray().forEach(({name, value}) => formData.append(name, value));
  //   $('.text-danger').text('');
  //   $('#studentBtn').prop('disabled', true);
  // });


  dzStud.on("sendingmultiple", function (files, xhr, formData) {
    $('#StudentForm').serializeArray().forEach(({ name, value }) => {
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
    $('#studentBtn').prop('disabled', true);
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
        $('#studentBtn').prop('disabled', true);
      })();
    } else {
      $('#studentBtn').prop('disabled', false);
      swal("error", "Something went wrong!", true, true);
    }
  });

  dzStud.on('error', function (file, errorMessage, xhr) {
    const msg = normalizeDzError(errorMessage, xhr);
    const el = file.previewElement?.querySelector('[data-dz-errormessage]');
    if (el) el.textContent = msg;
  });

  dzStud.on("errormultiple", function (files, errorMessage, xhr) {
    $('#studentBtn').prop('disabled', false);

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
      saveStudent();
    }
  });
});




$(document).on('click', '.js-mark-remove-st', function () {
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


// $('#yearpicker').datepicker({
//     format: "yyyy",
//     viewMode: "years",
//     minViewMode: "years",
//     autoclose: true
// });



$(document).off('click.payment', '#studentTbl .view-history').on('click.payment', '#studentTbl .view-history', function (e) {
      e.preventDefault();
      e.stopPropagation();
      const id  = $(this).data('id');
      const sid = $(this).data('school-id') ?? currentSchoolId();
      const base = `/admin/payment/student/${encodeURIComponent(id)}`;
      const url  = sid ? `${base}?school_id=${encodeURIComponent(sid)}` : base;
      window.location.assign(url);
});

$(document).ready(function(){
    $('#phone_1').mask('(000) 00-00-00');
    $('#phone_2').mask('(000) 00-00-00');
})