@extends('admin.layouts.main')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
  <div class="d-flex align-items-center">
    <a href="{{ url()->previous() }}" 
        class="btn btn-outline-secondary btn-sm mr-3 btn-icon" 
        title="Հետ վերադարձ">
        <i class="fas fa-arrow-left"></i>
    </a>
  </div>
</div>

<div class="row">
    <div class="col-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">{{ isset($user) ? 'Խմբագրել համակարգողին' : 'Ավելացնել համակարգող' }}</h3>
            </div>
            
            <form id="UserForm" action="{{ isset($user) ? route('admin.user.update', $user->id) : route('admin.user.add') }}">
                @csrf

                <div class="card-body">

                    <div class="form-group">
                        <label for="first_name">Անուն  <small class="validation_star">*</small></label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name', $user->first_name ?? '') }}" placeholder="">
                        <small class="error_first_name text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Ազգանուն  <small class="validation_star">*</small></label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name', $user->last_name ?? '') }}" placeholder="">
                        <small class="error_last_name text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="father_name">Հայրանուն  <small class="validation_star">*</small></label>
                        <input type="text" class="form-control" id="father_name" name="father_name" value="{{ old('father_name', $user->father_name ?? '') }}" placeholder="">
                        <small class="error_father_name text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="email">Էլ․ հասցե  <small class="validation_star">*</small></label>
                        <input type="text" class="form-control" id="email" name="email" value="{{ old('email', $user->email ?? '') }}" placeholder="">
                        <small class="error_email text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="school_id">Դպրոց <small class="validation_star">*</small></label>
                        <select class="form-control" name="school_id" id="school_id">
                            <option value="" disabled {{ empty(old('school_id', $user->school_id ?? '')) ? 'selected' : '' }}>Ընտրել դպրոցը</option>
                            @foreach($schoolNameData as $school)
                                <option value="{{ $school->id }}" 
                                    {{ old('school_id', $user->school_id ?? '') == $school->id ? 'selected' : '' }}>
                                    {{ $school->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="error_school_id text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="role_name">Դերը <small class="validation_star">*</small></label>
                        <select class="form-control" name="role_name" id="role_name">
                            <option value="" disabled {{ empty(old('role_name', $userRole ?? '')) ? 'selected' : '' }}>Ընտրել դերը</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" 
                                    {{ old('role_name', $userRole ?? '') == $role->name ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="error_role_name text-danger"></small>
                    </div>

                </div>
                <div class="card-footer  text-right">
                    <button type="button" class="btn btn-primary" id="userFormBtn"onClick="saveUser()">Պահպանել</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
