@extends('admin.layouts.main')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
  <div class="d-flex align-items-center">
      <a href="{{ url()->previous() }}" class="btn btn-light btn-sm mr-2" title="Հետ վերադարձ">
          <i class="fas fa-arrow-left"></i>
      </a>
        <h3 class="mb-0">
            <small class="text-muted">
                <i class="fas fa-school mr-1"></i> {{ auth()->user()->school->name }} /
                <i class="fas fa-users mr-1"></i> {{ $groupName }}
            </small>
        </h3>
  </div>
    <a href="{{ route('admin.staff.create') }}" class="btn btn-success mb-3">
        <i class="fas fa-plus"></i> Ավաելացնել
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body bg-white">
        <table class="table table-striped table-bordered dtTbl" style="width:100%" id="staffListTbl" data-id="{{ $groupId }}">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ԱԱՀ</th>
                    <th>Բնակության հասցե</th>
                    <th>Ծննդյան ամսաթիվ</th>
                    <!-- <th>Աշխատանքի ընդունման ամսաթիվ</th> -->
                    <th>Էլ․ հասցե</th>
                    <th>ՀԾՀ</th>
                    <!-- <th>Ուս․ հաստատություն</th> -->
                    <th>Գործողություն</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>    

@endsection