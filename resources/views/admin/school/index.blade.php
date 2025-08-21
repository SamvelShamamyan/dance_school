@extends('admin.layouts.main')
@section('content')

<div class="card shadow-sm mb-4 border-0">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col d-flex align-items-center">
                <a href="{{ url()->previous() }}" 
                   class="btn btn-outline-secondary btn-sm mr-3 btn-icon" 
                   title="Հետ վերադարձ">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 bg-transparent p-0">
                        <li class="breadcrumb-item active d-flex align-items-center" aria-current="page">
                            <i class="fas fa-university text-primary mr-1 fa-lg"></i>
                            <span>Ուս․ հաստատություններ </span>
                        </li>
                    </ol>
                </nav>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.school.create') }}" 
                   class="btn btn-success shadow-sm">
                    <i class="fas fa-plus"></i> Ավելացնել
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body bg-white">
        <table class="table table-striped table-bordered dtTbl" style="width:100%" id="school_name">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Անուն</th>
                    <th>Գործողություն</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

@endsection