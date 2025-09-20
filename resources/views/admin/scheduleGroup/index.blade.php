@extends('admin.layouts.main')
@section('content')

<div class="card shadow-sm mb-4 border-0">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col d-flex align-items-center">
               
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 bg-transparent p-0">

                        @if(optional(auth()->user()->school)->name)
                            <li class="breadcrumb-item d-flex align-items-center">
                                <i class="fas fa-university text-primary mr-1 fa-lg"></i>
                                <span>{{ auth()->user()->school->name }}</span>
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
            <div class="col-auto">
              <button class="btn btn-success shadow-sm" id="addBtn" type="button"><i class="fas fa-plus"></i> Ավելացնել</button>
            </div>
        </div>
    </div>
</div>


@if(Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('super-accountant'))
<div id="schedulerGroupFilter" class="card mb-3">
    <div class="card-body">
        <div class="form-row">
            <!-- School Select -->
            <div class="form-group col-md-6">
                <label for="school_id">Ընտրել ուս․ հաստատություն</label>
                <select id="school_id" class="form-control">
                    <option value="" selected>Բոլորը</option>
                    @foreach($schools as $school)
                        <option value="{{ $school->id }}" data-name="{{ $school->name }}">{{ $school->name }}</option>
                    @endforeach
                </select>
            </div>
            <!-- Group Select -->
            <div class="form-group col-md-6">
                <label for="group_id">Խումբ</label>
                <select id="group_id" class="form-control" disabled>
                    <option value="" selected>Բոլորը</option>
                </select>
            </div>
        </div>
    </div>
</div>
@endif


@if(Auth::user()->hasRole('school-admin'))
<div id="schedulerGroupFilter" class="card mb-3">
    <div class="card-body">
        <div class="form-row">
            <!-- Group Select -->
            <div class="form-group col-md-6">
                <label for="group_id">Խումբ</label>
                <select id="group_id" class="form-control" disabled>
                    <option value="" selected>Բոլորը</option>
                </select>
            </div>
        </div>
    </div>
</div>
@endif


<div class="container-fluid my-3">
  <div class="row">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-body p-0">
          <div class="calendar-wrap" id="calWrap">
            <div class="calendar" id="calendar">
              <div class="day-head" id="dayHead">
                <div class="bg-light">Ժամեր</div>
                <div data-head-day="1">Երկուշաբթի</div>
                <div data-head-day="2">Երեքշաբթի</div>
                <div data-head-day="3">Չորեքշաբթի</div>
                <div data-head-day="4">Հինգշաբթի</div>
                <div data-head-day="5">ՈՒրբաթ</div>
                <div data-head-day="6">Շաբաթ</div>
                <div data-head-day="7">Կիրակի</div>
              </div>

              <div class="grid" id="grid">
                <div class="times bg-white" id="times"></div>
                <div class="day"><div class="day-body" data-day="1"></div></div>
                <div class="day"><div class="day-body" data-day="2"></div></div>
                <div class="day"><div class="day-body" data-day="3"></div></div>
                <div class="day"><div class="day-body" data-day="4"></div></div>
                <div class="day"><div class="day-body" data-day="5"></div></div>
                <div class="day"><div class="day-body" data-day="6"></div></div>
                <div class="day"><div class="day-body" data-day="7"></div></div>
                <div class="now-line" id="nowLine"></div>
              </div>
            </div>

            <div class="day-tabs" id="dayTabs">
              <button class="btn btn-outline-secondary btn-sm" data-day="1">Երկուշաբթի</button>
              <button class="btn btn-outline-secondary btn-sm" data-day="2">Երեքշաբթի</button>
              <button class="btn btn-outline-secondary btn-sm" data-day="3">Չորեքշաբթի</button>
              <button class="btn btn-outline-secondary btn-sm" data-day="4">Հինգշաբթի</button>
              <button class="btn btn-outline-secondary btn-sm" data-day="5">ՈՒրբաթ</button>
              <button class="btn btn-outline-secondary btn-sm" data-day="6">Շաբաթ</button>
              <button class="btn btn-outline-secondary btn-sm" data-day="7">Կիրակի</button>
            </div>
          </div> <!-- /.calendar-wrap -->
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Events</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="evId" />
        <div class="form-row mb-2">
          <div class="col-6 col-md-4">
            <label class="form-label d-block">Օր</label>
            <select id="evDay" class="custom-select">
              <option value="1">Երկուշաբթի</option><option value="2">Երեքշաբթի</option><option value="3">Չորեքշաբթի</option>
              <option value="4">Հինգշաբթի</option><option value="5">ՈՒրբաթ</option><option value="6">Շաբաթ</option><option value="7">Կիրակի</option>
            </select>
          </div>
          <div class="col-6 col-md-4">
            <label class="form-label d-block">Սկիզբ</label>
            <input id="evStart" class="form-control" type="time" step="1800" max="23:30" />
          </div>
          <div class="col-6 col-md-4 mt-2 mt-md-0">
            <label class="form-label d-block">Ավարտ</label>
            <input id="evEnd" class="form-control" type="time" step="1800" max="23:30" />
          </div>
        </div>
        <div class="mb-2">
          <label class="form-label d-block">Անվանում</label>
          <input id="evTitle" class="form-control" placeholder="" />
        </div>

        <div class="form-group">
          <label for="school_id" class="mr-2 mb-0">Ընտրել ուս․ հաստատություն</label>
          <select id="school_id" class="form-control">
              <option value="" selected >Բոլորը</option>
              @foreach($schools as $school)
                  <option value="{{ $school->id }}" data-name="{{ $school->name }}">{{ $school->name }}</option>
              @endforeach
          </select>
        </div>

        <div class="form-group">
          <label for="group_id" class="mr-2 mb-0">Խումբ</label>
          <select id="group_id" class="form-control" disabled>
              <option value="" selected >Ընտրել</option>
          </select>
        </div>

        <div class="form-group">
          <label for="room_id" class="mr-2 mb-0">Դահլիճ</label>
          <select id="room_id" class="form-control" disabled>
              <option value="" selected >Ընտրել</option>
          </select>
        </div>

        <div class="mb-2">
          <label class="form-label d-block">Մեկնաբանություն</label>
          <input id="evNote" class="form-control" placeholder="" />
        </div>
        <div class="mb-2">
          <label class="form-label d-block">Գույն</label>
          <select id="evColor" class="custom-select">
            <option value="blue">Կապույտ</option><option value="green">Կանաչ</option><option value="purple">Մանուշակագույն</option><option value="orange">Նարնջագույն</option>
          </select>
        </div>
      </div>
      <div class="modal-footer d-flex justify-content-between">
        <div>
          <button type="button" class="btn btn-outline-danger" id="deleteBtn">Հեռացնել</button>
        </div>
        <div>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Փակել</button>
          <button type="button" class="btn btn-primary" id="saveBtn">Պահպանել</button>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

<script src="{{ asset( 'dist/js/scheduler.group.js') }}" defer></script>
