@php
  $isSuper = Auth::user()->hasRole('super-admin');
@endphp

@extends('admin.layouts.main')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
  <div class="d-flex align-items-center">
        <h3 class="mb-0">
            <small class="text-muted">
                <i class="fas fa-school mr-1"></i> {{ optional(auth()->user()->school)->name  ? 'Ուս․ հաստատություն →  '.auth()->user()->school->name : 'Բոլոր ուսումնական հաստատությունները' }}
            </small>
        </h3>
  </div>
     <a href="{{ route('admin.group.create') }}" class="btn btn-success" id="addGroupBtn">
        <i class="fas fa-plus me-1"></i> Ավելացնել
    </a>
</div>


@if(Auth::user()->hasRole('super-admin'))
  <div class="card mb-3">
      <div class="card-body">
          <div class="form-inline">
              <label for="filterSchoolGroup" class="mr-2 mb-0">Ընտրել ուս․ հաստատություն</label>
              <select id="filterSchoolGroup" class="form-control">
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
      <table class="table table-striped table-bordered dtTbl" style="width:100%" id="groupTbl">
          <thead>
              <tr>
                  <th>ID</th>
                  <th>Խմբի անուն</th>
                  <th>Ուս․ հաստատություն</th>
                  <th>Գործողություն</th>
              </tr>
          </thead>
          <tbody>
          </tbody>
      </table>
  </div>
</div>

  <!-- Modal -->
<div class="modal fade" id="studentGroupModal" tabindex="-1" role="dialog" aria-labelledby="studentGroupModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document"> 
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="studentGroupModalLabel">Ավելացնել խմբում նոր աշակերտ (ներ)</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Փակել">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="SutdentGroupModalForm">
          @csrf
          <input type="hidden" name="group_id" id="group_id">
           
            @if(Auth::user()->hasRole('super-admin'))      
                
                <input type="hidden" name="school_id" id="school_id">
                <div class="form-group">
                  <label></label>
                  <select name="add_student[]" id="addStudentsGroup" class="form-control select2" multiple="multiple" data-placeholder="Ընտրել" style="width: 100%;">      
                  </select>
                  <small class="error_add_student text-danger"></small>
                </div>  

              @else

              <div class="form-group">
                <label></label>
                <select name="add_student[]" id="addStudentsGroup" class="form-control select2" multiple="multiple" data-placeholder="Ընտրել" style="width: 100%;">    
                </select>
                <small class="error_add_student text-danger"></small>
              </div>

            @endif

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Փակել</button>
        <button type="button" class="btn btn-primary" id="addStudentGroupBtn" onclick="addStudentGroup()">Պահպանել</button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="staffGroupModal" tabindex="-1" role="dialog" aria-labelledby="staffGroupModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document"> 
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="staffGroupModalLabel">Ավելացնել խմբում նոր աշխատակից(պարուսույց)</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Փակել">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="StaffGroupModalForm"> 
          @csrf
          <input type="hidden" name="group_id" id="group_staff_id">
          @if(Auth::user()->hasRole('super-admin'))
              <input type="hidden" name="school_id" id="school_id">
              <div class="form-group">
                    <label></label>
                    <select name="add_staff[]" id="addStaffGroup" class="form-control select2" multiple="multiple" data-placeholder="Ընտրել" style="width: 100%;">
                    
                    </select>
                    <small class="error_add_staff text-danger"></small>
                </div>
            @else  
            <div class="form-group">
              <label></label>
              <select name="add_staff[]" id="addStaffGroup" class="form-control select2" multiple="multiple" data-placeholder="Ընտրել" style="width: 100%;">
              
              </select>
              <small class="error_add_staff text-danger"></small>
            </div>
          @endif

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Փակել</button>
        <button type="button" class="btn btn-primary" id="addStaffGroupBtn" onclick="addStaffGroup()">Պահպանել</button>
      </div>
    </div>
  </div>
</div>

@endsection

<script>
  window.currentUserRole = @json(Auth::user()->getRoleNames()[0] ?? null);
</script>