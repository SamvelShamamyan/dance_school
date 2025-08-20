@extends('admin.layouts.main')
@section('content')


<div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
  <div class="d-flex align-items-center">
        <h3 class="mb-0">
            <small class="text-muted">
                <i class="fas fa-user mr-1"></i> Համակարգողներ
            </small>
        </h3>
  </div>
    <a href="{{ route('admin.user.create') }}" class="btn btn-success mb-3">
        <i class="fas fa-plus"></i> Ավելացնել
    </a>
</div>


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