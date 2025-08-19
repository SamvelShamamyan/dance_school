@extends('admin.layouts.main')
@section('content')


    <a href="{{ route('admin.user.create') }}" class="btn btn-success mb-3">
        <i class="fas fa-plus"></i> Ավելացնել
    </a>

    <table class="table table-striped table-bordered dtTbl" style="width:100%" id="userTbl">
        <thead>
            <tr>
                <th>ID</th>
                <th>Անուն</th>
                <th>Ազգանուն</th>
                <th>Հայրանուն</th>
                <th>Ոուս․ հաստատություն</th>
                <th>Գործողություն</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>

@endsection