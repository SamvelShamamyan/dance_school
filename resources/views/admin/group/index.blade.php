@extends('admin.layouts.main')
@section('content')


    <a href="{{ route('admin.group.create') }}" class="btn btn-success mb-3">
        <i class="fas fa-plus"></i> Ավաելացնել
    </a>

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
        <h5 class="modal-title" id="studentGroupModalLabel">Ավելացնել խմբում նոր սան</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Փակել">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="SutdentGroupModalForm">
          @csrf
            <div class="form-group">
                <label></label>
                <select name="add_student[]" id="addStudentsGroup" class="form-control select2" multiple="multiple" data-placeholder="Ընտրել" style="width: 100%;">
                
                </select>
                <small class="error_add_student text-danger"></small>
            </div>
            <input type="hidden" name="group_id" id="group_id">
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
            <div class="form-group">
                <label></label>
                <select name="add_staff[]" id="addStaffGroup" class="form-control select2" multiple="multiple" data-placeholder="Ընտրել" style="width: 100%;">
                
                </select>
                <small class="error_add_staff text-danger"></small>
            </div>
              <input type="hidden" name="group_id" id="group_staff_id">
              <small class="error_group_id text-danger"></small>
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