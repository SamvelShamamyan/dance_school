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
                <a href="{{ route('admin.student.create') }}" 
                   class="btn btn-success shadow-sm">
                    <i class="fas fa-plus"></i> Ավելացնել
                </a>
            </div>

        </div>
    </div>
</div>

@if(Auth::user()->hasRole('super-admin'))
    <div id="studentHeaderFilter" class="card mb-3">
        <div class="card-body">
            <div class="row"> 
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12"> 
                    <div class="form-group">
                        <label for="filterStudentSchool">Ընտրել ուս․ հաստատություն</label>
                        <select id="filterStudentSchool" class="form-control">
                            <option value="" selected>Բոլորը</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->id }}" data-name="{{ $school->name }}">
                                    {{ $school->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <!-- Group Select -->
                <div class="form-group col-xl-4 col-lg-4 col-md-6 col-sm-12">
                    <label for="group_id">Խումբ</label>
                    <select id="group_id" class="form-control" disabled>
                        <option value="" selected>Բոլորը</option>
                    </select>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                    <div class="form-group">
                        <label for="filter_range_date">Ծննդյան ամսաթիվ</label>
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
            </div>
        </div>
    </div>
@endif

@if(Auth::user()->hasRole('school-admin'))
    <div id="studentHeaderFilter" class="card mb-3">
        <div class="card-body">
            <div class="form-row">
                <!-- Group Select -->
                <div class="form-group col-md-3">
                    <label for="group_id">Խումբ</label>
                    <select id="group_id" class="form-control">
                        <option value="" selected>Բոլորը</option>
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}" data-name="{{ $group->name }}">{{ $group->name }}</option>
                            @endforeach
                    </select>
                </div>

                 <div class="col-6 col-md-3">
                    <div class="form-group">
                        <label for="filter_range_date">Ծննդյան ամսաթիվ</label>
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

            </div>
        </div>
    </div>
@endif

<div id="birthdayBlock"></div>

<div class="card shadow-sm">
    <div class="card-body bg-white">
         <div class="table-responsive">
            <table class="table table-striped table-bordered dtTbl" style="width:100%" id="studentTbl">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ԱԱՀ</th>
                        <th>Էլ․ հասցե</th>
                        <!-- <th>Բնակության հասցե</th> -->
                        <th>Ծննդյան ամսաթիվ</th>
                        <th>Ընդունվելու ամսաթիվ</th>
                        <th>Վճար</th>
                        <th>Հավելավճար</th>
                        <th>Պարտք</th>
                        <th>Գործողություն</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

<script>
  window.currentUserRole = @json(Auth::user()->getRoleNames()[0] ?? null);
</script>

@push('scripts')
  <script src="{{asset('dist/js/student.js')}}" defer></script>
@endpush