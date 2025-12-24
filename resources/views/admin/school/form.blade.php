@extends('admin.layouts.main')
@section('content') 

<div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
  <div class="d-flex align-items-center">
    <a href="{{ route('admin.school.index') }}" 
        class="btn btn-outline-secondary btn-sm mr-3 btn-icon" 
        title="Հետ վերադարձ">
        <i class="fas fa-arrow-left"></i>
    </a>
  </div>
</div>

<div class="row">
    <div class="col-12 col-md-12 col-lg-12 col-xl-6">
        <div class="card card-primary">
                <div class="card-header">
                <h3 class="card-title mb-0 font-weight-bold">{{ isset($school) ? 'Խմբագրել ուս․ հաստատությունը' : 'Ավելացնել ուս․ հաստատություն' }}</h3>
                </div>
                <form id="schoolForm" action="{{ isset($school) ? route('admin.school.update', $school->id) : route('admin.school.add') }}">
                     @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Ուս․ հաստատության անվանումը  <small class="validation_star">*</small></label>
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


