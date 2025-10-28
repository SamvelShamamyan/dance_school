@extends('admin.layouts.main')
@section('content') 

<div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
  <div class="d-flex align-items-center">
    <a href="{{ route('admin.room.index') }}" 
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
                <h3 class="card-title">{{ isset($room) ? 'Խմբագրել դահլիճը' : 'Ավելացնել դահլիճ' }}</h3>
                </div>
                <form id="roomForm" action="{{ isset($room) ? route('admin.room.update', $room->id) : route('admin.room.add') }}">
                     @csrf
                    <div class="card-body">
                        @if(Auth::user()->hasRole('super-admin'))            
                            <div class="form-group">
                                <label for="school_id" class="mr-2 mb-0">Ուս․ հաստատություն <small class="validation_star">*</small></label>
                                <select name="school_id" id="school_id" class="form-control">
                                    <option value="" disabled {{ empty(old('school_id', $room->school_id ?? '')) ? 'selected' : '' }}>Ընտրել</option>
                                    @foreach($schools as $school)
                                        <option value="{{ $school->id }}" data-name="{{ $school->name }}" 
                                        {{ old('school_id', $room->school_id ?? '') == $school->id ? 'selected' : '' }}>
                                            {{ $school->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="error_school_id text-danger"></small>
                            </div>         
                        @endif
                        <div class="form-group">
                            <label for="name">Անվանումը <small class="validation_star">*</small></label>
                            <input type="text" name="name" class="form-control" id="name" value="{{ old('name', $room->name ?? '') }}" placeholder="">
                            <small class="error_name text-danger"></small>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="button" class="btn btn-primary" id="roomFormBtn" onClick="saveRoom()">Պահպանել</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 
@endsection

<script src="{{ asset('dist/js/room/room.js') }}" defer></script>


