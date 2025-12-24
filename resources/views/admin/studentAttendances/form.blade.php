@extends('admin.layouts.main')
@section('content')

<!-- <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
  <div class="d-flex align-items-center">
    <a href="{{ route('admin.room.index') }}"
       class="btn btn-outline-secondary btn-sm mr-3 btn-icon"
       title="Հետ վերադարձ">
      <i class="fas fa-arrow-left"></i>
    </a>
  </div>
</div> -->

<div class="card shadow-sm mb-4 border-0">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col d-flex align-items-center">
              <a href="{{ route('admin.student.attendances.index') }}"
                  class="btn btn-outline-secondary btn-sm mr-3 btn-icon"
                  title="Հետ վերադարձ">
                <i class="fas fa-arrow-left"></i>
              </a>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 bg-transparent p-0">

                      @if(optional(auth()->user()->school)->name)
                            <li class="breadcrumb-item d-flex align-items-center">
                                <i class="fas fa-university text-primary mr-1 fa-lg"></i>
                                <span>{{ auth()->user()->school->name }}</span>
                            </li>

                            <li class="breadcrumb-item active d-flex align-items-center font-weight-bold" aria-current="page">
                              <i class="fas fa-user-check text-success mr-1 fa-lg"></i>
                              <span>Ներկա-բացակա</span>
                            </li>
                          @else
                            <li class="breadcrumb-item active d-flex align-items-center" aria-current="page">
                                <i class="fas fa-university text-primary mr-1 fa-lg"></i>
                                <span>Բոլոր ուսումնական հաստատությունները</span>
                            </li>
                        @endif

                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-12 col-lg-12 col-xl-6">
        <form id="checkAttendancesForm">
             @csrf
            <input type="hidden" name="schedule_group_id" value="{{ $id }}">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title mb-0 font-weight-bold">Ստուգման ենթակա խմումբը ըստ աշակերտների</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="group_date">Ստուգում անցկացնելու ամսաթիվ</label>
                        <div class="input-group date"
                            id="checkAttendancesDatePicker"
                            data-target-input="nearest">
                        <input type="text"
                                id="inspection_date"
                                name="inspection_date"
                                class="form-control datetimepicker-input"
                                data-target="#checkAttendancesDatePicker"
                                data-toggle="datetimepicker" />
                        <div class="input-group-append"
                            data-target="#checkAttendancesDatePicker"
                            data-toggle="datetimepicker">
                            <div class="input-group-text">
                            <i class="fa fa-calendar"></i>
                            </div>
                        </div>
                        </div>
                        <small class="error_inspection_date text-danger"></small>
                    </div>

                    <div class="card-body dg-white {{ $isTrue ? 'shadow' : '' }}">
                        <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>ԱԱՀ</th>
                                <th class="text-center">Հյուր</th>
                                <th class="text-center">Ներկա / Բացակա</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($studentsList as $item)
                             <input type="hidden" name="students[]" value="{{ $item->id }}" {{ $isTrue ? '' : 'disabled' }}/>
                                <tr>
                                <td>{{ $item->full_name }}</td>
                                <td class="text-center">
                                    <div class="icheck-primary d-inline">
                                    <input type="checkbox"
                                            id="guest{{  $item->id }}"
                                            name="attendance_guest[{{  $item->id }}]" {{ $isTrue ? '' : 'disabled' }}>
                                    <label for="guest{{  $item->id }}"></label>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-group clearfix mb-0">
                                    <div class="icheck-success d-inline">
                                        <input type="radio"
                                            id="present{{  $item->id }}"
                                            name="attendance_check[{{  $item->id }}]"
                                            value="1" {{ $isTrue ? '' : 'disabled' }}>
                                        <label for="present{{ $item->id }}">Ներկա</label>
                                    </div>
                                    <div class="icheck-danger d-inline ml-3">
                                        <input type="radio"
                                            id="absent{{  $item->id }}"
                                            name="attendance_check[{{  $item->id }}]"
                                            value="0" {{ $isTrue ? '' : 'disabled' }}>
                                        <label for="absent{{  $item->id }}">Բացակա</label>
                                    </div>
                                    </div>
                                </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
                  <div class="card-footer text-right">
                    @if($isTrue)
                      <button type="button" class="btn btn-primary" id="checkAttendancesBtn" onclick="checkAttendancesSave()">Պահպանել</button>
                    @endif
                  </div>
            </div>
        </form>    
    </div>


    @if(!$checkedAttendance->isEmpty())
      <div class="col-12 col-md-12 col-lg-12 col-xl-6">
        <div class="card card-primary">
          <div class="card-header">
            <h3 class="card-title mb-0 font-weight-bold">Անցկացված ստուգումների պատմություն</h3>
          </div>
          <div class="card-body">
            <div id="accordion">
              @foreach ($checkedAttendance as $date => $items)
                @php $collapseId = 'collapse-' . $loop->index; @endphp
                @php $first = $items->first(); @endphp
                <div class="card">
                  <div class="card-header" id="heading-{{ $loop->index }}">
                    <h5 class="mb-0">
                      <button class="btn btn-link d-flex align-items-center justify-content-between w-100 text-left px-0"
                            type="button"
                            data-toggle="collapse"
                            data-target="#{{ $collapseId }}"
                            aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                            aria-controls="{{ $collapseId }}">

                        <div class="d-flex align-items-center flex-wrap">
                            <i class="fas fa-university text-primary mr-2"></i>
                            <strong class="mr-2">
                                {{ $first->school_name ?? '' }}
                            </strong>

                            <span class="text-muted mx-2">•</span>

                            <i class="fas fa-users text-info mr-2"></i>
                            <span class="font-weight-semibold mr-2">
                                {{ $first->group_name ?? '' }}
                            </span>

                            <span class="text-muted mx-2">•</span>

                            <i class="fas fa-calendar text-success mr-2"></i>
                            <span class="text-muted">
                                {{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}
                            </span>
                        </div>

                        <div class="d-flex align-items-center count-wrap">
                            <span class="text-muted small mr-2 count-label">Աշակերտներ</span>

                            <span class="badge badge-info badge-pill px-3 count-badge">
                                {{ $items->count() }}
                            </span>

                            <i class="fas fa-chevron-down ml-3 text-muted"></i>
                        </div>

                      </button>

                    </h5>
                  </div>
                  <div id="{{ $collapseId }}"
                      class="collapse {{ $loop->first ? 'show' : '' }}"
                      aria-labelledby="heading-{{ $loop->index }}"
                      data-parent="#accordion">
                    <div class="card-body">
                      <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                          <thead>
                            <tr>
                              <th>ԱԱՀ</th>
                              <th class="text-center">Հյուր</th>
                              <th class="text-center">Ներկա / Բացակա</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach ($items as $item)
                              <tr>
                                <td>{{ $item->full_name }}</td>
                                <td class="text-center">
                                  <div class="icheck-primary d-inline">
                                    <input type="checkbox" {{ $item->is_guest ? 'checked' : '' }} disabled>
                                    <label></label>
                                  </div>
                                </td>
                                <td class="text-center">
                                  <div class="form-group clearfix mb-0">
                                    <div class="icheck-success d-inline">
                                      <input type="radio" disabled {{ (int)$item->checked_status === 1 ? 'checked' : '' }}>
                                      <label>Ներկա</label>
                                    </div>
                                    <div class="icheck-danger d-inline ml-3">
                                      <input type="radio" disabled {{ (int)$item->checked_status === 0 ? 'checked' : '' }}>
                                      <label>Բացակա</label>
                                    </div>
                                  </div>
                                </td>
                              </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div> <!-- table-responsive -->
                    </div> <!-- card-body -->
                  </div> <!-- collapse -->
                </div> <!-- card -->
              @endforeach
            </div> <!-- accordion -->
          </div> <!-- card-body -->
        </div>
      </div>
    </div>
  @else
    <div class="col-12 col-md-12 col-lg-12 col-xl-6">
      <div class="card card-primary shadow-sm">
        <div class="card-header">
          <h3 class="card-title mb-0 font-weight-bold">Անցկացված ստուգումների պատմություն</h3>
        </div>
        <div class="card-body">
          <div class="alert alert-info d-flex align-items-center mb-0" role="alert">
            <i class="fas fa-info-circle fa-lg mr-2"></i>
            <span>Տվյալները բացակայում են</span>
          </div>
        </div>
      </div>
    </div>
  @endif
  

@endsection

<script src="{{ asset('dist/js/studentAttendances/studentAttendances.js') }}" defer></script>
