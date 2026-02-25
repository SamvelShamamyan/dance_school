@php
    $hasTodayBirthday = false;
@endphp

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
                            @php
                                $birth = \Carbon\Carbon::parse($st->birth_date);

                                // ВАЖНО: сегодня ли у конкретного ученика
                                $isTodayStudent = ($currentDate->day === $birth->day) && ($currentDate->month === $birth->month);

                                // Для кнопки: если хоть один сегодня — true
                                if ($isTodayStudent) {
                                    $hasTodayBirthday = true;
                                }
                            @endphp

                            @if($isTodayStudent)
                                <input type="hidden" name="student_ids[]" value="{{ $st->id }}">
                            @endif

                            <div class="col-md-4 col-lg-3 mb-3">
                                <div class="{{ $st->this_year_send_congratulation_email ? 'birthday-card-sended' : 'birthday-card' }}
                                            {{ $isTodayStudent ? 'birthday-card-today' : '' }}
                                            d-flex align-items-center justify-content-between p-3">

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
                                                🎈 {{ $birth->age }} տարեկան
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column align-items-end">
                                        <span class="badge badge-warning badge-pill mb-1">
                                            {{ $birth->format('d.m') }}

                                            @if($isTodayStudent)
                                                <span class="badge badge-today ml-1">ԱՅՍՕՐ</span>
                                            @endif
                                        </span>

                                        @if($st->this_year_send_congratulation_email)
                                            <i class="fas fa-check-circle text-success" data-toggle="tooltip" title="Շնորհավորական նամակը ուղարկված է"></i>
                                        @else
                                            <i class="fas fa-hourglass-half text-warning" data-toggle="tooltip" title="Նամակը դեռ չի ուղարկվել"></i>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if(
                        ($fromMonthFilter === null && $toMonthFilter === null) ||
                        ($fromMonthFilter !== null && (int)$currentDate->month === (int)$fromMonthFilter) ||
                        ($toMonthFilter !== null && (int)$currentDate->month === (int)$toMonthFilter)
                    )
                        @if($hasTodayBirthday)
                            <div class="text-center mt-3">
                                <button type="button"
                                        id="sendcongratulations"
                                        class="btn btn-warning btn-sm px-4 py-2 shadow-sm text-white"
                                        onclick="sendCongratulations()">
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    Ուղարկել շնորհավորական նամակ
                                </button>
                            </div>
                        @else
                            <div class="text-center mt-3 text-muted small">
                                <i class="fas fa-info-circle mr-1"></i>
                                <span class="text-muted dashed-text">
                                    Այսօր աշակերտի ծննդյան օր առկա չէ, շնորհավորանքը հասանելի չէ:
                                </span>
                            </div>
                        @endif
                    @else
                        <div class="text-center mt-3 text-muted small">
                            <i class="fas fa-info-circle mr-1"></i>
                            <span class="text-muted dashed-text">
                                Դուք չեք կարող շնորհավորական նամակ ուղարկել նախորդող կամ հաջորդող ամիսների համար։
                            </span>
                        </div>
                    @endif
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
