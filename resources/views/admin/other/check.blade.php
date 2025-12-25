@extends('admin.layouts.main')

@section('content')

<div class="card shadow-sm mb-4 border-0">
  <div class="card-body">
    <div class="row align-items-center">
      <div class="col d-flex align-items-center">
        <a href="{{ route('admin.otherOffers.index') }}"
           class="btn btn-outline-secondary btn-sm mr-3 btn-icon"
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
    </div>
  </div>
</div>

@if($studentsList->isEmpty())
  <div class="alert alert-info d-flex align-items-center mb-0" role="alert">
    <i class="fas fa-info-circle fa-lg mr-2"></i>
    <span>Տվյալները բացակայում են</span>
  </div>
@else

  <div class="row">
    <div class="col-12 col-md-12 col-lg-12 col-xl-6">
      <div class="card card-primary">

        <div class="card-header">
          <div class="d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0 font-weight-bold">
              Աշակերտների ցուցակ ըստ խմբերի
            </h3>

            <span class="badge badge-info badge-pill px-3 py-2">
              {{ $otherOffer->name }}
            </span>
          </div>
        </div>

        <div class="card-body">
          <form method="POST" action="#">
            @csrf

            <div id="accordionStudents">

              @foreach($studentsList as $groupId => $items)
                @php
                  $collapseId = 'collapse-group-' . $loop->index;
                  $first = $items->first();
                  $otherOfferGroupId = $otherOfferGroupMap[$groupId] ?? null;
                @endphp

                <div class="card mb-2">
                  <div class="card-header" id="heading-{{ $loop->index }}">
                    <h5 class="mb-0">
                      <button
                        class="btn btn-link d-flex align-items-center justify-content-between w-100 text-left"
                        type="button"
                        data-toggle="collapse"
                        data-target="#{{ $collapseId }}"
                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                        aria-controls="{{ $collapseId }}">

                        <div class="d-flex align-items-center flex-wrap">
                          <i class="fas fa-university text-primary mr-2"></i>
                          <strong class="mr-2">
                            {{ $otherOffer->school->name }}
                          </strong>

                          <span class="text-muted mx-2">•</span>

                          <i class="fas fa-users text-success mr-2"></i>
                          <span class="font-weight-semibold">
                            {{ $first->group_name ?? ('Group #' . $groupId) }}
                          </span>
                        </div>

                        <div class="d-flex align-items-center count-wrap">
                          <span class="text-muted small mr-2 count-label">Աշակերտներ</span>

                          <span class="badge badge-info badge-pill px-3 count-badge">
                            {{ $items->count() }}
                          </span>

                          <i class="fas fa-chevron-down ml-3 text-muted"></i>
                        </div>

                      </button>
                    </h5>
                  </div>

                  <div
                    id="{{ $collapseId }}"
                    class="collapse {{ $loop->first ? 'show' : '' }}"
                    aria-labelledby="heading-{{ $loop->index }}"
                    data-parent="#accordionStudents">

                    <div class="card-body">
                      <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                          <thead>
                            <tr>
                              <th style="width:220px;">ԱԱՀ</th>
                              <th class="text-center" style="width:220px;">
                                Վճարումը կատարվե՞լ է
                                <br>
                                <small class="text-muted">
                                  Նշիչը նշված լինելու դեպքում՝ վճարումը համարվում է կատարված
                                </small>
                              </th>
                            </tr>
                          </thead>

                          <tbody>
                            @foreach($items as $st)
                              @php
                                $status = (int) ($paidMap[$otherOfferGroupId][$st->id] ?? 0);
                                $isPaid = $status === 1;
                              @endphp

                              <tr class="{{ $isPaid ? 'tr-disabled' : '' }}">
                                <td>{{ $st->full_name }}</td>

                                <td class="text-center">
                                  <div class="icheck-success d-inline-flex align-items-center">
                                    <input
                                      type="checkbox"
                                      class="js-paid"
                                      name="paid[{{ $st->id }}]"
                                      id="paid-{{ $st->id }}"
                                      value="1"
                                      {{ $isPaid ? 'checked disabled' : '' }}
                                    >
                                    <label for="paid-{{ $st->id }}" class="mb-0">
                                      Այո / Ոչ
                                    </label>
                                  </div>
                                </td>
                              </tr>
                            @endforeach
                          </tbody>

                        </table>
                      </div>
                    </div>

                    <div class="card-footer text-right">
                      <button type="button"
                              class="btn btn-primary js-save-group"
                              data-group-id="{{ $groupId }}"
                              data-other-offer-group-id="{{ $otherOfferGroupId }}"
                              data-collapse="#{{ $collapseId }}">
                        Պահպանել
                      </button>
                    </div>

                  </div>
                </div>
              @endforeach

            </div>
          </form>
        </div>

      </div>
    </div>
  </div>

@endif

@endsection

@push('scripts')
  <script src="{{ asset('dist/js/other.offers.form.js') }}" defer></script>
@endpush
