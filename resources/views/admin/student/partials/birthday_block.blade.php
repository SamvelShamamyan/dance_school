@if(!empty($birthdayStudentsThisMonth) && $birthdayStudentsThisMonth->count())
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="mr-3">
                    <span class="badge badge-warning p-3 text-white" style="border-radius: 12px;">
                        <i class="fas fa-birthday-cake fa-lg"></i>
                    </span>
                </div>

                <div>
                    <div class="font-weight-bold">
                        Այս ամսվա ծննդյան օրերը
                        <span class="badge badge-pill badge-warning ml-2">
                            {{ $birthdayStudentsThisMonth->count() }}
                        </span>
                    </div>
                    <div class="text-muted small">
                        Արագ ցուցակ՝ այս ամսում ծննդյան օր ունեցող աշակերտներ
                    </div>
                </div>
            </div>

            <div class="ml-auto">
                <button id="showList" class="btn btn-outline-warning btn-sm" type="button"
                        data-toggle="collapse" data-target="#birthdayListCollapse">
                    Տեսնել ցուցակը
                </button>
            </div>
        </div>

        <div class="collapse" id="birthdayListCollapse">
            <div class="card-body pt-0">
                <form id="sendCongratulationsForm" action="{{ route('admin.student.sendCongratulations') }}">
                    <div class="row">
                        @foreach($birthdayStudentsThisMonth as $st)
                            <input type="hidden" name="student_ids[]" value="{{ $st->id }}">
                            <div class="col-md-4 col-lg-3 mb-3">
                                <div class="<?= $st->this_year_send_congratulation_email == false ? 'birthday-card' : 'birthday-card-sended'?> d-flex align-items-center justify-content-between p-3">

                                    <div class="d-flex align-items-center">
                                        <div class="birthday-icon mr-3">🎂</div>

                                        <div>
                                            <div class="font-weight-semibold mb-1">
                                                {{ $st->first_name }} {{ $st->last_name }}
                                            </div>

                                            <div class="small text-muted d-flex flex-wrap align-items-center gap-2">
                                                <span class="badge badge-light">
                                                    <i class="fas fa-university mr-1 text-primary"></i>
                                                    {{ $st->school->name ?? '—' }}
                                                </span>

                                                <span class="badge badge-light">
                                                    <i class="fas fa-users mr-1 text-success"></i>
                                                    {{ $st->group->name ?? '—' }}
                                                </span>
                                            </div>

                                            <div class="text-bold small mt-1">
                                                🎈 {{ \Carbon\Carbon::parse($st->birth_date)->age }} տարեկան
                                            </div>

                                        </div>
                                    </div>
                                    <!-- <span class="badge badge-warning badge-pill">
                                        {{ \Carbon\Carbon::parse($st->birth_date)->format('d.m') }}
                                    </span> -->

<div class="d-flex flex-column align-items-end">
    <span class="badge badge-warning badge-pill mb-1">
        {{ \Carbon\Carbon::parse($st->birth_date)->format('d.m') }}
    </span>

    @if($st->this_year_send_congratulation_email)
        <i class="fas fa-check-circle text-success"
           data-toggle="tooltip"
           title="Շնորհավորական նամակը ուղարկված է"></i>
    @else
        <i class="fas fa-hourglass-half text-warning"
           data-toggle="tooltip"
           title="Նամակը դեռ չի ուղարկվել"></i>
    @endif
</div>

                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="text-center mt-3">
                       <button type="button"
                                id="sendcongratulations"
                                class="btn btn-warning btn-sm px-4 py-2 shadow-sm text-white"
                                onclick="sendCongratulations()">
                            <i class="fas fa-paper-plane mr-2"></i>
                           Ուղարկել շնորհավորական նամակ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @else

     <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="mr-3">
                    <span class="badge badge-warning p-3 text-white" style="border-radius: 12px;">
                        <i class="fas fa-birthday-cake fa-lg"></i>
                    </span>
                </div>

                <div>
                    <div class="font-weight-bold">
                        Այս ամսվա ծննդյան օրերը
                        <span class="badge badge-pill badge-warning ml-2">
                            {{ $birthdayStudentsThisMonth->count() }}
                        </span>
                    </div>
                    <div class="text-muted small">
                        Արագ ցուցակ՝ այս ամսում ծննդյան օր ունեցող աշակերտներ չկան
                    </div>
                </div>
            </div>
        </div>
    </div>

@endif
