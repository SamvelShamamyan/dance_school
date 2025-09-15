@extends('admin.layouts.main')
@section('content')

@push('styles')
<style>
  #summary .card { border-radius: 14px; }
  #summary .sum  { font-weight: 700; font-size: 1.05rem; }
  .dt-right   { text-align: right; }
  .dt-center  { text-align: center; }
</style>
@endpush

@if ($student->trashed())
  <div class="card bg-danger text-white shadow-sm mb-4">
    <div class="card-body">
        <i class="fas fa-exclamation-triangle me-2"></i>
        Աշակերտը հեռացված է :
    </div>
  </div>
@endif


<div class="card shadow-sm mb-4 border-0">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col d-flex align-items-center">
                <a href="{{ route('admin.payment.index') }}" 
                   class="btn btn-outline-secondary btn-sm mr-3 btn-icon" 
                   title="Հետ վերադարձ">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <nav aria-label="breadcrumb">
                    Վճարումներ — {{ $student->last_name }} {{ $student->first_name }} {{ $student->father_name }}
                     @if(optional($student->group)->name)
                      <span class="badge badge-info ml-2">{{ $student->group->name }}</span>
                    @endif
                    <span class="badge badge-secondary ml-1">ID: {{ $student->id }}</span>
                </nav>
            </div>
            @if (!$student->trashed())
              <div class="col-auto">
                  <button class="btn btn-success" data-toggle="modal" data-target="#addPaymentModal">
                    <i class="fas fa-plus"></i> Ավելացնել վճարում
                  </button>
              </div>
            @endif
        </div>
    </div>
</div>

<div class="card mb-3">
  <div class="card-body">
    <div class="row">

      <div class="col-6 col-md-3 mb-2">
        <label class="form-label d-block">Տարի</label>
        <select id="year" class="form-control"></select>
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
    <div class="row" id="summary"></div>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body bg-white">
    <table class="table table-striped table-bordered"
           id="studentPaymentTbl"
           data-student-id="{{ $student->id }}"
           data-school-id="{{ $school_id }}"
           style="width:100%">
      <thead>
        <tr>
          <th>Ամսաթիվ</th>
          <th>Գումար</th>
          <th>Մեթոդ</th>
          <th>Կարգավիճակ</th>
          <th>Մեկնաբանություն</th>
          <th class="dt-center">Գործողություն</th>
        </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
        <tr>
          <th class="dt-right">Ընդամենը՝</th>
          <th id="tfootTotal" class="dt-right"></th>
          <th colspan="4"></th>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<div class="modal fade" id="addPaymentModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <form id="singlePaymentForm">
        <input type="hidden" name="student_id" value="{{ $student->id }}">
        <input type="hidden" name="group_id" value="{{ $student->group_id }}">
        <div class="modal-header">
          <h5 class="modal-title">Ավելացնել վճարում</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="close"><span>&times;</span></button>
        </div>
        <div class="modal-body">
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
          <button type="button" class="btn btn-primary" id="singlePaymentBtn" onclick="savePayent()">Պահպանել</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editPaymentModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <form id="editPaymentForm">

        <input type="hidden" name="student_id" value="{{ $student->id }}">
        <input type="hidden" name="group_id" value="{{ $student->group_id }}">
        <input type="hidden" name="id" id="payment_id">
        <input type="hidden" name="school_id" id="school_id">

        <div class="modal-header">
          <h5 class="modal-title">Խմբագրել վճարումը</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="close"><span>&times;</span></button>
        </div>

        <div class="modal-body">
          <div class="form-row">
            <div class="form-group col-6">
              <label for="paid_at">Վճարման ամսաթիվ</label>
              <div class="input-group date" id="paymentEditDatePicker" data-target-input="nearest">
                <input
                  type="text"
                  id="paid_at"
                  name="paid_at"
                  value=""
                  class="form-control datetimepicker-input"
                  data-target="#paymentEditDatePicker"
                  data-toggle="datetimepicker"
                />
                <div class="input-group-append" data-target="#paymentEditDatePicker" data-toggle="datetimepicker">
                  <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                </div>
              </div>
              <small class="error_paid_at text-danger"></small>
            </div>

            <div class="form-group col-6">
              <label for="amount">Վճարման ենթակա գումար</label>
              <input type="number" class="form-control" name="amount" id="amount">
              <small class="text-danger error_amount"></small>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-6">
              <label for="method">Վճարման տաբերակ</label>
              <select class="form-control" name="method" id="method">
                <option value="cash">Կանխիկ</option>
                <option value="card">Անկանխիկ</option>
              </select>
            </div>
            <div class="form-group col-6">
              <label for="status">Վճարման կարգավիճակ</label>
              <select class="form-control" name="status" id="status">
                <option value="paid">Վճարված</option>
                <option value="pending">Սպասման մեջ</option>
              </select>
            </div>
          </div>

          <div class="form-group mt-2">
            <label for="comment">Մեկնաբանություն</label>
            <input type="text" class="form-control" name="comment" id="comment">
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">Փակել</button>
          <button type="button" class="btn btn-primary" id="editPaymentBtn" onclick="updatePayment()">Պահպանել</button>
        </div>
      </form>
    </div>
  </div>
</div>


@endsection

<script>
  window.currentUserRole = @json(Auth::user()->getRoleNames()[0] ?? null);
</script>
<script src="{{ asset('dist/js/payment.student.js') }}" defer></script>

