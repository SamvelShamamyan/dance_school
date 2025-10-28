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

    #staff-dropzone {
        border: 2px dashed #ced4da;
        border-radius: .25rem;
        min-height: 160px;
        padding: 16px;
        cursor: pointer;
        background: #f8f9fa;
    }

    #staff-dropzone.dropzone .dz-filename span { overflow-wrap: anywhere; }

    #staff-dropzone .dz-progress { display: none !important; }
    #staff-dropzone .dz-success-mark,
    #staff-dropzone .dz-error-mark { display: none !important; }
    

</style>

<div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
  <div class="d-flex align-items-center">
        <a href="{{ route('admin.staff.index') }}" 
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
                <h3 class="card-title">{{ isset($staff) ? 'Խմբագրել աշխատակցին' : 'Ավելացնել աշխատակից' }}</h3>
            </div>

            <form id="StaffForm"
                  action="{{ isset($staff) ? route('admin.staff.update', $staff->id) : route('admin.staff.add') }}"
                  method="post">
                @csrf
                <div class="card-body">
                    @if(Auth::user()->hasRole('super-admin'))            
                        <div class="form-group">
                            <label for="school_ids" class="mr-2">Ուս․ հաստատություն <small class="validation_star">*</small></label>

                            @php
                                $chosen = collect(old('school_ids', $selectedSchoolIds ?? []))
                                            ->map(fn($v) => (int)$v)->all();
                            @endphp

                            <select name="school_ids[]" id="school_ids" class="form-control select2" multiple="multiple" data-placeholder="Ընտրել" style="width: 100%;">
                                @foreach($schools as $school)
                                    <option value="{{ $school->id }}"
                                            data-name="{{ $school->name }}"
                                            {{ in_array($school->id, $chosen, true) ? 'selected' : '' }}>
                                        {{ $school->name }}
                                    </option>
                                @endforeach      
                            </select>
                            <small class="error_school_ids text-danger"></small>
                        </div>         
                    @endif

                    <div class="form-group">
                        <label for="first_name">Անուն <small class="validation_star">*</small></label>
                        <input type="text" class="form-control" id="first_name" name="first_name"
                               value="{{ old('first_name', $staff->first_name ?? '') }}" placeholder="">
                        <small class="error_first_name text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Ազգանուն <small class="validation_star">*</small></label>
                        <input type="text" class="form-control" id="last_name" name="last_name"
                               value="{{ old('last_name', $staff->last_name ?? '') }}" placeholder="">
                        <small class="error_last_name text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="father_name">Հայրանուն <small class="validation_star">*</small></label>
                        <input type="text" class="form-control" id="father_name" name="father_name"
                               value="{{ old('father_name', $staff->father_name ?? '') }}" placeholder="">
                        <small class="error_father_name text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="address">Բնակության հասցե <small class="validation_star">*</small></label>
                        <input type="text" class="form-control" id="address" name="address"
                               value="{{ old('address', $staff->address ?? '') }}" placeholder="">
                        <small class="error_address text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="soc_number">ՀԾՀ</label>
                        <input type="text" class="form-control" id="soc_number" name="soc_number"
                               value="{{ old('soc_number', $staff->soc_number ?? '') }}" placeholder="">
                        <small class="error_soc_number text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="email">Էլ․ հասցե <small class="validation_star">*</small></label>
                        <input type="text" class="form-control" id="email" name="email"
                               value="{{ old('email', $staff->email ?? '') }}" placeholder="">
                        <small class="error_email text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="phone_1">Հեռ․/ 1 <small class="validation_star">*</small></label>
                        <input type="text" class="form-control" id="phone_1" name="phone_1" value="{{ old('phone_1', $staff->phone_1 ?? '') }}" placeholder="(__) ___-__-__">
                        <small class="error_phone_1 text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="phone_2">Հեռ․/ 2 </label>
                        <input type="text" class="form-control" id="phone_2" name="phone_2" value="{{ old('phone_2', $staff->phone_2 ?? '') }}" placeholder="(__) ___-__-__">
                    </div>

                    <div class="form-group">
                        <label for="group_date">Ծննդյան Ամսաթիվ <small class="validation_star">*</small></label>
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
                        <label for="group_date">Աշխատանքի ընդունման ամսաթիվ <small class="validation_star">*</small></label>
                        <div class="input-group date" id="staffDatePicker" data-target-input="nearest">
                            <input type="text"
                                   id="staff_date"
                                   name="staff_date"
                                   value="{{ old('staff_date', isset($staff->created_date) ? \Carbon\Carbon::parse($staff->created_date)->format('d.m.Y') : '') }}"
                                   class="form-control datetimepicker-input"
                                   data-target="#staffDatePicker"
                                   data-toggle="datetimepicker" />
                            <div class="input-group-append" data-target="#staffDatePicker" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                        <small class="error_staff_date text-danger"></small>
                    </div>

                    @if(isset($staff) && $staff->files->count())
                    <div class="form-group">
                        <label>Ավելացված ֆայլեր</label>

                        <div class="row" id="existing-files">
                        @foreach($staff->files as $file)
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
                                        class="btn btn-sm btn-outline-danger js-mark-remove"
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
                                <div id="staff-dropzone" class="dropzone">
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
                <div class="card-footer text-right">
                    <button type="button" class="btn btn-primary" id="staffBtn">Պահպանել</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
