@extends('admin.layouts.main')
@section('content')

<div class="card shadow-sm mb-4 border-0">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col d-flex align-items-center">
               
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 bg-transparent p-0">

                        @if(optional(auth()->user()->school)->name)
                            <li class="breadcrumb-item d-flex align-items-center">
                                <i class="fas fa-university text-primary mr-1 fa-lg"></i>
                                <span>{{ auth()->user()->school->name }}</span>
                            </li>
                        @else
                            <li class="breadcrumb-item active d-flex align-items-center" aria-current="page">
                                <i class="fas fa-university text-primary mr-1 fa-lg"></i>
                                <span>Բոլոր ուսումնական հաստատությունները</span>
                            </li>
                        @endif

                    </ol>
                </nav>
            </div>

            <div class="col-auto">
                <a href="{{ route('admin.room.create') }}" 
                   class="btn btn-success shadow-sm">
                    <i class="fas fa-plus"></i> Ավելացնել
                </a>
            </div>

        </div>
    </div>
</div>


@if(Auth::user()->hasRole('super-admin'))
  <div class="card mb-3">
      <div class="card-body">
          <div class="form-inline">
              <label for="school_id" class="mr-2 mb-0">Ընտրել ուս․ հաստատություն</label>
              <select id="school_id" class="form-control">
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
        <table class="table table-striped table-bordered dtTbl" style="width:100%" id="room">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Անվանումը</th>
                    <th>Ուս․ հաստատություն</th>
                    <th>Գործողություն</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

@endsection
<script>
  window.currentUserRole = @json(Auth::user()->getRoleNames()[0] ?? null);
</script>

<script src="{{ asset('dist/js/room/room.table.js') }}" defer></script>
