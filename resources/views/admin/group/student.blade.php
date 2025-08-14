@extends('admin.layouts.main')
@section('content')

  
<div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
  <div class="d-flex align-items-center">
      <a href="{{ url()->previous() }}" class="btn btn-light btn-sm mr-2" title="Հետ վերադարձ">
          <i class="fas fa-arrow-left"></i>
      </a>
        <h3 class="mb-0">
          Խումբ 
          <small class="text-muted">{{ auth()->user()->school->name }}</small>
      </h3>
  </div>
    <a href="{{ route('admin.group.create') }}" class="btn btn-success">
        <i class="fas fa-plus me-1"></i> Ավելացնել
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body bg-white">
        <table class="table table-striped table-bordered dtTbl" style="width:100%" id="studenetsListTbl" data-id="{{ $groupId }}">
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

@endsection

<div class="modal fade" id="studentGroupRepeatModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <form id="singleRepeatForm">
        <div class="modal-header">
          <h5 class="modal-title">Տեղափոխել մեկ այլ խումբ</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="group_id"></label>
                <select class="form-control" name="group_id" id="groupId">
                    <option value="" selected disabled>Ընտրել</option>
                        @foreach($groupsData as $group)
                            <option value="{{ $group->id }}" >
                                {{ $group->name }}
                            </option>
                        @endforeach
                </select>
                <small class="error_group_id text-danger"></small>
                <small class="error_student_id text-danger"></small>
            </div>
            <input type="hidden" name="student_id" id="studentId">
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">Փակել</button>
          <button type="button" class="btn btn-primary" id="singleRepeatBtn" onclick="saveStudentRepeat()">Պահպանել</button>
        </div>
      </form>
    </div>
  </div>
</div>