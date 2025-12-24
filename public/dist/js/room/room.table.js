$(function() {
    let roomTbl = $("#room").DataTable({
        language: lang,
        processing: true,
        serverSide: true,
        ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/admin/room/getData",
            type: 'post',
            data: function(d) {
                if (window.currentUserRole === 'super-admin') {d.school_id = $('#school_id').val() || '';}
            }
        },
        columns: [  
            {
                data: 'id',
                name: 'id'
            },
            {
                data: 'school_name',
                name: 'school_names.name'
            },
            {
                data: 'name',
                name: 'name'
            },
            {
                orderable: false,
                searchable: false,
                data: 'action',
                name: 'action',
            }
        ]
    });
    if (window.currentUserRole === 'super-admin') {
        $('#school_id').on('change', function () {
            const $select = $(this); 
            const name = $select.find('option:selected').data('name');
            $('#currentSchoolTitle').text(name ? name : 'Բոլորը');
            roomTbl.ajax.reload();
        });
    }
});

// $(document).on('click', '.btn-edit-room', function () {
//     let id = $(this).data('id');
//     if (id) {
//         window.location.href = `/admin/room/${id}/edit`;
//     }
// });

// $(document).on('click', '.btn-delete-room', function () {
//     let id = $(this).data('id');
//     let el = this;

//     Swal.fire({
//         title: "Դուք համոզված եք՞",
//         showDenyButton: true,
//         showCancelButton: true,
//         confirmButtonText: "Այո",
//         showCancelButton: false,
//         denyButtonText: `Ոչ`
//     }).then((result) => {
//         if (result.isConfirmed) {
//             $.ajax({
//                 headers: {
//                     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//                 },
//                 url: `/admin/room/${id}/delete`,
//                 cache: false,
//                 contentType: false,
//                 processData: false,
//                 type: 'POST',
//                 dataType: 'json',
//                 success: function (response) {
//                     if (response && response.status === -2) {
//                         swal("error", response.message);
//                     } else if (response && response.status === 1) {
//                        swal("success", "Գործողությունը կատարված է", true, true);
//                         $(el).closest('tr').remove();
//                     } else {
//                         Swal.fire("srror", "Չհաջողվեց ջնջել։", "error");
//                     }
//                 },
//                  error: function (xhr) {
//                     Swal.fire("Սխալ", "Խնդրում ենք կրկին փորձել։", "error");
//                     console.error(xhr.responseJSON);
//                 }
//             });
//         }
//     });
// });