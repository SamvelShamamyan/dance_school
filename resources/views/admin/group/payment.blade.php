{{-- resources/views/payments_ui_demo.blade.php --}}
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Демо — Платежи (UI)</title>

  {{-- Bootstrap 5 + Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  {{-- DataTables --}}
  <link href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" rel="stylesheet">

  <style>
    body { background:#f8fafc; }
    .filters .form-label { font-size:.85rem; margin-bottom:.25rem; }
    .summary-cards .card { border-radius: 14px; }
    .summary-cards .sum { font-weight: 700; font-size: 1.05rem; }
    .dt-input { width: 110px; }
    .dt-select { width: 120px; }
    .table thead th { white-space: nowrap; }
  </style>
</head>
<body>
<div class="container-fluid p-3 p-md-4">

  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="mb-0">Оплаты по месяцам</h3>
      <div class="text-muted small">Визуальный макет: фильтры, сводка, таблица, модалка</div>
    </div>
    <div class="btn-group">
      <button class="btn btn-outline-secondary" id="btnRefresh"><i class="bi bi-arrow-repeat"></i> Обновить</button>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPaymentModal"><i class="bi bi-plus-circle"></i> Добавить платёж</button>
    </div>
  </div>

  {{-- Фильтры --}}
  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-3 align-items-end filters">
        <div class="col-6 col-md-2">
          <label class="form-label">Год</label>
          <select id="year" class="form-select"></select>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Группа/класс</label>
          <select id="group_id" class="form-select">
            <option value="">Все</option>
            <option value="1">A-1</option>
            <option value="2">B-2</option>
            <option value="3">C-3</option>
          </select>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Статус</label>
          <select id="status" class="form-select">
            <option value="paid" selected>Оплачено</option>
            <option value="">Любой</option>
            <option value="pending">Ожидает</option>
            <option value="refunded">Возврат</option>
            <option value="failed">Ошибка</option>
          </select>
        </div>
        <div class="col-6 col-md-4">
          <label class="form-label">Поиск</label>
          <input id="search" type="text" class="form-control" placeholder="Имя ученика, комментарий…">
        </div>
      </div>
    </div>
  </div>

  {{-- Сводка по месяцам --}}
  <div class="summary-cards row row-cols-2 row-cols-sm-3 row-cols-md-6 row-cols-lg-12 g-2 mb-3" id="summary"></div>

  {{-- Таблица --}}
  <div class="card">
    <div class="card-body">
      <table id="pivotTable" class="table table-striped table-bordered w-100">
        <thead>
        <tr>
          <th style="min-width:220px">Ученик</th>
          <th>Янв</th><th>Фев</th><th>Мар</th><th>Апр</th>
          <th>Май</th><th>Июн</th><th>Июл</th><th>Авг</th>
          <th>Сен</th><th>Окт</th><th>Ноя</th><th>Дек</th>
          <th>Итого</th>
          <th style="width:60px"></th>
        </tr>
        </thead>
        <tbody></tbody>
      </table>
      <div class="small text-muted mt-2">Нажми на «⋯» у строки, чтобы открыть историю оплат ученика.</div>
    </div>
  </div>
</div>

{{-- Модалка: одиночное добавление платежа --}}
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="singlePaymentForm">
        <div class="modal-header">
          <h5 class="modal-title">Добавить платёж (одиночный)</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label">Ученик</label>
            <select name="student_id" class="form-select" required>
              <option value="" disabled selected>Выберите ученика</option>
              <option value="1">Анна Петросян</option>
              <option value="2">Գրիգոր Մարտիրոսյան</option>
              <option value="3">Mariam Karapetyan</option>
            </select>
          </div>
          <div class="row g-2">
            <div class="col-6">
              <label class="form-label">Дата оплаты</label>
              <input type="date" name="paid_date" class="form-control" required>
            </div>
            <div class="col-6">
              <label class="form-label">Сумма</label>
              <input type="number" name="amount" class="form-control" required min="0" step="1000" placeholder="напр. 50000">
            </div>
          </div>
          <div class="row g-2 mt-1">
            <div class="col-6">
              <label class="form-label">Метод</label>
              <select name="method" class="form-select">
                <option value="cash">Наличные</option>
                <option value="card">Карта</option>
                <option value="online">Онлайн</option>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label">Статус</label>
              <select name="status" class="form-select">
                <option value="paid" selected>Оплачено</option>
                <option value="pending">Ожидает</option>
                <option value="failed">Ошибка</option>
                <option value="refunded">Возврат</option>
              </select>
            </div>
          </div>
          <div class="mt-2">
            <label class="form-label">Комментарий</label>
            <input type="text" name="comment" class="form-control" placeholder="например: Оплата за март">
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
          <button type="submit" class="btn btn-primary">Сохранить платёж</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- JS --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

<script>
(function(){
  const monthNamesRU = ['Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'];

  // 1) Демоданные (можешь заменить потом на AJAX)
  const demoStudents = [
    { id:1, full_name:'Анна Петросян', m:[50000,0,50000,0,50000,0,0,50000,0,50000,0,0] },
    { id:2, full_name:'Գրիգոր Մարտիրոսյան', m:[0,60000,0,60000,0,60000,0,0,60000,0,60000,0] },
    { id:3, full_name:'Mariam Karapetyan', m:[40000,40000,40000,40000,40000,40000,0,0,0,0,0,0] },
  ];
  const demoSummary = [90000,100000,130000,100000,90000,100000,40000,50000,60000,110000,60000,0];

  // 2) Фильтр-«Год»
  (function fillYears(){
    const y = new Date().getFullYear();
    const sel = document.getElementById('year');
    for (let i=0;i<6;i++){
      const opt = document.createElement('option');
      opt.value = y - i;
      opt.textContent = y - i;
      sel.appendChild(opt);
    }
    sel.value = y;
  })();

  // 3) Рендер сводки
  function money(n){ n=Number(n||0); return n.toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:0}); }
  function renderSummary(){
    const wrap = document.getElementById('summary');
    wrap.innerHTML = demoSummary.map((sum, i)=>`
      <div class="col">
        <div class="card p-2 text-center">
          <div class="text-muted small">${monthNamesRU[i]}</div>
          <div class="sum">${money(sum)}</div>
        </div>
      </div>
    `).join('');
  }
  renderSummary();

  // 4) DataTables — пивот
  const table = $('#pivotTable').DataTable({
    data: demoStudents.map(s => {
      const total = s.m.reduce((a,b)=>a+(+b||0),0);
      return {
        id: s.id, full_name: s.full_name,
        m01: s.m[0], m02: s.m[1], m03: s.m[2], m04: s.m[3],
        m05: s.m[4], m06: s.m[5], m07: s.m[6], m08: s.m[7],
        m09: s.m[8], m10: s.m[9], m11: s.m[10], m12: s.m[11],
        total
      };
    }),
    columns: [
      { data: 'full_name' },
      ...Array.from({length:12}, (_,i)=>({
        data: 'm'+String(i+1).padStart(2,'0'),
        className: 'text-end',
        render: (v)=> {
          const num = parseFloat(v||0);
          const html = num===0 ? `<span class="text-muted">0</span>` : money(num);
          return html;
        }
      })),
      { data: 'total', className:'text-end fw-semibold', render:(v)=>money(v)},
      { data: 'id', orderable:false, className:'text-center',
        render:(id,_,row)=>`<button class="btn btn-sm btn-light view-history" data-id="${id}" data-name="${row.full_name}" title="История">&#8942;</button>` }
    ],
    paging: true,
    pageLength: 10,
    ordering: false,
    dom: 'Bfrtip',
    buttons: ['copy','csv','excel'],
    createdRow: function(row, data){
      for (let m=1;m<=12;m++){
        const idx = m; // 0 — имя
        const val = parseFloat(data['m'+String(m).padStart(2,'0')]||0);
        $(row).find('td').eq(idx).toggleClass('table-success', val>0);
      }
    }
  });

  // 5) Поиск и фильтры (в демо — просто перерисовка)
  $('#btnRefresh, #year, #group_id, #status').on('change click', function(){
    table.draw(false);
    renderSummary();
  });
  $('#search').on('keyup', function(){
    table.search(this.value).draw();
  });

  // 6) История оплат — демо
  $('#pivotTable').on('click', '.view-history', function(){
    const name = $(this).data('name');
    const id = $(this).data('id');
    const rows = [
      { paid_at:'2025-01-15', amount:50000, method:'cash', status:'paid', comment:'Январь' },
      { paid_at:'2025-03-10', amount:50000, method:'card', status:'paid', comment:'Март' },
      { paid_at:'2025-08-05', amount:50000, method:'online', status:'paid', comment:'Август' },
    ];
    const html = rows.map(r => `
      <div class="list-group-item d-flex justify-content-between align-items-center">
        <div>
          <div class="fw-semibold">${new Date(r.paid_at).toLocaleDateString()} · <span class="text-success">${money(r.amount)}</span></div>
          <div class="small text-muted">${r.method} · ${r.status}${r.comment ? ' · '+r.comment : ''}</div>
        </div>
        <span class="badge bg-light text-dark">${monthNamesRU[new Date(r.paid_at).getMonth()]}</span>
      </div>
    `).join('');

    const modalHtml = `
      <div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">История оплат — ${name}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body"><div class="list-group list-group-flush">${html}</div></div>
          </div>
        </div>
      </div>`;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('historyModal'));
    document.getElementById('historyModal').addEventListener('hidden.bs.modal', e => e.target.remove());
    modal.show();
  });

  // 7) Сабмит одиночного платежа — демо
  $('[name="paid_date"]').val(new Date().toISOString().slice(0,10));
  $('#singlePaymentForm').on('submit', function(e){
    e.preventDefault();
    const form = $(this).serializeArray().reduce((a,x)=>(a[x.name]=x.value,a),{});
    form.paid_at = (form.paid_date||new Date().toISOString().slice(0,10))+' 12:00:00';
    delete form.paid_date;
    console.log('SINGLE PAYMENT (demo):', form);
    alert('Демо: платёж сформирован (смотри консоль).');
    $('#addPaymentModal').modal('hide');
    this.reset();
  });
})();
</script>
</body>
</html>
