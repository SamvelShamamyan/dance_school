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

<div class="row">
    <div class="col-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">{{ isset($staff) ? 'Խմբագրել աշխատակցին' : 'Ավելացնել աշխատակից' }}</h3>
            </div>
            
            <form id="StaffForm" action="{{ isset($staff) ? route('admin.staff.update', $staff->id) : route('admin.staff.add') }}">
                @csrf

                <div class="card-body">

                    <div class="form-group">
                        <label for="first_name">Անուն</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name', $staff->first_name ?? '') }}" placeholder="">
                        <small class="error_first_name text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Ազգանուն</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('first_name', $staff->last_name ?? '') }}" placeholder="">
                        <small class="error_last_name text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="father_name">Հայրանուն</label>
                        <input type="text" class="form-control" id="father_name" name="father_name" value="{{ old('first_name', $staff->father_name ?? '') }}" placeholder="">
                        <small class="error_father_name text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="address">Բնակության հասցե</label>
                        <input type="text" class="form-control" id="address" name="address" value="{{ old('address', $staff->address ?? '') }}" placeholder="">
                        <small class="error_address text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="soc_number">ՀԾՀ</label>
                        <input type="text" class="form-control" id="soc_number" name="soc_number" value="{{ old('soc_number', $staff->soc_number ?? '') }}" placeholder="">
                        <small class="error_soc_number text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="email">Էլ․ հասցե</label>
                        <input type="text" class="form-control" id="email" name="email" value="{{ old('email', $staff->email ?? '') }}" placeholder="">
                        <small class="error_email text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="group_date">Ծննդյան Ամսաթիվ</label>
                        <div class="input-group date" id="staffBirthDatePicker" data-target-input="nearest">
                            <input type="text"
                                id="birth_date"
                                name="birth_date"
                                value="{{ old('birth_date', isset($staff->birth_date) ? \Carbon\Carbon::parse($staff->birth_date)->format('d.m.Y') : '') }}"
                                class="form-control datetimepicker-input"
                                data-target="#staffBirthDatePicker"
                                data-toggle="datetimepicker" />
                            <div class="input-group-append" data-target="#staffBirthDatePicker" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                        <small class="error_birth_date text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="group_date">Աշխատանքի ընդունման ամսաթիվ</label>
                        <div class="input-group date" id="staffDatePicker" data-target-input="nearest">
                            <input type="text"
                                id="staff_date"
                                name="staff_date"
                                value="{{ old('created_date', isset($staff->created_date) ? \Carbon\Carbon::parse($staff->created_date)->format('d.m.Y') : '') }}"
                                class="form-control datetimepicker-input"
                                data-target="#staffDatePicker"
                                data-toggle="datetimepicker" />
                            <div class="input-group-append" data-target="#staffDatePicker" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                        <small class="error_staff_date text-danger"></small>
                    </div>

                </div>
                <div class="card-footer  text-right">
                    <button type="button" class="btn btn-primary" id="staffBtn" onClick="saveStaff()">Պահպանել</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
