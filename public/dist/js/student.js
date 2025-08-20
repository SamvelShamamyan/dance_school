function saveStudent() {
    const form = document.getElementById("StudentForm");
    const formData = new FormData(form);
    const url = form.getAttribute('action'); 

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
        success: function(response) {

            if (response.status === 1) {
                swal("success", "Գործողությունը կատարված է", true, true);
                $('#StudentForm')[0].reset();
                $('.text-danger').text('');
                $('#studentBtn').prop('disabled', true)
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
  const studentDate = $('#student_date').val();

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


$(function () {
  const dzElStud   = document.getElementById('student-dropzone');
  const formEl = document.getElementById('StudentForm');
  const saveBtn= document.getElementById('studentBtn');

  if (!dzElStud || !formEl || !saveBtn) return;
  if (typeof Dropzone === 'undefined') { console.error('Dropzone not loaded'); return; }

  const dzStud = new Dropzone(dzElStud, {
    url: formEl.action,
    paramName: "files",           
    uploadMultiple: true,
    parallelUploads: 5,
    autoProcessQueue: false,
    maxFilesize: 10,               // MB
    maxFiles: 5,
    acceptedFiles: "image/*,.pdf",
    addRemoveLinks: true,
    clickable: '#student-dropzone',
    previewsContainer: '#student-dropzone',
    hiddenInputContainer: document.body,
    headers: { "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content },
    dictRemoveFile: 'Հեռացնել',
  });

  dzStud.on("sendingmultiple", function (files, xhr, formData) {
    $('#StudentForm').serializeArray().forEach(({name, value}) => formData.append(name, value));
    $('.text-danger').text('');
    $('#studentBtn').prop('disabled', true);
  });

  dzStud.on("successmultiple", function (files, response) {
    if (response && response.status === 1) {
      swal("success", "Գործողությունը կատարված է", true, true);
      formEl.reset();
      dzStud.removeAllFiles(true);
      $('#removed-files').empty(); 
      $('#studentBtn').prop('disabled', true);
    } else {
      $('#studentBtn').prop('disabled', false);
      swal("error", "Something went wrong!", true, true);
    }
  });

  dzStud.on("errormultiple", function (files, xhrResp, xhr) {
    $('#studentBtn').prop('disabled', false);
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


// if (window.currentUserRole === 'super-admin') {

//   $(document).on('change','#schoolIdStudFilter', function(){

//     let schoolId = $(this).val();

//     $.ajax({
//       url: `/admin/payment/getGroupsBySchool/${schoolId}`,
//       type: 'GET',
//       success: function (groups) {
//         const $group = $('#group_id');
//         $group.empty().append('<option value="">Բոլորը</option>');

//         groups.forEach(group => {
//           $group.append(`<option value="${group.id}">${group.name}</option>`);
//         });

//         $group.prop('disabled',false)
//       }
//     });

//   });


// }