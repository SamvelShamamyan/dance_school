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
        </div>
    </div>
</div>

@if(Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('super-accountant'))
    <div id="studentAttendancesFilter" class="card mb-3">
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

<div class="card shadow-sm">
    <div class="card-body bg-white">
        <div class="table-responsive">
            <table class="table table-striped table-bordered dtTbl" style="width:100%" id="studentAttendances">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ուս․ հաստատություն</th>
                        <th>Խումբ</th>
                        <th>Դահլիճ</th>
                        <th>Օր</th>
                        <th>Սկիզբ</th>
                        <th>Ավարտ</th>
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

<script src="{{ asset('dist/js/studentAttendances/studentAttendances.table.js') }}" defer></script>
