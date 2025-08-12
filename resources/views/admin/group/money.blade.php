@extends('admin.layouts.main')
@section('content')

@section('title', 'Демо — Оплаты по месяцам')

@push('styles')
<style>
  .filters .form-label { font-size:.85rem; margin-bottom:.25rem; }
  .summary-cards .card { border-radius: 14px; }
  .summary-cards .sum { font-weight: 700; font-size: 1.05rem; }
  .pivot-table th, .pivot-table td { white-space: nowrap; }
  .pivot-table td.text-end { text-align: right; }
  .pivot-table td.zero { color:#888; }
  .pivot-table td.paid { background: rgba(25,135,84,.08); }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

  {{-- Заголовок --}}
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="mb-0">Оплаты по месяцам</h3>
      <div class="text-muted small">Демо-отображение (статические данные)</div>
    </div>
    <div>
      <button id="demoReload" class="btn btn-primary">Обновить</button>
    </div>
  </div>

  {{-- Фильтры (демо) --}}
  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-3 align-items-end filters">
        <div class="col-6 col-md-2">
          <label class="form-label">Год</label>
          <select id="year" class="form-select">
            @for($y = now()->year; $y >= now()->year - 5; $y--)
              <option value="{{ $y }}" @selected($y==now()->year)>{{ $y }}</option>
            @endfor
          </select>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Группа/класс</label>
          <select id="group_id" class="form-select">
            <option value="">Все</option>
            <option value="1">A-1</option>
            <option value="2">B-2</option>
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
          <input id="search" type="text" class="form-control" placeholder="Имя ученика…">
        </div>
      </div>
    </div>
  </div>

  {{-- Карточки-сводки по месяцам --}}
  <div class="summary-cards row row-cols-2 row-cols-sm-3 row-cols-md-6 row-cols-lg-12 g-2 mb-3" id="summary">
    {{-- Подставляется ниже из JS (демо-данные) --}}
  </div>

  {{-- Таблица-пивот (статические демо-данные) --}}
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped table-bordered pivot-table mb-0" id="pivotTable">
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
          <tbody>
          {{-- Несколько демо-строк --}}
          <tr data-student="1" data-name="Анна Петросян">
            <td>Анна Петросян</td>
            <td class="text-end paid">50,000</td>
            <td class="text-end zero">0</td>
            <td class="text-end paid">50,000</td>
            <td class="text-end zero">0</td>
            <td class="text-end paid">50,000</td>
            <td class="text-end zero">0</td>
            <td class="text-end zero">0</td>
            <td class="text-end paid">50,000</td>
            <td class="text-end zero">0</td>
            <td class="text-end paid">50,000</td>
            <td class="text-end zero">0</td>
            <td class="text-end zero">0</td>
            <td class="text-end fw-semibold">250,000</td>
            <td class="text-center">
              <button class="btn btn-sm btn-light view-history">⋯</button>
            </td>
          </tr>
          <tr data-student="2" data-name="Григор Мартиросян">
            <td>Григор Мартиросян</td>
            <td class="text-end zero">0</td>
            <td class="text-end paid">60,000</td>
            <td class="text-end zero">0</td>
            <td class="text-end paid">60,000</td>
            <td class="text-end zero">0</td>
            <td class="text-end paid">60,000</td>
            <td class="text-end zero">0</td>
            <td class="text-end zero">0</td>
            <td class="text-end paid">60,000</td>
            <td class="text-end zero">0</td>
            <td class="text-end paid">60,000</td>
            <td class="text-end zero">0</td>
            <td class="text-end fw-semibold">300,000</td>
            <td class="text-center">
              <button class="btn btn-sm btn-light view-history">⋯</button>
            </td>
          </tr>
          <tr data-student="3" data-name="Մարիամ Կարապետյան">
            <td>Մարիամ Կարապետյան</td>
            <td class="text-end paid">40,000</td>
            <td class="text-end paid">40,000</td>
            <td class="text-end paid">40,000</td>
            <td class="text-end paid">40,000</td>
            <td class="text-end paid">40,000</td>
            <td class="text-end paid">40,000</td>
            <td class="text-end zero">0</td>
            <td class="text-end zero">0</td>
            <td class="text-end zero">0</td>
            <td class="text-end zero">0</td>
            <td class="text-end zero">0</td>
            <td class="text-end zero">0</td>
            <td class="text-end fw-semibold">240,000</td>
            <td class="text-center">
              <button class="btn btn-sm btn-light view-history">⋯</button>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
      <div class="small text-muted mt-2">Нажмите «⋯» для просмотра истории оплат (демо).</div>
    </div>
  </div>
</div>

{{-- Модалка истории оплат (демо) --}}
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><span id="histTitle">История оплат</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
      </div>
      <div class="modal-body">
        <div id="histBody" class="list-group list-group-flush"></div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Демонстрационные данные для карточек-«сводки по месяцам»
  const summaryDemo = [
    90000, 100000, 130000, 100000, 90000, 100000,
    40000, 50000, 60000, 110000, 60000, 0
  ];
  const monthNames = ['Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'];

  function money(n) {
    n = Number(n||0);
    return n.toLocaleString(undefined, {minimumFractionDigits:0, maximumFractionDigits:0});
  }

  // Рендер карточек сводки
  (function renderSummary(){
    const wrap = document.getElementById('summary');
    wrap.innerHTML = summaryDemo.map((sum, i) => `
      <div class="col">
        <div class="card p-2 text-center">
          <div class="text-muted small">${monthNames[i]}</div>
          <div class="sum">${money(sum)}</div>
        </div>
      </div>
    `).join('');
  })();

  // История оплат (демо) по клику
  document.querySelector('#pivotTable').addEventListener('click', function(e){
    const btn = e.target.closest('.view-history');
    if(!btn) return;
    const tr = btn.closest('tr');
    const name = tr?.dataset?.name || 'Ученик';
    document.getElementById('histTitle').textContent = 'История оплат — ' + name;

    // Простая мок-история
    const nowYear = new Date().getFullYear();
    const mock = [
      { paid_at: `${nowYear}-01-15`, amount: 50000, method: 'cash', status: 'paid', comment:'Январь' },
      { paid_at: `${nowYear}-03-10`, amount: 50000, method: 'card', status: 'paid', comment:'Март' },
      { paid_at: `${nowYear}-08-05`, amount: 50000, method: 'online', status: 'paid', comment:'Август' },
      { paid_at: `${nowYear}-10-02`, amount: 50000, method: 'cash', status: 'paid', comment:'Октябрь' }
    ];
    const html = mock.map(r => `
      <div class="list-group-item d-flex justify-content-between align-items-center">
        <div>
          <div class="fw-semibold">${new Date(r.paid_at).toLocaleDateString()} · <span class="text-success">${money(r.amount)}</span></div>
          <div class="small text-muted">${r.method} · ${r.status}${r.comment ? ' · ' + r.comment : ''}</div>
        </div>
        <span class="badge bg-light text-dark">${monthNames[new Date(r.paid_at).getMonth()]}</span>
      </div>
    `).join('');
    document.getElementById('histBody').innerHTML = html;

    // Открыть модалку (Bootstrap 5)
    const modal = new bootstrap.Modal(document.getElementById('historyModal'));
    modal.show();
  });

  // Кнопка «Обновить» (в демо просто подсвечиваем ячейки > 0)
  document.getElementById('demoReload').addEventListener('click', function(){
    document.querySelectorAll('#pivotTable tbody tr').forEach(tr => {
      tr.querySelectorAll('td').forEach((td, idx) => {
        if(idx === 0 || idx >= 14) return; // пропустить имя и «итого/кнопка»
        const v = Number((td.textContent||'0').replace(/\D/g,''));
        td.classList.toggle('paid', v>0);
        td.classList.toggle('zero', v===0);
      });
    });
  });

  // Автоподсветка при загрузке
  document.getElementById('demoReload').click();
</script>
@endpush

@endsection
