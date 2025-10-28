@extends('admin.layouts.main')
@section('content')

<style>
    .bootstrap-datetimepicker-widget {
        min-width: 340px;
        font-size: 14px;
    }

    .bootstrap-datetimepicker-widget table {
        width: 100%;
    }

    .bootstrap-datetimepicker-widget .dow {
        font-size: 12px;
        white-space: nowrap;
        padding: 0.4rem 0.6rem;
        text-align: center;
    }

    .bootstrap-datetimepicker-widget .day {
        padding: 0.5rem 0.6rem;
    }
</style>


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
    <div class="col-12 col-md-12 col-lg-12 col-xl-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">{{ isset($group) ? 'Խմբագրել Խումբը' : 'Ավելացնել Խումբ' }}</h3>
            </div>
            
            <form id="GroupForm" action="{{ isset($group) ? route('admin.group.update', $group->id) : route('admin.group.add') }}">
                @csrf
                <div class="card-body">
                    @if(Auth::user()->hasRole('super-admin'))            
                        <div class="form-group">
                            <label for="filterSchoolGroup" class="mr-2 mb-0">Ուս․ հաստատություն  <small class="validation_star">*</small></label>
                            <select name="school_id" id="filterSchoolGroup" class="form-control">
                                <option value="" disabled {{ empty(old('school_id', $group->school_id ?? '')) ? 'selected' : '' }}>Ընտրել</option>
                                @foreach($schools as $school)
                                    <option value="{{ $school->id }}" data-name="{{ $school->name }}" 
                                    {{ old('school_id', $group->school_id ?? '') == $school->id ? 'selected' : '' }}>
                                        {{ $school->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="error_school_id text-danger"></small>
                        </div>         
                    @endif

                    <div class="form-group">
                        <label for="group_name">Անուն  <small class="validation_star">*</small></label>
                        <input type="text" class="form-control" id="group_name" name="group_name" value="{{ old('name', $group->name ?? '') }}" placeholder="">
                        <small class="error_group_name text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="group_date">Ամսաթիվ  <small class="validation_star">*</small></label>
                        <div class="input-group date" id="groupDatePicker" data-target-input="nearest">
                            <input type="text"
                                id="group_date"
                                name="group_date"
                                value="{{ old('created_date', isset($group->created_date) ? \Carbon\Carbon::parse($group->created_date)->format('d.m.Y') : '') }}"
                                class="form-control datetimepicker-input"
                                data-target="#groupDatePicker"
                                data-toggle="datetimepicker" />
                            <div class="input-group-append" data-target="#groupDatePicker" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                        <small class="error_group_date text-danger"></small>
                    </div>
                </div>
                <div class="card-footer  text-right">
                    <button type="button" class="btn btn-primary" id="groupBtn" onClick="saveGroup()">Պահպանել</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
