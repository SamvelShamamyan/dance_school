@extends('admin.layouts.main')
@section('content')

<div class="card shadow-sm mb-4 border-0">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col d-flex align-items-center">
                <a href="{{ url()->previous() }}" 
                   class="btn btn-outline-secondary btn-sm mr-3" 
                   title="Հետ վերադարձ">
                    <i class="fas fa-arrow-left"></i>
                </a>
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
                <a href="{{ route('admin.staff.create') }}" 
                   class="btn btn-success shadow-sm" id="addStaffBtn">
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