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
    <div class="card mb-3">
        <div class="card-body">
            <div class="row"> 
                <div class="col-md-3"> 
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
            </div>
        </div>
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-body bg-white">
         <div class="table-responsive">
            <table class="table table-striped table-bordered dtTbl" style="width:100%" id="studentTbl">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ԱԱՀ</th>
                        <th>Բնակության հասցե</th>
                        <th>Ծննդյան ամսաթիվ</th>
                        <!-- <th>Աշխատանքի ընդունման ամսաթիվ</th> -->
                        <th>Էլ․ հասցե</th>
                        <th>ՀԾՀ</th>
                        <th>Վճար</th>
                        <th>Հավելավճար</th>
                        <th>Պարտք</th>
                        <!-- <th>Ուս․ հաստատություն</th> -->
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