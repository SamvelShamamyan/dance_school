const lang = {
    "emptyTable": "Տվյալները բացակայում են",
    "processing": "Կատարվում է...",
    "infoThousands": ",",
    "lengthMenu": "Ցուցադրել _MENU_ արդյունքներ մեկ էջում",
    "loadingRecords": "Բեռնվում է ...",
    "zeroRecords": "Հարցմանը համապատասխանող արդյունքներ չկան",
    "info": "Ցուցադրված են _START_-ից _END_ արդյունքները ընդհանուր _TOTAL_-ից",
    "infoEmpty": "Արդյունքներ գտնված չեն",
    "infoFiltered": "(ֆիլտրվել է ընդհանուր _MAX_ արդյունքներից)",
    "search": "Փնտրել",
    "paginate": {
        "first": "Առաջին էջ",
        "previous": "Նախորդ էջ",
        "next": "Հաջորդ էջ",
        "last": "Վերջին էջ"
    },
    "aria": {
        "sortAscending": ": ակտիվացրեք աճման կարգով դասավորելու համար",
        "sortDescending": ": ակտիվացրեք նվազման կարգով դասավորելու համար"
    }
} ;

$(function() {
    let schoolNameTbl = $("#school_name").DataTable({
        language: lang,
        processing: true,
        serverSide: true,
        ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/admin/school/getData",
            type: 'post'
        },
        order: [[0, 'desc']],
        columns: [  
            {
                data: 'id',
                name: 'id',
                width: "10%"
            },
            {
                data: 'name',
                name: 'name',
                width: "43%"
            },
            {
                orderable: false,
                searchable: false,
                data: 'action',
                name: 'action',
                width: "33%"
            }
        ]
    });
});

$(function() {
    let userTbl = $("#userTbl").DataTable({
        language: lang,
        processing: true,
        serverSide: true,
        ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/admin/user/getData",
            type: 'post'
        },
        order: [[0, 'desc']],
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
                data: 'full_name',
                name: 'full_name'
            },
            {
                orderable: false,
                searchable: false,
                data: 'action',
                name: 'action',
            }
        ]
    });
});


$(function() {
    let groupTbl = $("#groupTbl").DataTable({
        language: lang,
        processing: true,
        serverSide: true,
        ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/admin/group/getData",
            type: 'post',
             data: function(d) {
                if (window.currentUserRole === 'super-admin') {d.school_id = $('#filterSchoolGroup').val() || '';}
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
        $('#filterSchoolGroup').on('change', function () {
            const $select = $(this); 
            const name = $select.find('option:selected').data('name');
            $('#currentSchoolTitle').text(name ? name : 'Բոլորը');
            groupTbl.ajax.reload();
        });
    }

});


$(function() {
    let staffTbl = $("#staffTbl").DataTable({
        language: lang,
        processing: true,
        serverSide: true,
        ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/admin/staff/getData",
            type: 'post',
            data: function(d) {
            if (window.currentUserRole === 'super-admin') {d.school_id = $('#filterSchool').val() || '';}
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
                name: 'school_names.name'
            },
            {
                data: 'full_name',
                name: 'last_name',
            },
            {
                data: 'address',
                name: 'address'
            },
            {
                data: 'birth_date',
                name: 'birth_date'
            },
            {
                data: 'email',
                name: 'email'
            },
            {
                data: 'soc_number',
                name: 'soc_number'
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
        $('#filterSchool').on('change', function () {
            const $select = $(this); 
            const name = $select.find('option:selected').data('name');
            $('#currentSchoolTitle').text(name ? name : 'Բոլորը');
            staffTbl.ajax.reload();
        });
    }
});


$(function() {
    const money = n => Number(n || 0).toLocaleString('hy-AM', { maximumFractionDigits: 0 });
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
                if (window.currentUserRole === 'super-admin') {d.school_id = $('#filterStudentSchool').val() || '';}
            }
        },
        order: [[0, 'desc']],
        columns: [  
            {
                data: 'id',
                name: 'id',
            },
            {
                data: 'full_name',
                name: 'last_name',
            },
            {
                data: 'email',
                name: 'email',
            },
            {
                data: 'birth_date',
                name: 'birth_date',
                render: v => moment(v).format('DD.MM.YYYY'),
            },
            {
                data: 'created_date',
                name: 'created_date',
                render: v => moment(v).format('DD.MM.YYYY'),
            },
            {
                data: 'student_expected_payments',
                name: 'student_expected_payments',
                render: function(data, type, row) {
                    return money(data);
                }
            },
            {
                data: 'student_prepayment',
                name: 'student_prepayment',
                render: function(data, type, row) {
                    return money(data);
                }
            },
            {
                data: 'student_debts',
                name: 'student_debts',
                render: function(data, type, row) {
                    return money(data);
                }
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
        $('#filterStudentSchool').on('change', function () {
            const $select = $(this); 
            const name = $select.find('option:selected').data('name');
            $('#currentSchoolTitle').text(name ? name : 'Բոլորը');
            studentTbl.ajax.reload();
        });
    }
});


$(function() {
    const groupId = $("#studenetsListTbl").data('id');
    const school_id = $("#studenetsListTbl").data('school-id');

    let studenetsListTblUrl = school_id
        ? `/admin/group/${groupId}/getStudenetsList?school_id=${school_id}`
        : `/admin/group/${groupId}/getStudenetsList`;

    let studenetsListTbl = $("#studenetsListTbl").DataTable({
        language: lang,
        processing: true,
        serverSide: true,
        ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: studenetsListTblUrl,
            type: 'post'
        },
        columns: [  
            {
                data: 'id',
                name: 'id',
            },
            {
                data: 'full_name',
                name: 'last_name',
                orderable: false,
                searchable: false,
            },
            {
                data: 'email',
                name: 'email',
            },
            {
                data: 'birth_date',
                name: 'birth_date',
                render: v => moment(v).format('DD.MM.YYYY')
            },
            {
                data: 'created_date',
                name: 'created_date',
                render: v => moment(v).format('DD.MM.YYYY')
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
            }
            
        ]
    });
});

$(function() {
    const groupId = $("#staffListTbl").data('id');
    const school_id = $("#staffListTbl").data('school-id');

    let staffListTblUrl = school_id
        ? `/admin/group/${groupId}/getStaffList?school_id=${school_id}`
        : `/admin/group/${groupId}/getStaffList`;

    let staffListTbl = $("#staffListTbl").DataTable({
        language: lang,
        processing: true,
        serverSide: true,
        ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: staffListTblUrl,
            type: 'post'
        },
        columns: [  
            {
                data: 'id',
                name: 'id'
            },
            {
                data: 'full_name',
                name: 'last_name',
                orderable: false,
                searchable: false,
            },
            {
                data: 'address',
                name: 'address'
            },
            {
                data: 'birth_date',
                name: 'birth_date'
            },
            // {
            //     data: 'created_date',
            //     name: 'created_date'
            // },
            {
                data: 'email',
                name: 'email'
            },
            {
                data: 'soc_number',
                name: 'soc_number'
            },
            // {
            //     data: 'school_name',
            //     name: 'school_names.name'
            // },
            {
                orderable: false,
                searchable: false,
                data: 'action',
                name: 'action',
            }
        ]
    });
});


$(function () {
  const $tbl = $('#studentGroupHistoryTbl');
  const studentId = $tbl.data('student-id');

  const historyTbl = $tbl.DataTable({
    language: lang,
    processing: true,
    serverSide: true,
    searching: true,
    responsive: true,
    autoWidth: false,
    ajax: {
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url: `/admin/student/${studentId}/groupHistory`,
      type: 'post'
    },
    columns: [
      { data: 'id',             name: 'id', width: '60px' },
      { data: 'transition',     name: 'transition', orderable: false },
      { data: 'period',         name: 'period', orderable: false, searchable: false },
      { data: 'is_last',        name: 'is_last', width: '70px' },
    ],
    order: [[0, 'desc']],
  });
});

// === Payments page ===
// $(function () {
//   if (!$('#paymentTbl').length) return;
//   const monthShortHY = ['Հուն','Փետ','Մար','Ապր','Մայ','Հուն','Հուլ','Օգս','Սեպ','Հոկ','Նոյ','Դեկ'];
//   const money = n => Number(n || 0).toLocaleString('hy-AM', { maximumFractionDigits: 0 });
//   const renderMonth = v => (Number(v || 0) === 0 ? '<span class="text-muted">0</span>' : money(v));

//   function renderSummary(arr){
//     const wrap = document.getElementById('summary');
//     if (!wrap) return;
//     wrap.innerHTML = (arr || []).map((sum, i) => `
//       <div class="col-6 col-sm-4 col-md-2 col-lg-1 mb-2">
//         <div class="card p-2 text-center">
//           <div class="text-muted small">${monthShortHY[i] || ''}</div>
//           <div class="sum" style="font-weight:700">${money(sum)}</div>
//         </div>
//       </div>
//     `).join('');
//   }

//   let paymentTbl = null;

//   function initPaymentTable(){
    
//     if (paymentTbl) return; 

//     paymentTbl = $("#paymentTbl").DataTable({
//       language: lang,
//       processing: true,
//       serverSide: true,
//       ajax: {
//         headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
//         url: `/admin/payment/getData`,
//         type: 'post',
//         data: function(d) {
//           d.year     = $('#year').val();
//           d.group_id = $('#group_id').val();
//           d.method   = $('#method').val();
//           d.status   = $('#status').val();

//           if (window.currentUserRole === 'super-admin' || window.currentUserRole === 'super-accountant') {
//                 d.school_id = $('#school_id').val();
//             }

//         }
//       },
//       columns: [
//         { 
//             data: 'full_name', 
//             name: 'full_name',
//             render: (v, t, row) => {
//             if (row.is_deleted) {
//                 return `${v} <span class="badge badge-danger ml-2">Հեռացված</span>`;
//             }
//             return v;
//             }
//         },
//         { data: 'm01', className: 'text-end', render: renderMonth },
//         { data: 'm02', className: 'text-end', render: renderMonth },
//         { data: 'm03', className: 'text-end', render: renderMonth },
//         { data: 'm04', className: 'text-end', render: renderMonth },
//         { data: 'm05', className: 'text-end', render: renderMonth },
//         { data: 'm06', className: 'text-end', render: renderMonth },
//         { data: 'm07', className: 'text-end', render: renderMonth },
//         { data: 'm08', className: 'text-end', render: renderMonth },
//         { data: 'm09', className: 'text-end', render: renderMonth },
//         { data: 'm10', className: 'text-end', render: renderMonth },
//         { data: 'm11', className: 'text-end', render: renderMonth },
//         { data: 'm12', className: 'text-end', render: renderMonth },
//         { data: 'total', className: 'text-end font-weight-bold', render: v => money(v) },
//         {
//           data: 'id', orderable: false, searchable: false, className: 'text-center',
//           render: (id, t, row) => `<button class="btn btn-sm btn-light view-history" data-id="${id}" data-school-id="${row.school_id ?? ''}" data-name="${row.full_name}" title="Պատմություն">&#8942;</button>`
//         }
//       ],
//       createdRow: function(row, data){
//         for (let m = 1; m <= 12; m++){
//           const key = 'm' + String(m).padStart(2,'0');
//           const val = Number(data[key] || 0);
//           const $td = $('td', row).eq(m);
//           if (val > 0) $td.addClass('table-success'); else $td.removeClass('table-success');
//         }
//       }
//     });

//     $('#paymentTbl').on('xhr.dt', function (e, settings, json) {
//       if (json && Array.isArray(json.summary)) renderSummary(json.summary);
//     });

//     $('#btnRefresh, #year, #group_id, #status, #method, #filter_range_date').on('change click', function(){
//       if ($.fn.DataTable.isDataTable('#paymentTbl')) {
//         paymentTbl.ajax.reload();
//       }
//     });
//   }

//   $(document).one('payment:filtersLoaded', initPaymentTable);

//   if ($('#year option').length) initPaymentTable();
// });

$(function () {
  if (!$('#paymentTbl').length) return;

  const monthShortHY = ['Հնվ','Փտր','Մրտ','Ապր','Մայ','Հնս','Հլս','Օգս','Սեպ','Հոկ','Նոյ','Դեկ'];
  const academicOrderNums = [9,10,11,12,1,2,3,4,5,6,7,8]; 
  const academicKeys   = academicOrderNums.map(n => 'm' + String(n).padStart(2,'0'));
  const academicTitles = academicOrderNums.map(n => monthShortHY[(n-1+12)%12]);

  const toAcademicOrder = (arr = []) => academicOrderNums.map(n => arr[n - 1] ?? 0);
 

  const money = n => Number(n || 0).toLocaleString('hy-AM', { maximumFractionDigits: 0 });
  const renderMonth = v => (Number(v || 0) === 0 ? '<span class="text-muted">0</span>' : money(v));

  (function rebuildThead(){
    const $thead = $('#paymentTbl thead');
    if (!$thead.length) return;
    let ths = `<tr><th>Անուն Ազգանուն</th>`;
    academicTitles.forEach(t => ths += `<th class="text-end">${t}</th>`);
    ths += `<th class="text-end">Ընդամենը</th><th class="text-center">Գործողություն</th></tr>`;
    $thead.html(ths);
  })();

  function renderSummary(arr){
    const wrap = document.getElementById('summary');
    if (!wrap) return;
    const sums = toAcademicOrder(arr);
    wrap.innerHTML = sums.map((sum, i) => `
      <div class="col-6 col-sm-4 col-md-2 col-lg-1 mb-2">
        <div class="card p-2 text-center">
          <div class="text-muted small">${academicTitles[i]}</div>
          <div class="sum" style="font-weight:700">${money(sum)}</div>
        </div>
      </div>
    `).join('');
  }

  let paymentTbl = null;

  function initPaymentTable(){
    if (paymentTbl) return;

    const monthCols = academicKeys.map(k => ({ data: k, className: 'text-end', render: renderMonth }));

    const columns = [
      {
        data: 'full_name',
        name: 'full_name',
        render: (v, t, row) => row.is_deleted ? `${v} <span class="badge badge-danger ml-2">Հեռացված</span>` : v
      },
      ...monthCols,
      { data: 'total', className: 'text-end font-weight-bold', render: v => money(v) },
      {
        data: 'id', orderable: false, searchable: false, className: 'text-center',
        render: (id, t, row) => `<button class="btn btn-sm btn-light view-history" data-id="${id}" data-school-id="${row.school_id ?? ''}" data-name="${row.full_name}" title="Պատմություն">&#8942;</button>`
      }
    ];

    paymentTbl = $("#paymentTbl").DataTable({
      language: lang,
      processing: true,
      serverSide: true,
      ajax: {
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url: `/admin/payment/getData`,
        type: 'post',
        data: function(d) {
          d.year       = $('#year').val();
          d.group_id   = $('#group_id').val();
          d.method     = $('#method').val();
          d.status     = $('#status').val();
          d.range_from = $('#range_from').val(); 
          d.range_to   = $('#range_to').val();  
          if (window.currentUserRole === 'super-admin' || window.currentUserRole === 'super-accountant') {
            d.school_id = $('#school_id').val();
          }
        }
      },
      columns
    });

    $('#paymentTbl').on('draw.dt', function(){
      $('#paymentTbl tbody tr').each(function(){
        const $tds = $('td', this);
        academicKeys.forEach((key, i) => {
          const $td = $tds.eq(1 + i);
          const rowData = paymentTbl.row(this).data() || {};
          const val = Number(rowData[key] || 0);
          if (val > 0) $td.addClass('table-success'); else $td.removeClass('table-success');
        });
      });
    });

    $('#paymentTbl').on('xhr.dt', function (e, settings, json) {
      if (json?.summary) renderSummary(json.summary);

      const p = json?.meta?.period;
      if (p) {
        $('#academicBadge').remove();
        const title = (p.label === 'custom') ? 'Ընտրված' : p.label;
        const html = `<span id="academicBadge" class="badge badge-info ml-2 mt-2">
          ${title} • ${p.from}–${p.to}
        </span>`;
        $('#paymentTbl_wrapper .dataTables_length').append(html);
      }
    });

    $('#btnRefresh, #year, #group_id, #status, #method, #school_id').on('change click', function(){
      if ($.fn.DataTable.isDataTable('#paymentTbl')) paymentTbl.ajax.reload();
    });
  }

  $(document).one('payment:filtersLoaded', initPaymentTable);
  if ($('#year option').length) initPaymentTable();

  $('#filter_range_date').on('apply.daterangepicker', function(ev, picker) {
    if ($.fn.DataTable.isDataTable('#paymentTbl')) paymentTbl.ajax.reload();
  });

});




// === Student payments DataTable ===
$(function () {
  if (!$('#studentPaymentTbl').length) return;

  const monthShortHY = ['Հուն','Փետ','Մար','Ապր','Մայ','Հուն','Հուլ','Օգս','Սեպ','Հոկ','Նոյ','Դեկ'];
  const money = n => Number(n || 0).toLocaleString('hy-AM', { maximumFractionDigits: 0 });
  const statusMap = { paid:'Վճարված', pending:'Սպասման մեջ', refunded:'Վերադարձված', failed:'Սխալ' };
  const methodMap = { cash:'Կանխիկ', card:'Անկանխիկ', online:'Առցանց' };


  const academicOrderNums = [9,10,11,12,1,2,3,4,5,6,7,8]; 
  const academicKeys   = academicOrderNums.map(n => 'm' + String(n).padStart(2,'0'));
  const academicTitles = academicOrderNums.map(n => monthShortHY[(n-1+12)%12]);

  const toAcademicOrder = (arr = []) => academicOrderNums.map(n => arr[n - 1] ?? 0);

  // function renderSummary(arr){
  //   const wrap = document.getElementById('summary');
  //   if (wrap) {
  //       wrap.innerHTML = (arr || []).map((sum, i) => `
  //       <div class="col-6 col-sm-4 col-md-2 col-lg-1 mb-2">
  //           <div class="card p-2 text-center">
  //           <div class="text-muted small">${monthShortHY[i] || ''}</div>
  //           <div class="sum" style="font-weight:700">${money(sum)}</div>
  //           </div>
  //       </div>
  //       `).join('');
  //   }

  //   const total = (arr || []).reduce((a, b) => a + Number(b || 0), 0);
  //   $('#tfootTotal').text(money(total));
  // }

    function renderSummary(arr){
      const wrap = document.getElementById('summary');
      if (!wrap) return;
      const sums = toAcademicOrder(arr);
      wrap.innerHTML = sums.map((sum, i) => `
        <div class="col-6 col-sm-4 col-md-2 col-lg-1 mb-2">
          <div class="card p-2 text-center">
            <div class="text-muted small">${academicTitles[i]}</div>
            <div class="sum" style="font-weight:700">${money(sum)}</div>
          </div>
        </div>
      `).join('');
      
      const total = (arr || []).reduce((a, b) => a + Number(b || 0), 0);
      $('#tfootTotal').text(money(total));
      
    }

    const $tbl = $('#studentPaymentTbl');
    const STUDENT_ID = $tbl.data('student-id');
    const SCHOOLID = $tbl.data('school-id');
    const moreUrl = (SCHOOLID)
    ? `/admin/payment/student/${STUDENT_ID}/data?school_id=${SCHOOLID}`
    : `/admin/payment/student/${STUDENT_ID}/data`;


  let studentPaymentTbl = null;

  function initStudentPaymentTable(){
    if (studentPaymentTbl) return;

    studentPaymentTbl = $("#studentPaymentTbl").DataTable({
      language: lang,
      processing: true,
      serverSide: true,
      ajax: {
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url: moreUrl,
        type: 'post',
        data: function(d) {
          d.year   = $('#year').val();
          d.method = $('#method').val();
          d.status = $('#status').val();
          d.range_from = $('#range_from').val(); 
          d.range_to   = $('#range_to').val();  
        }
      },
      columns: [
        { data: 'paid_at', render: v => moment(v).format('DD.MM.YYYY') },
        { data: 'amount', className: 'text-end', render: v => money(v) },
        { data: 'method', render: v => methodMap[v] || v || '' },
        { data: 'status', render: v => {
            const label = statusMap[v] || v || '';
            const cls = v==='paid' ? 'success' : (v==='pending' ? 'warning' : (v==='failed' ? 'danger' : 'secondary'));
            return `<span class="badge badge-${cls}">${label}</span>`;
          }
        },
        { data: 'comment', defaultContent: '' },
        {
          data: 'id', orderable: false, searchable: false, className: 'text-center',
          render: (id, t, row) => `
            <button class="btn btn-sm btn-outline-primary act-edit"
                    data-id="${id}"
                    data-paid="${moment(row.paid_at).format('DD.MM.YYYY')}"
                    data-amount="${row.amount}"
                    data-method="${row.method}"
                    data-status="${row.status}"
                    data-comment="${(row.comment||'').replace(/"/g,'&quot;')}" disabled>
              <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger act-del" data-id="${id}" disabled>
              <i class="fas fa-trash"></i>
            </button>
            <button class="btn btn-sm btn-outline-warning act-notification" title="Ուղարկել նամեկ" data-id="${id}">
              <i class="fas fa-envelope"></i>
            </button>`
        }
      ]
    });

    $('#studentPaymentTbl').on('xhr.dt', function (e, settings, json) {
      if (json?.summary) renderSummary(json.summary);

      const p = json?.meta?.period;
      if (p) {
        $('#academicBadge').remove();
        const title = (p.label === 'custom') ? 'Ընտրված' : p.label;
        const html = `<span id="academicBadge" class="badge badge-info ml-2 mt-2">
          ${title} • ${p.from}–${p.to}
        </span>`;
        $('#studentPaymentTbl_wrapper .dataTables_length').append(html);
      }
    });


    $('#studentPaymentTbl').on('xhr.dt', function (_e, _settings, json) {
      if (json && Array.isArray(json.summary)) renderSummary(json.summary);
    });

    $('#btnRefresh, #year, #status, #method').on('change click', function(){
      if ($.fn.DataTable.isDataTable('#studentPaymentTbl')) {
        studentPaymentTbl.ajax.reload();
      }
    });
  }

  $(document).one('payment:filtersLoaded', initStudentPaymentTable);
  if ($('#year option').length) initStudentPaymentTable();

  $('#student_payment_filter_range_date').on('apply.daterangepicker', function(ev, picker) {
    if ($.fn.DataTable.isDataTable('#studentPaymentTbl')) studentPaymentTbl.ajax.reload();
  });
});


// === Debts page ===
$(function () {
  if (!$('#debtTbl').length) return;

  const money = n => Number(n || 0).toLocaleString('hy-AM', { maximumFractionDigits: 0 });
  const renderZeroAsMuted = v => (Number(v || 0) === 0 ? '<span class="text-muted">0</span>' : money(v));

  let debtTbl = null;

  function initDebtTable(){
    if (debtTbl) return;

    const ajaxCfg = {
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url: `/admin/debts/getData`,
      type: 'post',
      data: function(d) {
        d.year     = $('#year').val();
        d.group_id = $('#group_id').val();
        // d.status   = $('#status').val();

        // const status = $('#status').val();
        // if (status !== '') d.status = status;

        if (window.currentUserRole === 'super-admin' || window.currentUserRole === 'super-accountant') {
            d.school_id = $('#school_id').val();
        }
      }
    };

    const cols = [{ 
      data: 'full_name', 
      name: 'full_name',
      render: (v, _t, row) => {
        if (row.deleted) {
          return `${v} <span class="badge bg-danger ms-1 badge-deleted">Հեռացված</span>`;
        }
        return v;
      } 
    }];
    for (let m = 1; m <= 12; m++) {
      const s = String(m).padStart(2,'0');
      cols.push(
        { data: 'due_m'  + s, className: 'text-end', render: renderZeroAsMuted },
        { data: 'paid_m' + s, className: 'text-end', render: renderZeroAsMuted },
        { data: 'rem_m'  + s, className: 'text-end',
          render: v => {
            const n = Number(v || 0);
            const cls = n > 0 ? 'text-danger' : 'text-success';
            return `<span class="${cls}">${money(n)}</span>`;
          }
        },
      );
    }
    cols.push(
      { data: 'total_due',  className: 'text-end', render: v => money(v) },
      { data: 'total_paid', className: 'text-end', render: v => money(v) },
      { data: 'total_rem',  className: 'text-end', render: v => `<b>${money(v || 0)}</b>` }
    );
    cols.push({
      data: 'id', orderable: false, searchable: false, className: 'text-center',
      render: (id, t, row) => `<button class="btn btn-sm btn-light view-history" data-id="${id}" data-school-id="${row.school_id ?? ''}" title="Պատմություն">&#8942;</button>`
    });

    debtTbl = $("#debtTbl").DataTable({
      language: lang,
      processing: true,
      serverSide: true,
      ajax: ajaxCfg,
      columns: cols,
      order: [[0, 'asc']],
       createdRow: function (row, data) {
        if (data.deleted) {
          $(row).addClass('is-deleted');
        }
      }
    });

    // $('#debtTbl').off('xhr.dt.main').on('xhr.dt.main', function(){  });

    $('#btnRefresh, #year, #group_id, #status').off('change.debtsMain click.debtsMain').on('change.debtsMain click.debtsMain', function(){
      if ($.fn.DataTable.isDataTable('#debtTbl')) {
        $('#debtTbl').DataTable().ajax.reload();
      }
    });
  }

  $(document).one('debts:filtersLoaded', initDebtTable);

  if ($('#year option').length) initDebtTable();
});



$(function() {
    let deletedStudentTbl = $("#deletedStudentTbl").DataTable({
        language: lang,
        processing: true,
        serverSide: true,
        searchDelay: 700, 
        ajax: {
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url: "/admin/deletedStudent/getData",
            type: 'post',
            data: function(d) {
                if (window.currentUserRole === 'super-admin') {d.school_id = $('#filterStudentSchool').val() || '';}
            }
        },
        columns: [  
            {
              data: 'id',
              name: 'id',
            },
            {
              data: 'full_name',
              name: 'last_name',
            },
            {
              data: 'address',
              name: 'address'
            },
            {
              data: 'birth_date',
              name: 'birth_date',
            },
            {
              data: 'email',
              name: 'email',
            },
            {
              data: 'phone_1',
              name: 'phone_1',
              render: function (data, type, row) {
                  return formatPhone(data);
              }
            },
            {
              data: 'phone_2',
              name: 'phone_2',
              render: function (data, type, row) {
                  return formatPhone(data);
              }
            },
            {
              data: 'soc_number',
              name: 'soc_number'
            },
            // {
            //     data: 'school_name',
            //     name: 'school_names.name'
            // },
            {
              data: 'action',
              name: 'action',
              orderable: false,
              searchable: false,
            }
        ]  
    });
    if (window.currentUserRole === 'super-admin') {
        $('#filterStudentSchool').on('change', function () {
            const $select = $(this); 
            const name = $select.find('option:selected').data('name');
            $('#currentSchoolTitle').text(name ? name : 'Բոլորը');
            deletedStudentTbl.ajax.reload();
        });
    }
});
