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

<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="d-flex align-items-center">
    <a href="{{ url()->previous() }}" class="btn btn-light btn-sm mr-2" title="Հետ վերադարձ">
      <i class="fas fa-arrow-left"></i>
    </a>
    <h3 class="mb-0">
      Վճարումներ — {{ $student->last_name }} {{ $student->first_name }} {{ $student->father_name }}
    </h3>
    @if(optional($student->group)->name)
      <span class="badge badge-info ml-2">{{ $student->group->name }}</span>
    @endif
    <span class="badge badge-secondary ml-1">ID: {{ $student->id }}</span>
  </div>
  <div class="btn-group">
    <button class="btn btn-success" data-toggle="modal" data-target="#addPaymentModal">
      <i class="fas fa-plus"></i> Ավելացնել վճարում
    </button>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body">
    <div class="row">
      <div class="col-6 col-md-3 mb-2">
        <label class="form-label d-block">Տարի</label>
        <select id="year" class="form-control"></select>
      </div>
      <div class="col-6 col-md-3 mb-2">
        <label class="form-label d-block">Կարգավիճակ</label>
        <select id="status" class="form-control">
          <option value="">Բոլորը</option>
          <option value="paid">Վճարված</option>
          <option value="pending">Սպասման մեջ</option>
          <option value="refunded">Վերադարձված</option>
          <option value="failed">Սխալ</option>
        </select>
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
          <button type="button" class="btn btn-primary" id="singlePaymentBtn" onclick="savePayent()">Պահպանել</button>
        </div>
      </form>
    </div>
  </div>
</div>


<div class="modal fade" id="editPaymentModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form id="editPaymentForm">
        <input type="hidden" name="student_id" value="{{ $student->id }}">
        <input type="hidden" name="group_id" value="{{ $student->group_id }}">
        <input type="hidden" name="id" id="payment_id">
        <div class="modal-header">
          <h5 class="modal-title">Խմբագրել վճարումը</h5>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
            <div class="form-group">
              <label for="group_date">Վճարման ամսաթիվ</label>
              <div class="input-group date" id="paymentEditDatePicker" data-target-input="nearest">
                  <input type="text"
                      id="edit_paid_at"
                      name="edit_paid_at"
                      value=""
                      class="form-control datetimepicker-input"
                      data-target="#paymentEditDatePicker"
                      data-toggle="datetimepicker" />
                  <div class="input-group-append" data-target="#paymentEditDatePicker" data-toggle="datetimepicker">
                  <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                  </div>
              </div>
              <small class="paid_edit_paid_at text-danger"></small>
            </div>

          <div class="form-group">
            <label>Գումար</label>
            <input type="number" class="form-control" name="amount" id="edit_amount">
            <small class="text-danger error_amount"></small>
          </div>

          <div class="form-row">
            <div class="form-group col-6">
              <label>Մեթոդ</label>
              <select class="form-control" name="method" id="edit_method">
                <option value="cash">Կանխիկ</option>
                <option value="card">Անկանխիկ</option>
                <!-- <option value="online">Առցանց</option> -->
              </select>
            </div>
            <div class="form-group col-6">
              <label>Կարգավիճակ</label>
              <select class="form-control" name="status" id="edit_status">
                <option value="paid">Վճարված</option>
                <option value="pending">Սպասման մեջ</option>
                <!-- <option value="failed">Սխալ</option> -->
                <!-- <option value="refunded">Վերադարձված</option> -->
              </select>
            </div>
          </div>

          <div class="form-group">
            <label>Մեկնաբանություն</label>
            <input type="text" class="form-control" name="comment" id="edit_comment">
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">Փակել</button>
          <button type="submit" class="btn btn-primary">Պահպանել</button>
        </div>
      </form>
    </div>
  </div>
</div>


@endsection

