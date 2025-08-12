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
                <h3 class="card-title">{{ isset($student) ? 'Խմբագրել աշակերտիօն' : 'Ավելացնել աշակերտ' }}</h3>
            </div>
            
            <form id="StudentForm" action="{{ isset($student) ? route('admin.student.update', $student->id) : route('admin.student.add') }}">
                @csrf

                <div class="card-body">

                    <div class="form-group">
                        <label for="first_name">Անուն</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name', $student->first_name ?? '') }}" placeholder="">
                        <small class="error_first_name text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Ազգանուն</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('first_name', $student->last_name ?? '') }}" placeholder="">
                        <small class="error_last_name text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="father_name">Հայրանուն</label>
                        <input type="text" class="form-control" id="father_name" name="father_name" value="{{ old('first_name', $student->father_name ?? '') }}" placeholder="">
                        <small class="error_father_name text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="address">Բնակության հասցե</label>
                        <input type="text" class="form-control" id="address" name="address" value="{{ old('address', $student->address ?? '') }}" placeholder="">
                        <small class="error_address text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="soc_number">ՀԾՀ</label>
                        <input type="text" class="form-control" id="soc_number" name="soc_number" value="{{ old('soc_number', $student->soc_number ?? '') }}" placeholder="">
                        <small class="error_soc_number text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="email">Էլ․ հասցե</label>
                        <input type="text" class="form-control" id="email" name="email" value="{{ old('email', $student->email ?? '') }}" placeholder="">
                        <small class="error_email text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="group_date">Ծննդյան Ամսաթիվ</label>
                        <div class="input-group date" id="staffBirthDatePicker" data-target-input="nearest">
                            <input type="text"
                                id="birth_date"
                                name="birth_date"
                                value="{{ old('birth_date', isset($student->birth_date) ? \Carbon\Carbon::parse($student->birth_date)->format('d.m.Y') : '') }}"
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
                        <label for="group_date">Ընդունվելու ամսաթիվ</label>
                        <div class="input-group date" id="studentDatePicker" data-target-input="nearest">
                            <input type="text"
                                id="student_date"
                                name="student_date"
                                value="{{ old('created_date', isset($student->created_date) ? \Carbon\Carbon::parse($student->created_date)->format('d.m.Y') : '') }}"
                                class="form-control datetimepicker-input"
                                data-target="#studentDatePicker"
                                data-toggle="datetimepicker" />
                            <div class="input-group-append" data-target="#studentDatePicker" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                        <small class="error_student_date text-danger"></small>
                    </div>

                </div>
                <div class="card-footer  text-right">
                    <button type="button" class="btn btn-primary" id="studentBtn" onClick="saveStudent()">Պահպանել</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
