@extends('admin.layouts.main')
@section('content') 
<div class="row">
    <div class="col-6">
        <div class="card card-primary">
                <div class="card-header">
                <h3 class="card-title">{{ isset($school) ? 'Խմբագրել Ավաելացնել ուս․ հաստատությունը' : 'Ավաելացնել ուս․ հաստատություն' }}</h3>
                </div>
                <form id="schoolForm" action="{{ isset($school) ? route('admin.school.update', $school->id) : route('admin.school.add') }}">
                     @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Ուս․ հաստատության անվանումը</label>
                            <input type="text" name="name" class="form-control" id="name" value="{{ old('name', $school->name ?? '') }}" placeholder="">
                            <small class="error_name text-danger"></small>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="button" class="btn btn-primary" id="schoolFormBtn" onClick="saveSchool()">Պահպանել</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection


