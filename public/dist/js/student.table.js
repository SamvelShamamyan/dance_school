$(function() {
    const money = n => Number(n || 0).toLocaleString('hy-AM', { maximumFractionDigits: 0 });

    function reloadBirthdayBlock() {
        const role = window.currentUserRole;

        const payload = {
            range_from: $('#range_from').val() || '',
            range_to:   $('#range_to').val()   || '',
        };

        if (role === 'super-admin') {
            payload.school_id = $('#filterStudentSchool').val() || '';
            payload.group_id  = $('#group_id').val() || '';
        } else if (role === 'school-admin') {
            payload.group_id = $('#studentHeaderFilter').find('#group_id').val() || '';
        }

        $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url: "/admin/student/birthdayBlock",
            type: "POST",
            data: payload,
            success: function(html) {
                $('#birthdayBlock').html(html);
            }
        });
    }

    let studentTbl = $("#studentTbl").DataTable({
        language: lang,
        processing: true,
        serverSide: true,
        searchDelay: 700,
        ajax: {
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url: "/admin/student/getData",
            type: 'post',
            data: function(d) {
                const rangeFrom = $('#range_from').val() || '';
                const rangeTo   = $('#range_to').val() || '';

                if (window.currentUserRole === 'super-admin') {
                    d.school_id  = $('#filterStudentSchool').val() || '';
                    d.group_id   = $('#group_id').val() || '';
                    d.range_from = rangeFrom;
                    d.range_to   = rangeTo;
                } else if (window.currentUserRole === 'school-admin') {
                    d.group_id   = $('#studentHeaderFilter').find('#group_id').val() || '';
                    d.range_from = rangeFrom;
                    d.range_to   = rangeTo;
                }
            }
        },
        order: [[0, 'desc']],
        columns: [
            { data: 'id', name: 'id' },
            { data: 'full_name', name: 'last_name' },
            { data: 'email', name: 'email' },
            { data: 'birth_date', name: 'birth_date', render: v => moment(v).format('DD.MM.YYYY') },
            { data: 'created_date', name: 'created_date', render: v => moment(v).format('DD.MM.YYYY') },
            { data: 'student_expected_payments', name: 'student_expected_payments', render: d => money(d) },
            { data: 'student_prepayment', name: 'student_prepayment', render: d => money(d) },
            { data: 'student_debts', name: 'student_debts', render: d => money(d) },
            { orderable: false, searchable: false, data: 'action', name: 'action' }
        ]
    });

    // ---------- filters ----------
    if (window.currentUserRole === 'super-admin') {

        $('#filterStudentSchool').on('change', function () {
            const name = $(this).find('option:selected').data('name');
            $('#currentSchoolTitle').text(name ? name : 'Բոլորը');

            studentTbl.ajax.reload(null, true);
            reloadBirthdayBlock();
        });

        $('#studentHeaderFilter').find('#group_id').on('change', function () {
            const name = $(this).find('option:selected').data('name');
            $('#currentSchoolTitle').text(name ? name : 'Բոլորը');

            studentTbl.ajax.reload(null, true);
            reloadBirthdayBlock();
        });
    }
    else if (window.currentUserRole === 'school-admin') {

        $('#studentHeaderFilter').find('#group_id').on('change', function () {
            const name = $(this).find('option:selected').data('name');
            $('#currentSchoolTitle').text(name ? name : 'Բոլորը');

            studentTbl.ajax.reload(null, true);
            reloadBirthdayBlock();
        });
    }

    // ✅ apply daterangepicker -> reload
    $('#filter_range_date').on('apply.daterangepicker', function() {
        if ($.fn.DataTable.isDataTable('#studentTbl')) {
            studentTbl.ajax.reload(null, true);
        }
        reloadBirthdayBlock();
    });

    // ✅ reset daterangepicker (из student.js) -> reload
    $(document)
        .off('student:dateRangeReset') 
        .on('student:dateRangeReset', function () {
            if ($.fn.DataTable.isDataTable('#studentTbl')) {
                studentTbl.ajax.reload(null, true);
            }
            reloadBirthdayBlock();
        });

    // first load
    reloadBirthdayBlock();
});