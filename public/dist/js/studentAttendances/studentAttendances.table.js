$(function() {
    let studentAttendancesTbl = $("#studentAttendances").DataTable({
        language: lang,
        processing: true,
        serverSide: true,
        ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/admin/studentAttendances/getData",
            type: 'post',
            data: function(d) {
                if (window.currentUserRole === 'super-admin') {
                    d.school_id = $('#studentAttendancesFilter').find('#school_id').val() || '';
                    d.group_id = $('#studentAttendancesFilter').find('#group_id').val() || '';
                }
            }
        },
        order: [[0, 'desc']],
        columns: [ 
            {
                data: 'id',
                name: 'id'
            },
            {
                data: 'school_name',
                name: 'school_name',
                orderable: false,
                searchable: false,
            },
            {
                data: 'group_name',
                name: 'groups.name',
                orderable: false,
                searchable: false,
            },
            {
                data: 'room_name',
                name: 'rooms.name',
                orderable: false,
                searchable: false,
            },
            {
                data: 'week_day',
                name: 'week_day',
                orderable: false,
                searchable: false,
            },
            {
                data: 'start_time',
                name: 'start_time',
                orderable: false,
                searchable: false,
            },
            {
                data: 'end_time',
                name: 'end_time',
                orderable: false,
                searchable: false,
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
            }
        ]
    });
    if (window.currentUserRole === 'super-admin') {
        $('#studentAttendancesFilter #school_id').on('change', function () {
            const name = $(this).find('option:selected').data('name');
            $('#currentSchoolTitle').text(name ? name : 'Բոլորը');
            studentAttendancesTbl.ajax.reload();
        });

        $('#studentAttendancesFilter #group_id').on('change', function () {
            const name = $(this).find('option:selected').data('name');
            $('#currentSchoolTitle').text(name ? name : 'Բոլորը');
            studentAttendancesTbl.ajax.reload();
        });
    }
});

$(document).on('click', '.btn-edit-room', function () {
    let id = $(this).data('id');
    if (id) {
        window.location.href = `/admin/room/${id}/edit`;
    }
});

$(document).on('click', '.btn-delete-room', function () {
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
                url: `/admin/room/${id}/delete`,
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


$('#studentAttendancesFilter #school_id').on('change', function(){
    const schoolId = $(this).val();

    $select = $('#studentAttendancesFilter').find('#group_id');

    if (!schoolId) {
      $select.prop('disabled', true).empty().append('<option value="">Բոլորը</option>');
      return;
    }

    $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url:  `/admin/scheduleGroup/getGroupsRoomsBySchool/${schoolId}`,
      type: 'GET',
      dataType: 'json',
      success: function(response) {
        $select.prop('disabled',false);
        $select.empty().append('<option value="">Բոլորը</option>');
        $.each(response.groups, function (index, group) {
          $select.append($('<option>', { value: group.id, text: group.name }));
        });
      },
      error: function() {
        swal("error", "Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։", "error");
      },
    });
});


$(document).on('click', '.btn-check-attendances', function () {
    let id = $(this).data('id');
    if (id) {
        window.location.href = `/admin/studentAttendances/${id}/check`;
    }
});

