@extends('admin.layouts.main')
@section('content') 

<div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
  <div class="d-flex align-items-center">
    <a href="{{ route('admin.otherOffers.index') }}" 
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
                <h3 class="card-title mb-0 font-weight-bold">{{ isset($room) ? 'Խմբագրել' : 'Ավելացնել' }}</h3>
                </div>
                    <div class="card-body">
                        <form id="otherOfferForm" action="{{ route('admin.otherOffers.add') }}">
                            @csrf
                            @if(Auth::user()->hasRole('super-admin'))            
                                <div class="form-group">
                                    <label for="school_id" class="mr-2">Ուս․ հաստատություն <small class="validation_star">*</small></label>
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
                                
                                <div class="form-group">
                                    <label for="group_ids" class="mr-2">Խմբեր <small class="validation_star">*</small></label>
                                    <select name="group_ids[]" id="group_ids" class="form-control select2" multiple="multiple" data-placeholder="Ընտրել" style="width: 100%;">
                                    </select>
                                    <small class="error_group_ids text-danger"></small>
                                </div>   

                                <div class="form-group">
                                    <label for="name">Անվանումը <small class="validation_star">*</small></label>
                                    <input type="text" name="name" class="form-control" id="name" value="{{ old('name', $room->name ?? '') }}" placeholder="">
                                    <small class="error_name text-danger"></small>
                                </div>

                                <div class="form-group">
                                    <label>Վճարման ենթակա գումար <small class="validation_star">*</small></label>
                                    <input type="number" name="payments" value="" class="form-control" min="0" step="1000" placeholder="Օրինակ` 50000">
                                    <small class="error_payments text-danger"></small>
                                </div>

                            @endif

                            @if(Auth::user()->hasRole('school-admin'))            
                                
                                <div class="form-group">
                                    <label for="group_ids" class="mr-2">Խմբեր <small class="validation_star">*</small></label>
                                    <select name="group_ids[]" id="group_ids" class="form-control select2" multiple="multiple" data-placeholder="Ընտրել" style="width: 100%;">   
                                        @foreach($groups as $group)
                                            <option value="{{ $group->id }}" data-name="{{ $group->name }}" 
                                            {{ old('group_id', $group->school_id ?? '') == $group->id ? 'selected' : '' }}>
                                                {{ $group->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="error_group_ids text-danger"></small>
                                </div>   

                                <div class="form-group">
                                    <label for="name">Անվանումը <small class="validation_star">*</small></label>
                                    <input type="text" name="name" class="form-control" id="name" value="{{ old('name', $room->name ?? '') }}" placeholder="">
                                    <small class="error_name text-danger"></small>
                                </div>

                                <div class="form-group">
                                    <label>Վճարման ենթակա գումար <small class="validation_star">*</small></label>
                                    <input type="number" name="payments" value="" class="form-control" min="0" step="1000" placeholder="Օրինակ` 50000">
                                    <small class="error_payments text-danger"></small>
                                </div>

                            @endif

                          

                        </div>
                        <div class="card-footer text-right">
                            <button type="button" class="btn btn-primary" id="roomFormBtn" onClick="saveOtherOffer()">Պահպանել</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

@endsection

@push('scripts')
    <script src="{{ asset('dist/js/other.offers.form.js') }}" defer></script>
@endpush

