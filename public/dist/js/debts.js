// === Debts page (logic only; no DataTable init) ===
if (window.__DEBTS_JS_LOADED__) {
  console.warn('debts.js already loaded — skipped');
} else {
  window.__DEBTS_JS_LOADED__ = true;

  window.monthShortHY ||= ['Հուն','Փետ','Մար','Ապր','Մայ','Հուն','Հուլ','Օգս','Սեպ','Հոկ','Նոյ','Դեկ'];
  window.money       ||= (n => Number(n || 0).toLocaleString('hy-AM', { maximumFractionDigits: 0 }));

  function currentSchoolId() {
    return $('#school_id').length ? ($('#school_id').val() || '') : undefined;
  }

  // ===== Summary =====
  let currentSummary = { due: [], paid: [], rem: [] };

  function activeSummaryKind() {
    const $active = $('#sumTabs .nav-link.active');
    return $active.length ? ($active.data('kind') || 'rem') : 'rem';
  }

  function renderSummary(kind){
    const wrap = document.getElementById('summary');
    if (!wrap) return;
    const arr = (currentSummary[kind] || []);
    wrap.innerHTML = (arr || []).map((sum, i) => `
      <div class="col-6 col-sm-4 col-md-2 col-lg-1 mb-2">
        <div class="card p-2 text-center">
          <div class="text-muted small">${monthShortHY[i] || ''}</div>
          <div class="sum" style="font-weight:700">${money(sum)}</div>
        </div>
      </div>
    `).join('');
  }

  $('#sumTabs a').off('click.debts').on('click.debts', function (e) {
    e.preventDefault();
    $('#sumTabs a').removeClass('active');
    $(this).addClass('active');
    renderSummary(activeSummaryKind());
  });

  // ===== filters =====
  function loadFiltersAndMaybeInitTables() {
    const sid = currentSchoolId();
    $.ajax({
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      url: `/admin/debts/filters`,
      type: 'GET',
      dataType: 'json',
      data: { school_id: sid ?? '' },
      success: function (res) {
        // Տարի / Year
        const $year = $('#year').empty();
        if (res.years && res.years.length) {
          res.years.forEach(y => $year.append(`<option value="${y}">${y}</option>`));
          $year.val(res.years[0]);
        } else {
          const y = new Date().getFullYear();
          $year.append(`<option value="${y}">${y}</option>`).val(y);
        }

        // Խումբ / Group
        // const $group = $('#group_id').empty().append('<option value="">Բոլորը</option>');
        // (res.groups || []).forEach(g => $group.append(`<option value="${g.id}">${g.name}</option>`));

          const $group = $('#group_id')
          .prop('disabled', !sid) // если school_id пуст — делаем disabled
          .empty()
          .append('<option value="" disabled selected>Ընտրել</option>');

        (res.groups || []).forEach(g => 
          $group.append(`<option value="${g.id}">${g.name}</option>`)
        );



        // Վճարման կարգավիճակ / Status
        // const $status = $('#status').empty().append('<option value="">Բոլորը</option>');
        // (res.statuses || []).forEach(s => $status.append(`<option value="${s}">${s}</option>`));

       
        $(document).trigger('debts:filtersLoaded');

        if ($.fn.DataTable.isDataTable('#debtTbl')) {
          $('#debtTbl').DataTable().ajax.reload();
        }
      }
    });
  }

  $(function(){
    if (!$('#debtTbl').length) return;

    const $defaultTab = $('#sumTabs .nav-link.active');
    if ($defaultTab.length === 0 && $('#sumTabs .nav-link[data-kind="rem"]').length) {
      $('#sumTabs .nav-link[data-kind="rem"]').addClass('active');
    }

    if (!$('#school_id').length) {
      loadFiltersAndMaybeInitTables();
    } else {
      $.ajax({
        url: '/admin/payment/getSchools',
        type: 'GET',
        success: function (schools) {
          const $school = $('#school_id');
          $school.empty().append('<option value="" selected>Բոլորը</option>');
          (schools || []).forEach(school => $school.append(`<option value="${school.id}">${school.name}</option>`));
          loadFiltersAndMaybeInitTables();
        }
      });
      $('#school_id').off('change.debts').on('change.debts', loadFiltersAndMaybeInitTables);
    }
  });

  $('#year, #group_id, #status').off('change.debts click.debts').on('change.debts click.debts', function(){
    if ($.fn.DataTable.isDataTable('#debtTbl')) {
      $('#debtTbl').DataTable().ajax.reload();
    }
  });

  $(document).off('click.debts', '#debtTbl .view-history').on('click.debts', '#debtTbl .view-history', function (e) {
    e.preventDefault();
    e.stopPropagation();
    const id  = $(this).data('id');
    const sid = $(this).data('school-id') ?? currentSchoolId();
    const base = `/admin/payment/student/${encodeURIComponent(id)}`;
    const url  = sid ? `${base}?school_id=${encodeURIComponent(sid)}` : base;
    window.location.assign(url);
  });

  $('#debtTbl').off('xhr.dt.debts').on('xhr.dt.debts', function (_e, _settings, json) {
    if (json && json.summary) {
      currentSummary = {
        due:  Array.isArray(json.summary.due)  ? json.summary.due  : [],
        paid: Array.isArray(json.summary.paid) ? json.summary.paid : [],
        rem:  Array.isArray(json.summary.rem)  ? json.summary.rem  : []
      };
      renderSummary(activeSummaryKind());
    }


        if (json && json.summary) {
      currentSummary = {
        due:  Array.isArray(json.summary.due)  ? json.summary.due  : [],
        paid: Array.isArray(json.summary.paid) ? json.summary.paid : [],
        rem:  Array.isArray(json.summary.rem)  ? json.summary.rem  : []
      };
      renderSummary(activeSummaryKind());

      const money = n => Number(n || 0).toLocaleString('hy-AM', { maximumFractionDigits: 0 });
      let totalDue = 0, totalPaid = 0, totalRem = 0;

      for (let m = 1; m <= 12; m++) {
        const due  = Number(currentSummary.due[m - 1]  || 0);
        const paid = Number(currentSummary.paid[m - 1] || 0);
        const rem  = Number(currentSummary.rem[m - 1]  || 0);

        totalDue  += due;
        totalPaid += paid;
        totalRem  += rem;

        $(`#sum_due_${m}`).html(due  ? money(due)  : '<span class="text-muted">0</span>');
        $(`#sum_paid_${m}`).html(paid ? money(paid) : '<span class="text-muted">0</span>');
        $(`#sum_rem_${m}`).html(rem  ? `<span class="${rem > 0 ? 'text-danger' : 'text-success'}">${money(rem)}</span>` : '<span class="text-muted">0</span>');
      }

      $('#sum_total_due').html(`<b>${money(totalDue)}</b>`);
      $('#sum_total_paid').html(`<b>${money(totalPaid)}</b>`);
      $('#sum_total_rem').html(`<b class="${totalRem > 0 ? 'text-danger' : 'text-success'}">${money(totalRem)}</b>`);
    }


  });

  $(function(){
    if ($('#debtTbl').length && $('#year option').length) {
      $(document).trigger('debts:filtersLoaded');
      if ($.fn.DataTable.isDataTable('#debtTbl')) {
        $('#debtTbl').DataTable().ajax.reload();
      }
    }
  });
}
