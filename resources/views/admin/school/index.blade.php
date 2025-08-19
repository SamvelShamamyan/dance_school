@extends('admin.layouts.main')
@section('content')


    <a href="{{ route('admin.school.create') }}" class="btn btn-success mb-3">
        <i class="fas fa-plus"></i> Ավելացնել
    </a>

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