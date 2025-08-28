@extends('admin.layouts.main')
@section('content')

<div class="card shadow-sm mb-4 border-0">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col d-flex align-items-center">
                
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 bg-transparent p-0">
                        <li class="breadcrumb-item active d-flex align-items-center" aria-current="page">
                            <i class="fas fa-user-shield text-primary mr-1 fa-lg"></i>
                            <span>Համակարգողներ</span>
                        </li>
                    </ol>
                </nav>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.user.create') }}" 
                   class="btn btn-success shadow-sm">
                    <i class="fas fa-plus"></i> Ավելացնել
                </a>
            </div>
        </div>
    </div>
</div> 

<div class="card shadow-sm">
    <div class="card-body bg-white">
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
    </div>
</div>
@endsection