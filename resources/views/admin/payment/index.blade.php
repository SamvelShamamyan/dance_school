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
                            <i class="fas fa-coins text-primary mr-1 fa-lg"></i>
                            <span>Ամսական վճարումներ</span>
                        </li>
                    </ol>
                </nav>
            </div>
            <div class="col-auto">
              <div class="btn-group">
                  <button class="btn btn-success" id="paymentBtn" data-toggle="modal" data-target="#addPaymentModal" ><i class="fas fa-plus"></i> Ավելացնել վճարում</button>
              </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
  <div class="card-body">
    <div class="row align-items-end">

      @role('super-admin|super-accountant')
        <div class="col-4 col-md-4 mb-3">
          <label class="form-label d-block">Դպրոց</label>
          <select id="school_id" class="form-control">
            <option value="" selected>Բոլորը</option>
          </select>
        </div>
      @endrole
      
      <div class="col-4 col-md-4 mb-3">
        <label class="form-label d-block">Տարի</label>
        <select id="year" class="form-control"></select>
      </div>

      <div class="col-4 col-md-4 mb-3">
        <label class="form-label d-block">Խումբ</label>
        <select id="group_id" class="form-control"></select>
      </div>

      <div class="col-4 col-md-4 mb-3">
        <label class="form-label d-block">Վճարման կարգավիճակ</label>
        <select id="status" class="form-control"></select>
      </div>

    </div>
  </div>
</div>


<div class="card mb-3">
  <div class="card-body">
    <div class="row" id="summary">
    </div>
  </div>
</div>


<div class="card shadow-sm">
    <div class="card-body bg-white">
      <table class="table table-striped table-bordered dtTbl" style="width:100%" id="paymentTbl">
          <thead>
              <tr>
                  <th>Աշակերտ</th>
                  <th>Հուն</th>
                  <th>Փետ</th>
                  <th>Մար</th>
                  <th>Ապր</th>
                  <th>Մայ</th>
                  <th>Հուն</th>
                  <th>Հուլ</th>
                  <th>Օգս</th>
                  <th>Սեպ</th>
                  <th>Հոկ</th>
                  <th>Նոյ</th>
                  <th>Դեկ</th>
                  <th>Ընդամենը</th>
                  <th>Գործողություն</th>
              </tr>
          </thead>
          <tbody>
          </tbody>
      </table>
  </div>
</div>

<div class="modal fade" id="addPaymentModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <form id="singlePaymentForm">
        <div class="modal-header">
          <h5 class="modal-title">Ավելացնել վճարում (միանվագ)</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">

          @role('super-admin|super-accountant')
            <div class="form-group mb-2">
              <label class="form-label d-block">Դպրոց</label>
              <select id="school_id" class="form-control">
                <option value="" selected>Բոլորը</option>
              </select>
              <small class="error_school_id text-danger"></small>
            </div>
          @endrole

          <div class="form-group mb-2">
            <label>Խումբ</label>
            <select name="group_id" class="form-control" id="groups" {{ Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('super-accountant') ? 'disbled' : '' }} >
              <option value="" disabled selected>Ընտրել</option>
            </select>
            <small class="error_group_id text-danger"></small>
          </div>

          <div class="form-group mb-2">
            <label>Աշակերտ</label>
            <select name="student_id" class="form-control" id="students_list">
              <option value="" disabled selected>Ընտրել</option>
            </select>
            <small class="error_student_id text-danger"></small>
          </div>
          <div class="form-row">
            <div class="form-group col-6">
              <label for="group_date">Վճարման ամսաթիվ</label>
              <div class="input-group date" id="paymentDatePicker" data-target-input="nearest">
                  <input type="text"
                      id="paid_at"
                      name="paid_at"
                      value=""
                      class="form-control datetimepicker-input"
                      data-target="#paymentDatePicker"
                      data-toggle="datetimepicker" />
                  <div class="input-group-append" data-target="#paymentDatePicker" data-toggle="datetimepicker">
                  <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                  </div>
              </div>
              <small class="error_paid_at text-danger"></small>
            </div>


            <div class="form-group col-6">
              <label>Վճարման ենթակա գումար</label>
              <input type="number" name="amount" class="form-control" min="0" step="1000" placeholder="Օրինակ` 50000">
              <small class="error_amount text-danger"></small>
            </div>

          </div>
          <div class="form-row">
            <div class="form-group col-6">
              <label>Վճարման տաբերակ</label>
              <select name="method" class="form-control" id="pay_method">
                <option value="" disabled selected>Ընտրել</option>
                <option value="cash">Կանխիկ</option>
                <option value="card">Անկանխիկ</option>
              </select>
              <small class="error_method text-danger"></small>
            </div>
            <div class="form-group col-6">
              <label>Վճարման կարգավիճակ</label>
              <select name="status" class="form-control" id="pay_status">
                <option value="" disabled selected>Ընտրել</option>
                <option value="paid">Վճարված</option>
                <option value="pending">Սպասման մեջ</option>
              </select>
              <small class="error_status text-danger"></small>
            </div>
          </div>
          <div class="form-group mt-2">
            <label>Մեկնաբանություն</label>
            <input type="text" name="comment" class="form-control" placeholder="օրինակ` Մարտ ամսվա վճարում">
            <small class="error_comment text-danger"></small>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">Փակել</button>
          <button type="button" class="btn btn-primary" id="singlePaymentBtn" onclick="savePayment()">Պահպանել</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection


<script>
    window.currentUserRole = @json(Auth::user()->getRoleNames()[0] ?? null);
    window.currentUserRoleSchoolId = @json(Auth::user()->school_id ?? null);
</script>
<script src="{{ asset('dist/js/payment.js') }}" defer></script>
