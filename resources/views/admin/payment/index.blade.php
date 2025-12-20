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
        <div class="col-12 col-md-3 mb-3">
          <label class="form-label d-block">Դպրոց</label>
          <select id="school_id" class="form-control">
            <option value="" selected>Բոլորը</option>
          </select>
        </div>
      @endrole


      <div class="col-6 col-md-3">
        <div class="form-group">
          <label for="filter_range_date">Ժամանակահատված</label>
          <div class="input-group">
            <input type="text"
                  id="filter_range_date"
                  name="filter_range_date"
                  class="form-control" />
            <input type="hidden" id="range_from" name="range_from">
            <input type="hidden" id="range_to"   name="range_to">
            <div class="input-group-append">
              <div class="input-group-text"><i class="fa fa-calendar"></i></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-3">
        <label class="form-label d-block">Խումբ</label>
        <select id="group_id" class="form-control"></select>
      </div>
      <div class="col-6 col-md-3 mb-3">
        <label class="form-label d-block">Վճարման տաբերակ</label>
        <select id="method" class="form-control"></select>
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

<div class="card shadow-sm mb-5">
    <div class="card-body bg-white">
      <div class="table-responsive">
        <table class="table table-striped table-bordered dtTbl" style="width:100%" id="paymentTbl">
            <thead>
            </thead>
            <tbody>
            </tbody>
        </table>
      </div>
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

          <div id="infoBlock" style="display:none;">
            <div class="alert shadow-sm border-0 rounded-3 d-flex align-items-start p-3 mb-3" 
                style="background-color:#fff8e5; color:#5a4636;" role="alert">
              
              <div class="flex-shrink-0 mr-2">
                <i class="bi bi-exclamation-triangle-fill me-3" 
                  style="color:#e69500; font-size:2.5rem;"></i>
              </div>

              <div class="flex-grow-1">
                <div id="infoBlockContent" class="fw-semibold mb-1" style="color:#3d2c1d;"></div>
                <small class="d-block" style="color:#7a6a58;">
                  Հավելավճարը ամեն ամսվա սկզբում ինքնաշխատ կերպով կգանձվի որպես ընթացիկ ամսվա վճար:
                </small>
              </div>
            </div>
          </div>

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
            <select name="group_id" class="form-control" id="groups" {{ Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('super-accountant') ? 'disabled' : '' }} >
              <option value="" disabled selected>Ընտրել</option>
            </select>
            <small class="error_group_id text-danger"></small>
          </div>

          <div class="form-group mb-2">
            <label>Աշակերտ</label>
            <select name="student_id" class="form-control" id="students_list" {{ Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('super-accountant') ? 'disabled' : 'disabled' }}>
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
              <input type="number" name="amount" class="form-control" min="0" step="1000" placeholder="Օրինակ` 50000" id="amount">
              <small class="error_amount text-danger"></small>
            </div>

          </div>
          <div class="form-row">
            <div class="form-group col-12">
              <label>Վճարման տաբերակ</label>
              <select name="method" class="form-control" id="pay_method">
                <option value="" disabled selected>Ընտրել</option>
                <option value="cash">Կանխիկ</option>
                <option value="card">Անկանխիկ</option>
              </select>
              <small class="error_method text-danger"></small>
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

@push('head')
  <script>
      window.currentUserRole = @json(Auth::user()->getRoleNames()[0] ?? null);
      window.currentUserRoleSchoolId = @json(Auth::user()->school_id ?? null);
  </script>
@endpush

@push('scripts')
  <script src="{{ asset('dist/js/payment.js') }}" defer></script>
@endpush
