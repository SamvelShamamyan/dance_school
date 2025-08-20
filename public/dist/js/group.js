function saveGroup() {
    const form = document.getElementById("GroupForm");
    const formData = new FormData(form);
    const url = form.getAttribute('action'); 
    $.ajax({        
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:  url,
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        type: 'POST',
        dataType: 'json',
        success: function(response) {

            if (response.status === 1) {
                swal("success", response.message, true, true);
                $('#GroupAddForm')[0].reset();
                $('.text-danger').text('');
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                $.each(errors, function(field, messages) {
                    $(`.error_${field}`).text(messages[0])
                });
            } else {
                swal("error", "Ինչ-որ բան այն չէ, կրկին փորձեք!", true, true)
            }
        }
    });
}

$(document).on('click', '.btn-edit-group', function () {
    let id = $(this).data('id');
    if (id) {
        window.location.href = `/admin/group/${id}/edit`;
    }
});


$(document).on('click', '.btn-delete-group', function () {
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
                url: `/admin/group/${id}/delete`,
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


// $(document).on('click', '#studentGroupModalBtn', function () {
    
//     let groupId = $(this).data('group-id');
//     $('#group_id').val(groupId);
//     let school_id = '';
//     $.ajax({
//         headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
//         url: `/admin/group/getStudents`,
//         type: 'POST',
//         data: {'school_id':school_id},
//         dataType: 'json',
//         success: function (response) {            

//             let $select = $('#addStudentsGroup');
//             $select.empty();

//             $.each(response, function (index, student) {
//                 $select.append(
//                     $('<option>', {
//                         value: student.id,
//                         text: student.full_name
//                     })
//                 );
//             });

//             $select.select2({
//                 theme: 'bootstrap4',
//                 placeholder: "Ընտրել",
//                 width: '100%'
//             });
            
//         }
//     });
// });


if (window.currentUserRole === 'super-admin') {

    $(document).on('click', '#studentGroupModalBtn', function () {        
        let groupId = $(this).data('group-id');
        let schoolId = $(this).data('school-id')
        $('#group_id').val(groupId);
        $('#school_id').val(schoolId);

        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: `/admin/group/getStudents`,
            type: 'POST',
            data: {'school_id':schoolId},
            dataType: 'json',
            success: function (response) {            

                let $select = $('#addStudentsGroup');
                $select.empty();

                $.each(response, function (index, student) {
                    $select.append(
                        $('<option>', {
                            value: student.id,
                            text: student.full_name
                        })
                    );
                });

                $select.select2({
                    theme: 'bootstrap4',
                    placeholder: "Ընտրել",
                    width: '100%'
                });
                
            }
        });
    });

    $(document).on('click', '#staffGroupModalBtn', function () {   
        let groupId = $(this).data('group-id');
        let schoolId = $(this).data('school-id');

        $('#school_id').val(schoolId);
        $('#group_staff_id').val(groupId);
    
        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: `/admin/group/getStaff`,
            type: 'POST',
            data: {'school_id':schoolId},
            dataType: 'json',
            success: function (response) {            

                let $select = $('#addStaffGroup');
                $select.empty();

                $.each(response, function (index, student) {
                    $select.append(
                        $('<option>', {
                            value: student.id,
                            text: student.full_name
                        })
                    );
                });

                $select.select2({
                    theme: 'bootstrap4',
                    placeholder: "Ընտրել",
                    width: '100%'
                });
                
            }
        });
    });
}else{

    $(document).on('click', '#studentGroupModalBtn', function () {
        
        let groupId = $(this).data('group-id');
        $('#group_id').val(groupId);
        let school_id = '';
        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: `/admin/group/getStudents`,
            type: 'POST',
            data: {'school_id':school_id},
            dataType: 'json',
            success: function (response) {            

                let $select = $('#addStudentsGroup');
                $select.empty();

                $.each(response, function (index, student) {
                    $select.append(
                        $('<option>', {
                            value: student.id,
                            text: student.full_name
                        })
                    );
                });

                $select.select2({
                    theme: 'bootstrap4',
                    placeholder: "Ընտրել",
                    width: '100%'
                });
                
            }
        });
    });

    $(document).on('click', '#staffGroupModalBtn', function () {
    
    let groupId = $(this).data('group-id');
    $('#group_staff_id').val(groupId);
  
    $.ajax({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        url: `/admin/group/getStaff`,
        type: 'POST',
        dataType: 'json',
        success: function (response) {            

            let $select = $('#addStaffGroup');
            $select.empty();

            $.each(response, function (index, student) {
                $select.append(
                    $('<option>', {
                        value: student.id,
                        text: student.full_name
                    })
                );
            });

            $select.select2({
                theme: 'bootstrap4',
                placeholder: "Ընտրել",
                width: '100%'
            });
            
        }
    });
});
}

function addStudentGroup() {
    const formData = new FormData(document.getElementById("SutdentGroupModalForm"));
    $.ajax({        
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:  '/admin/group/addStudenets',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        type: 'POST',
        dataType: 'json',
        success: function(response) {

            if (response.status === 1) {
                swal("success", response.message, true, true);
                $('#addStudentGroupBtn').prop('disabled', true)
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                $.each(errors, function(field, messages) {
                    $(`.error_${field}`).text(messages[0])
                });
                // swal("error", "Ստուգեք ներմուծված տվյալները", true, true);
            } else {
                swal("error", "Ինչ-որ բան այն չէ, կրկին փորձեք!", true, true)
            }
        }
    });
}

$(document).on('click', '.btn-delete-group-student', function () {
    let studentId = $(this).data('id-student');
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
                url: `/admin/group/student/${studentId}/delete`,
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

// $(document).on('click', '#staffGroupModalBtn', function () {
    
//     let groupId = $(this).data('group-id');
//     $('#group_staff_id').val(groupId);
  
//     $.ajax({
//         headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
//         url: `/admin/group/getStaff`,
//         type: 'POST',
//         dataType: 'json',
//         success: function (response) {            

//             let $select = $('#addStaffGroup');
//             $select.empty();

//             $.each(response, function (index, student) {
//                 $select.append(
//                     $('<option>', {
//                         value: student.id,
//                         text: student.full_name
//                     })
//                 );
//             });

//             $select.select2({
//                 theme: 'bootstrap4',
//                 placeholder: "Ընտրել",
//                 width: '100%'
//             });
            
//         }
//     });
// });


function addStaffGroup() {
    const $btn = $('#addStaffGroupBtn');
    const form = document.getElementById("StaffGroupModalForm");
    const formData = new FormData(form);

    $('.error_add_staff, .error_group_id').text('');
    $btn.prop('disabled', true);

    $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url: '/admin/group/addStaff',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        type: 'POST',
        dataType: 'json'
    })
    .done(function(response) {
        if (response.status === 1) {
            swal("success", response.message, true, true);
            $('#staffGroupModal').modal('hide');
            $('#addStaffGroup').val(null).trigger('change'); 
        }
        else if (response.status === 2) {
            const list = (response.already_names || []).join(', ');
            const text = list
                ? `Պահպանվեց ${response.added_count}. Այս աշխատակիցները արդեն կային խմբում՝ ${list}`
                : `Պահպանվեց ${response.added_count}. Որոշ աշխատակիցներ արդեն կային խմբում։`;
            swal("warning", text, true, true);

            const selected = $('#addStaffGroup').val() || [];
            const alreadyIds = (response.already_ids || []).map(String);
            const left = selected.filter(id => !alreadyIds.includes(id));
            $('#addStaffGroup').val(left).trigger('change');
        }
        else if (response.status === 3) {
            const list = (response.already_names || []).join(', ');
            const text = list
                ? `Այս աշխատակիցները արդեն կային խմբում՝ ${list}`
                : response.message || 'Ընտրված աշխատակիցները արդեն կային խմբում';
            swal("info", text, true, true);
        }
        else {
            swal("error", "Ինչ-որ բան այն չէ, կրկին փորձեք!", true, true);
        }
    })
    .fail(function(xhr) {
        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
            const errors = xhr.responseJSON.errors;
            $.each(errors, function(field, messages) {
                $(`.error_${field}`).text(messages[0]);
            });
            // swal("error", "Ստուգեք ներմուծված տվյալները", true, true);
        } else {
            swal("error", "Ինչ-որ բան այն չէ, կրկին փորձեք!", true, true);
        }
    })
    .always(function() {
        $btn.prop('disabled', false);
    });
}

$(document).on('click', '.btn-delete-group-staff', function () {
    let staffId = $(this).data('id-staff');
    let groupId = $(this).data('id-group');
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
                url: `/admin/group/staff/${staffId}/${groupId}/delete`,
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


$(document).on('click', '#studentGroupRepeatModalBtn', function () {  
    let studentId = $(this).data('id-student');
    $('#studentId').val(studentId);
});

function saveStudentRepeat() {
    const form = document.getElementById("singleRepeatForm");
    const formData = new FormData(form);
    $.ajax({        
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url:  `/admin/group/studentRepeat`,
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        type: 'POST',
        dataType: 'json',
        success: function(response) {

            if (response.status === 1) {
                swal("success", response.message, true, true);
                $('#singleRepeatForm')[0].reset();
                $('.text-danger').text('');
                $('#singleRepeatBtn').prop('disabled', true)
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                $.each(errors, function(field, messages) {
                    $(`.error_${field}`).text(messages[0])
                });
            } else {
                swal("error", "Ինչ-որ բան այն չէ, կրկին փորձեք!", true, true)
            }
        }
    });
}

// $(document).on('click', '#getGroupStudent', function () {
//     let groupId = $(this).data('group-id');
//     if (groupId) {
//         window.location.href = `/admin/group/${groupId}/students`;
//     }
// });

$(function () {
    $('.select2').select2({
        theme: 'bootstrap4'
        // theme: 'classic'
        
    });
});

$(function () {
  moment.locale('hy');

  const groupDate = $('#group_date').val();

  $('#groupDatePicker').datetimepicker({
    format: 'DD.MM.YYYY',
    locale: 'hy',
    showTodayButton: true,
    defaultDate: groupDate ? moment(groupDate, 'DD.MM.YYYY') : moment(),

  });
});

$('#studentGroupModal').on('hidden.bs.modal', function () {
  const form = $('#SutdentGroupModalForm')[0];
  if (form) form.reset();              
  $('#group_id').val('');   
  $('.text-danger').empty();    
  const $select = $('#addStudentsGroup');
  if ($select.hasClass('select2-hidden-accessible')) {
    $select.val(null).trigger('change');
  }
});

$('#staffGroupModal').on('hidden.bs.modal', function () {
  const form = $('#StaffGroupModalForm')[0];
  if (form) form.reset();              
  $('#group_id').val('');       
  $('.text-danger').empty();       
  const $select = $('#addStaffGroup');
  if ($select.hasClass('select2-hidden-accessible')) {
    $select.val(null).trigger('change');
  }
});

$('#studentGroupRepeatModal').on('hidden.bs.modal', function () {
  const form = $('#singleRepeatForm')[0];
  if (form) form.reset();              
  $('#student_id').val('');     
  $('.text-danger').empty();    
});