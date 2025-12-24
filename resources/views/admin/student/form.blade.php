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

    #student-dropzone {
        border: 2px dashed #ced4da;
        border-radius: .25rem;
        min-height: 160px;
        padding: 16px;
        cursor: pointer;
        background: #f8f9fa;
    }

    #student-dropzone.dropzone .dz-filename span { overflow-wrap: anywhere; }

    #student-dropzone .dz-progress { display: none !important; }
    #student-dropzone .dz-success-mark,
    #student-dropzone .dz-error-mark { display: none !important; }


  .form-section {
    position: relative;
    margin: 1.25rem 0 0.75rem;
  }
  .form-section hr {
    margin: 0;
    border-top: 1px dashed #5e5e5eff;
  }




</style>

<div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
  <div class="d-flex align-items-center">
    <a href="{{ route('admin.student.index') }}" 
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
                <h3 class="card-title mb-0 font-weight-bold">{{ isset($student) ? 'Խմբագրել աշակերտին' : 'Ավելացնել աշակերտ' }}</h3>
            </div>
            
            <form id="StudentForm" 
                  action="{{ isset($student) ? route('admin.student.update', $student->id) : route('admin.student.add') }}" method="post">
                @csrf

                <div class="card-body">

                    @if(Auth::user()->hasRole('super-admin'))            
                        <div class="form-group">
                            <label for="school_id" class="mr-2 mb-0">Ուս․ հաստատություն <small class="validation_star">*</small></label>
                            <select name="school_id" id="schoolIdStudFilter" class="form-control">
                                <option value="" disabled {{ empty(old('school_id', $student->school_id ?? '')) ? 'selected' : '' }}>Ընտրել</option>
                                @foreach($schools as $school)
                                    <option value="{{ $school->id }}" data-name="{{ $school->name }}" 
                                    {{ old('school_id', $student->school_id ?? '') == $school->id ? 'selected' : '' }}>
                                        {{ $school->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="error_school_id text-danger"></small>
                        </div>     
                    @endif


                    <div class="form-group">
                        <label for="first_name">Անուն <small class="validation_star">*</small></label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name', $student->first_name ?? '') }}" placeholder="">
                        <small class="error_first_name text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Ազգանուն <small class="validation_star">*</small></label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('first_name', $student->last_name ?? '') }}" placeholder="">
                        <small class="error_last_name text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="father_name">Հայրանուն <small class="validation_star">*</small></label>
                        <input type="text" class="form-control" id="father_name" name="father_name" value="{{ old('first_name', $student->father_name ?? '') }}" placeholder="">
                        <small class="error_father_name text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="address">Բնակության հասցե </label>
                        <input type="text" class="form-control" id="address" name="address" value="{{ old('address', $student->address ?? '') }}" placeholder="">
                        <small class="error_address text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="soc_number">ՀԾՀ</label>
                        <input type="text" class="form-control" id="soc_number" name="soc_number" value="{{ old('soc_number', $student->soc_number ?? '') }}" placeholder="">
                        <small class="error_soc_number text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="email">Էլ․ հասցե <small class="validation_star">*</small></label>
                        <input type="text" class="form-control" id="email" name="email" value="{{ old('email', $student->email ?? '') }}" placeholder="">
                        <small class="error_email text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="phone_1">Հեռ․/1 <small class="validation_star">*</small></label>
                        <input type="text" class="form-control" id="phone_1" name="phone_1" value="{{ old('phone_1', $student->phone_1 ?? '') }}" placeholder="(__) ___-__-__">
                        <small class="error_phone_1 text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="phone_2">Հեռ․/2 </label>
                        <input type="text" class="form-control" id="phone_2" name="phone_2" value="{{ old('phone_2', $student->phone_2 ?? '') }}" placeholder="(__) ___-__-__">
                    </div>

                    <div class="form-group">
                        <label>Վճարման ենթակա գումար <small class="validation_star">*</small></label>
                        <input type="number" name="student_expected_payments" value="{{ old('student_expected_payments', $student->student_expected_payments ?? '') }}" class="form-control" min="0" step="1000" placeholder="Օրինակ` 50000">
                        <small class="error_student_expected_payments text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="group_date">Ծննդյան Ամսաթիվ <small class="validation_star">*</small></label>
                        <div class="input-group date" id="studentBirthDatePicker" data-target-input="nearest">
                            <input type="text"
                                id="birth_date"
                                name="birth_date"
                                value="{{ old('birth_date', isset($student->birth_date) ? \Carbon\Carbon::parse($student->birth_date)->format('d.m.Y') : '') }}"
                                placeholder="Ընտրել"
                                class="form-control datetimepicker-input"
                                data-target="#studentBirthDatePicker"
                                data-toggle="datetimepicker" />
                            <div class="input-group-append" data-target="#studentBirthDatePicker" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                        <small class="error_birth_date text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="group_date">Ընդունվելու ամսաթիվ <small class="validation_star">*</small></label>
                        <div class="input-group date" id="studentDatePicker" data-target-input="nearest">
                            <input type="text"
                                id="student_date"
                                name="created_date"
                                value="{{ old('created_date', isset($student->created_date) ? \Carbon\Carbon::parse($student->created_date)->format('d.m.Y') : '') }}"
                                class="form-control datetimepicker-input"
                                data-target="#studentDatePicker"
                                data-toggle="datetimepicker" />
                            <div class="input-group-append" data-target="#studentDatePicker" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                        <small class="error_created_date text-danger"></small>
                    </div>

                    <div class="card card-outline card-primary p-3 mb-3">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="parent_first_name">Ծնողի անուն </label>
                                <input type="text"
                                    class="form-control"
                                    id="parent_first_name"
                                    name="parent_first_name"
                                    value="{{ old('parent_first_name', $student->parent_first_name ?? '') }}"
                                    placeholder="">
                                <small class="error_parent_first_name text-danger"></small>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="parent_last_name">Ծնողի ազգանուն </label>
                                <input type="text"
                                    class="form-control"
                                    id="parent_last_name"
                                    name="parent_last_name"
                                    value="{{ old('parent_last_name', $student->parent_last_name ?? '') }}"
                                    placeholder="">
                                <small class="error_parent_last_name text-danger"></small>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded p-3 mb-3 bg-light d-flex align-items-center">
                        <div class="form-check icheck-primary mb-0">
                            <input 
                                type="checkbox"
                                class="form-check-input"
                                id="is_guest"
                                name="is_guest"
                                value="1"
                                {{ old('is_guest', $student->is_guest ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label font-weight-bold text-secondary" for="is_guest">
                                <i class="fas fa-user-check text-primary mr-1"></i> Հյուր
                            </label>
                        </div>
                    </div>
    
                    @if(isset($student) && $student->files->count())
                    <div class="form-group">
                        <label>Ավելացված ֆայլեր</label>

                        <div class="row" id="existing-files">
                        @foreach($student->files as $file)
                            @php
                            $name     = $file->name ?? basename($file->path);
                            $filePath = ltrim($file->path ?? '', '/');

                            $publicUrl = asset('storage/' . $filePath);

                            $ext   = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                            $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp','bmp','svg']);
                            @endphp

                            <div class="col-md-6 mb-2" data-file-id="{{ $file->id }}">
                            <div class="d-flex align-items-center border rounded p-2">
                                <div class="mr-2"
                                    style="width:56px;height:56px;overflow:hidden;border-radius:6px;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;">
                                @if($isImg)
                                    <img src="{{ $publicUrl }}" alt="" style="max-width:100%;max-height:100%;">
                                @else
                                    <i class="far fa-file fa-lg text-secondary"></i>
                                @endif
                                </div>

                            <div class="flex-grow-1 mr-2 px-2 py-1" style="min-width:0;">
                                    <div class="text-truncate" title="{{ $name }}">{{ $name }}</div>
                                    @if(!empty($file->size))
                                        <small class="text-muted">{{ number_format($file->size / 1024, 1) }} KB</small>
                                    @endif
                                </div>

                                <a href="{{ $publicUrl }}" target="_blank" class="btn btn-sm btn-outline-secondary mr-2">
                                Դիտել
                                </a>
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger js-mark-remove-st"
                                        data-id="{{ $file->id }}">
                                Ջնջել
                                </button>
                            </div>
                            </div>
                        @endforeach
                        </div>
                        <div id="removed-files"></div>
                        <small class="text-muted">Ֆայլը ջնջվում է պահպանելուց հետո։</small>
                    </div>
                    @endif
                    <div class="form-group">
                        <label>Ներբեռնել ֆայլեր</label>
                        <div class="card card-outline card-primary">
                            <div class="card-body">
                                <div id="student-dropzone" class="dropzone">
                                    <div class="dz-message">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-2"></i>
                                    <h5>Քաշեք և թողեք ֆայլերը այստեղ կամ սեղմեք ընտրելու համար</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <small class="error_file text-danger"></small>
                    </div>
                </div>
                <div class="card-footer  text-right">
                    <button type="button" class="btn btn-primary" id="studentBtn">Պահպանել</button>
                </div>
            </form>
        </div>
    </div>


    @if (isset($student))
        <div class="col-6">
            <div class="card card-primary">
                <div class="card-header">
                <h3 class="card-title">Խմբերում տեղափոխությունների պատմություն</h3>
                </div>
                <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered mb-0 w-100" 
                        id="studentGroupHistoryTbl"
                        data-student-id="{{ $student->id ?? '' }}">
                    <thead>
                        <tr>
                        <th>#</th>
                        <th>Փոխադրումը</th>         
                        <th>Ժամանակահատվածը (նախորդ խմբում)</th>
                        <th>Վերջին՞</th>        
                        </tr>
                    </thead>
                    <tbody></tbody>
                    </table>
                </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
  window.currentUserRole = @json(Auth::user()->getRoleNames()[0] ?? null);
</script>

@endsection

