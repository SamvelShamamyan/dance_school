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
        <h3 class="card-title mb-0 font-weight-bold">
          {{ isset($offer) ? 'Խմբագրել' : 'Ավելացնել' }}
        </h3>
      </div>

      <form id="otherOfferForm"
            action="{{ isset($offer) ? route('admin.otherOffers.update', $offer->id) : route('admin.otherOffers.add') }}">
        @csrf
    
        <div class="card-body">

          {{-- SUPER ADMIN --}}
          @if(Auth::user()->hasRole('super-admin'))
            
            @if(isset($offer))
              <input type="hidden" name="school_id" value="{{ old('school_id', $offer->school_id) }}">
            @endif

            <div class="form-group">
              <label for="school_id">Ուս․ հաստատություն <small class="validation_star">*</small></label>
              <select name="school_id" id="school_id" class="form-control"  {{ isset($offer) ? 'disabled' : '' }}>
                <option value="" disabled
                  {{ empty(old('school_id', $offer->school_id ?? '')) ? 'selected' : '' }}>
                  Ընտրել
                </option>

                @foreach($schools as $school)
                  <option value="{{ $school->id }}"
                    {{ (string)old('school_id', $offer->school_id ?? '') === (string)$school->id ? 'selected' : '' }}>
                    {{ $school->name }}
                  </option>
                @endforeach
              </select>
              <small class="error_school_id text-danger"></small>
            </div>

            <div class="form-group">
              <label for="group_ids">Խմբեր <small class="validation_star">*</small></label>
              <select name="group_ids[]" id="group_ids"
                      class="form-control select2"
                      multiple="multiple"
                      style="width:100%;">
                {{-- OPTIONS LOAD VIA AJAX --}}
              </select>
              <small class="error_group_ids text-danger"></small>
            </div>

          @endif

          {{-- SCHOOL ADMIN --}}
          @if(Auth::user()->hasRole('school-admin'))

            <div class="form-group">
              <label for="group_ids">Խմբեր <small class="validation_star">*</small></label>
              <select name="group_ids[]" id="group_ids"
                      class="form-control select2"
                      multiple="multiple"
                      style="width:100%;">
                @foreach($groups as $group)
                  <option value="{{ $group->id }}"
                    {{ in_array($group->id, old('group_ids', $selectedGroupIds ?? [])) ? 'selected' : '' }}>
                    {{ $group->name }}
                  </option>
                @endforeach
              </select>
              <small class="error_group_ids text-danger"></small>
            </div>

          @endif

          {{-- NAME --}}
          <div class="form-group">
            <label for="name">Անվանումը <small class="validation_star">*</small></label>
            <input type="text"
                   name="name"
                   id="name"
                   class="form-control"
                   value="{{ old('name', $offer->name ?? '') }}">
            <small class="error_name text-danger"></small>
          </div>

          {{-- PAYMENTS --}}
          <div class="form-group">
            <label>Վճարման ենթակա գումար <small class="validation_star">*</small></label>
            <input type="number"
                   name="payments"
                   class="form-control"
                   min="0"
                   step="1000"
                   value="{{ old('payments', $offer->payments ?? '') }}"
                   placeholder="Օրինակ` 50000">
            <small class="error_payments text-danger"></small>
          </div>

        </div>

        <div class="card-footer text-right">
          <button type="button"
                  class="btn btn-primary"
                  onclick="saveOtherOffer()">
            Պահպանել
          </button>
        </div>

      </form>

    </div>
  </div>
</div>

<script>
  window.otherOfferEdit = {
    isEdit: @json(isset($offer)),
    schoolId: @json(old('school_id', $offer->school_id ?? null)),
    selectedGroupIds: @json(old('group_ids', $selectedGroupIds ?? [])),
  };
</script>

@endsection

@push('scripts')
  <script src="{{ asset('dist/js/other.offers.form.js') }}" defer></script>
@endpush
