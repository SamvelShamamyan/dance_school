<style>
#addStaffBtn {
    transition: all 0.3s ease;
    opacity: 1;
    transform: scale(1);
}

#addStaffBtn.hidden {
    opacity: 0;
    transform: scale(0.9);
    pointer-events: none;
}

</style>

@php
    $isSuper = Auth::user()->hasRole('super-admin');
@endphp

@extends('admin.layouts.main')
@section('content')



<div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
  <div class="d-flex align-items-center">
      <a href="{{ url()->previous() }}" class="btn btn-light btn-sm mr-2" title="Հետ վերադարձ">
          <i class="fas fa-arrow-left"></i>
      </a>
        <h3 class="mb-0">
            <small class="text-muted">
                <i class="fas fa-school mr-1"></i> {{ optional(auth()->user()->school)->name ?? 'Բոլոր ուսումնական հաստատությունները' }}
            </small>
        </h3>
  </div>
    <a href="{{  
        $isSuper ? route('admin.staff.create') : route('admin.staff.create') }}" class="btn btn-success mb-3 {{ $isSuper  ? 'hidden' : ' ' }}" id="addStaffBtn">
        <i class="fas fa-plus"></i> Ավելացնել
    </a>
</div>

@if(Auth::user()->hasRole('super-admin'))
  <div class="card mb-3">
      <div class="card-body">
          <div class="form-inline">
              <label for="filterSchool" class="mr-2 mb-0">Ընտրել ուս․ հաստատություն</label>
              <select id="filterSchool" class="form-control">
                  <option value="" selected >Բոլորը</option>
                  @foreach($schools as $school)
                      <option value="{{ $school->id }}" data-name="{{ $school->name }}">{{ $school->name }}</option>
                  @endforeach
              </select>
          </div>
      </div>
  </div>
@endif

<div class="card shadow-sm">
    <div class="card-body bg-white">
        <div class="table-responsive">
            <table class="table table-striped table-bordered dtTbl" style="width:100%" id="staffTbl">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ԱԱՀ</th>
                        <th>Բնակության հասցե</th>
                        <th>Ծննդյան ամսաթիվ</th>
                        <!-- <th>Աշխատանքի ընդունման ամսաթիվ</th> -->
                        <th>Էլ․ հասցե</th>
                        <th>ՀԾՀ</th>
                        <!-- <th>Ուս․ հաստատություն</th> -->
                        <th>Գործողություն</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

<script>
  window.currentUserRole = @json(Auth::user()->getRoleNames()[0] ?? null);
</script>