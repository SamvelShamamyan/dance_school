@extends('admin.layouts.main')
@section('content')

@push('styles')
<style>
  #summary .card { border-radius: 14px; }
  #summary .sum { font-weight: 700; font-size: 1.05rem; }
</style>
@endpush

<div class="card shadow-sm mb-4 border-0">
  <div class="card-body">
    <div class="row align-items-center">
      <div class="col d-flex align-items-center">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item active d-flex align-items-center" aria-current="page">
               <i class="fas fa-hand-holding-usd text-primary mr-1 fa-lg"></i>
              <span>Աշակերտների պարտքեր</span>
            </li>
          </ol>
        </nav>
      </div>
    </div>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body">
    <div class="row align-items-end">

      @role('super-admin|super-accountant')
      <div class="col-12 col-md-4 mb-4">
        <label class="form-label d-block">Դպրոց</label>
        <select id="school_id" class="form-control">
          <option value="" selected>Բոլորը</option>
        </select>
      </div>
      @endrole

      <div class="col-6 col-md-4 mb-4">
        <label class="form-label d-block">Տարի</label>
        <select id="year" class="form-control"></select>
      </div>

      <div class="col-6 col-md-4 mb-4">
        <label class="form-label d-block">Խումբ</label>
        <select id="group_id" class="form-control">
          <option value="">Բոլորը</option>
        </select>
      </div>

    </div>
  </div>
</div>

<!-- Սեղմագիր -->
<div class="card mb-3">
  <div class="card-body">
    <div class="row" id="summary"></div>
  </div>
</div>

<!-- Աղյուսակ -->
<div class="card shadow-sm">
  <div class="card-body bg-white">
      <div class="table-responsive">

    <table class="table table-striped table-bordered dtTbl" style="width:100%" id="debtTbl">
    <thead>
      <tr>
        <th rowspan="2">Աշակերտ</th>
        <th colspan="3" class="text-center">Հուն</th>
        <th colspan="3" class="text-center">Փետ</th>
        <th colspan="3" class="text-center">Մար</th>
        <th colspan="3" class="text-center">Ապր</th>
        <th colspan="3" class="text-center">Մայ</th>
        <th colspan="3" class="text-center">Հուն</th>
        <th colspan="3" class="text-center">Հուլ</th>
        <th colspan="3" class="text-center">Օգս</th>
        <th colspan="3" class="text-center">Սեպ</th>
        <th colspan="3" class="text-center">Հոկ</th>
        <th colspan="3" class="text-center">Նոյ</th>
        <th colspan="3" class="text-center">Դեկ</th>
        <th colspan="3" class="text-center">Ընդամենը</th>
        <th rowspan="2" class="text-center">Գործ.</th>
      </tr>
      <tr>
        @for ($i=1; $i<=12; $i++)
          <th class="text-end">Նշ.</th>
          <th class="text-end">Վճ.</th>
          <th class="text-end">Պարտք</th>
        @endfor
        <th class="text-end">Նշ.</th>
        <th class="text-end">Վճ.</th>
        <th class="text-end">Պարտք</th>
      </tr>
    </thead>
      <tbody></tbody>
    <tfoot>
      <tr>
        <th>Ընդհանուր</th>
        @for ($i = 1; $i <= 12; $i++)
          <th class="text-end" id="sum_due_{{ $i }}">-</th>
          <th class="text-end" id="sum_paid_{{ $i }}">-</th>
          <th class="text-end" id="sum_rem_{{ $i }}">-</th>
        @endfor
        <th class="text-end" id="sum_total_due">-</th>
        <th class="text-end" id="sum_total_paid">-</th>
        <th class="text-end" id="sum_total_rem">-</th>
        <th></th> 
      </tr>
    </tfoot>
    </table>
  </div>
</div>
</div>

@endsection

<script>
  window.currentUserRole = @json(Auth::user()->getRoleNames()[0] ?? null);
  window.currentUserRoleSchoolId = @json(Auth::user()->school_id ?? null);
</script>
<script src="{{ asset('dist/js/debts.js') }}" defer></script>
