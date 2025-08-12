@extends('admin.layouts.main')
@section('content')


    <a href="{{ route('admin.student.create') }}" class="btn btn-success mb-3">
        <i class="fas fa-plus"></i> Ավաելացնել
    </a>

    <div class="card shadow-sm">
        <div class="card-body bg-white">
            <table class="table table-striped table-bordered dtTbl" style="width:100%" id="studenetsListTbl" data-id="{{ $groupId }}">
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