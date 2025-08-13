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
        columns: [  
            {
                data: 'id',
                name: 'id'
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
        columns: [  
            {
                data: 'id',
                name: 'id'
            },
            {
                data: 'first_name',
                name: 'first_name'
            },
            {
                data: 'last_name',
                name: 'last_name'
            },
            {
                data: 'father_name',
                name: 'father_name'
            },
            {
                data: 'school_name',
                name: 'school_names.name'
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
            type: 'post'
        },
        columns: [  
            {
                data: 'id',
                name: 'id'
            },
            {
                data: 'name',
                name: 'name'
            },
            {
                data: 'school_name',
                name: 'school_names.name'
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
    let staffTbl = $("#staffTbl").DataTable({
        language: lang,
        processing: true,
        serverSide: true,
        ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/admin/staff/getData",
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


$(function() {
    let studentTbl = $("#studentTbl").DataTable({
        language: lang,
        processing: true,
        serverSide: true,

        searchDelay: 700, 

        ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/admin/student/getData",
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


$(function() {
    const groupId = $("#studenetsListTbl").data('id');
    let studenetsListTbl = $("#studenetsListTbl").DataTable({
        language: lang,
        processing: true,
        serverSide: true,
        ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: `/admin/group/${groupId}/getStudenetsList`,
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

$(function() {
    const groupId = $("#staffListTbl").data('id');
    let staffListTbl = $("#staffListTbl").DataTable({
        language: lang,
        processing: true,
        serverSide: true,
        ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: `/admin/group/${groupId}/getStaffList`,
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

// === Payments page ===
$(function () {
  if (!$('#paymentTbl').length) return;

  const monthShortHY = ['Հուն','Փետ','Մար','Ապր','Մայ','Հուն','Հուլ','Օգս','Սեպ','Հոկ','Նոյ','Դեկ'];
  const money = n => Number(n || 0).toLocaleString('hy-AM', { maximumFractionDigits: 0 });
  const renderMonth = v => (Number(v || 0) === 0 ? '<span class="text-muted">0</span>' : money(v));

  function renderSummary(arr){
    const wrap = document.getElementById('summary');
    if (!wrap) return;
    wrap.innerHTML = (arr || []).map((sum, i) => `
      <div class="col-6 col-sm-4 col-md-2 col-lg-1 mb-2">
        <div class="card p-2 text-center">
          <div class="text-muted small">${monthShortHY[i] || ''}</div>
          <div class="sum" style="font-weight:700">${money(sum)}</div>
        </div>
      </div>
    `).join('');
  }

  let paymentTbl = null;

  function initPaymentTable(){
    if (paymentTbl) return; 

    paymentTbl = $("#paymentTbl").DataTable({
      language: lang,
      processing: true,
      serverSide: true,
      ajax: {
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url: `/admin/payment/getData`,
        type: 'post',
        data: function(d) {
          d.year     = $('#year').val();
          d.group_id = $('#group_id').val();
          d.status   = $('#status').val();
        }
      },
      columns: [
        { data: 'full_name', name: 'full_name' },
        { data: 'm01', className: 'text-end', render: renderMonth },
        { data: 'm02', className: 'text-end', render: renderMonth },
        { data: 'm03', className: 'text-end', render: renderMonth },
        { data: 'm04', className: 'text-end', render: renderMonth },
        { data: 'm05', className: 'text-end', render: renderMonth },
        { data: 'm06', className: 'text-end', render: renderMonth },
        { data: 'm07', className: 'text-end', render: renderMonth },
        { data: 'm08', className: 'text-end', render: renderMonth },
        { data: 'm09', className: 'text-end', render: renderMonth },
        { data: 'm10', className: 'text-end', render: renderMonth },
        { data: 'm11', className: 'text-end', render: renderMonth },
        { data: 'm12', className: 'text-end', render: renderMonth },
        { data: 'total', className: 'text-end font-weight-bold', render: v => money(v) },
        {
          data: 'id', orderable: false, searchable: false, className: 'text-center',
          render: (id, t, row) => `<button class="btn btn-sm btn-light view-history" data-id="${id}" data-name="${row.full_name}" title="Պատմություն">&#8942;</button>`
        }
      ],
      createdRow: function(row, data){
        for (let m = 1; m <= 12; m++){
          const key = 'm' + String(m).padStart(2,'0');
          const val = Number(data[key] || 0);
          const $td = $('td', row).eq(m);
          if (val > 0) $td.addClass('table-success'); else $td.removeClass('table-success');
        }
      }
    });

    $('#paymentTbl').on('xhr.dt', function (e, settings, json) {
      if (json && Array.isArray(json.summary)) renderSummary(json.summary);
    });

    $('#btnRefresh, #year, #group_id, #status').on('change click', function(){
      if ($.fn.DataTable.isDataTable('#paymentTbl')) {
        paymentTbl.ajax.reload();
      }
    });
  }

  $(document).one('payment:filtersLoaded', initPaymentTable);

  if ($('#year option').length) initPaymentTable();
});


//

// === Student payments DataTable ===
$(function () {
  if (!$('#studentPaymentTbl').length) return;

  const monthShortHY = ['Հուն','Փետ','Մար','Ապր','Մայ','Հուն','Հուլ','Օգս','Սեպ','Հոկ','Նոյ','Դեկ'];
  const money = n => Number(n || 0).toLocaleString('hy-AM', { maximumFractionDigits: 0 });
  const statusMap = { paid:'Վճարված', pending:'Սպասման մեջ', refunded:'Վերադարձված', failed:'Սխալ' };
  const methodMap = { cash:'Կանխիկ', card:'Անկանխիկ', online:'Առցանց' };

function renderSummary(arr){
  const wrap = document.getElementById('summary');
  const monthShortHY = ['Հուն','Փետ','Մար','Ապր','Մայ','Հուն','Հուլ','Օգս','Սեպ','Հոկ','Նոյ','Դեկ'];
  const money = n => Number(n || 0).toLocaleString('hy-AM', { maximumFractionDigits: 0 });

  if (wrap) {
    wrap.innerHTML = (arr || []).map((sum, i) => `
      <div class="col-6 col-sm-4 col-md-2 col-lg-1 mb-2">
        <div class="card p-2 text-center">
          <div class="text-muted small">${monthShortHY[i] || ''}</div>
          <div class="sum" style="font-weight:700">${money(sum)}</div>
        </div>
      </div>
    `).join('');
  }

  const total = (arr || []).reduce((a, b) => a + Number(b || 0), 0);
  $('#tfootTotal').text(money(total));
}

  const $tbl = $('#studentPaymentTbl');
  const STUDENT_ID = $tbl.data('student-id');

  let studentPaymentTbl = null;

  function initStudentPaymentTable(){
    if (studentPaymentTbl) return;

    studentPaymentTbl = $("#studentPaymentTbl").DataTable({
      language: lang,
      processing: true,
      serverSide: true,
      ajax: {
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url: `/admin/payment/student/${STUDENT_ID}/data`,
        type: 'post',
        data: function(d) {
          d.year   = $('#year').val();
          d.status = $('#status').val();
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
                    data-comment="${(row.comment||'').replace(/"/g,'&quot;')}">
              <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger act-del" data-id="${id}">
              <i class="fas fa-trash"></i>
            </button>`
        }
      ]
    });

    $('#studentPaymentTbl').on('xhr.dt', function (_e, _settings, json) {
      if (json && Array.isArray(json.summary)) renderSummary(json.summary);
    });

    $('#btnRefresh, #year, #status').on('change click', function(){
      if ($.fn.DataTable.isDataTable('#studentPaymentTbl')) {
        studentPaymentTbl.ajax.reload();
      }
    });
  }

  $(document).one('payment:filtersLoaded', initStudentPaymentTable);
  if ($('#year option').length) initStudentPaymentTable();
});
