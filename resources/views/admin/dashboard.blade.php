@extends('admin.layouts.main')

@section('content')

<div class="row">
  <div class="col-lg-3 col-6">
    <div class="small-box bg-primary">
      <div class="inner">
        <h3>{{ number_format($paymentsMonth, 0, '.', ' ') }} ֏</h3>
        <p>Վճարումներ — այս ամիս</p>
      </div>
      <div class="icon">
        <i class="fas fa-coins"></i>
      </div>
      <a href="{{ route('admin.payment.index') }}" class="small-box-footer">
        Մանրամասն <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  <div class="col-lg-3 col-6">
    <div class="small-box bg-success">
      <div class="inner">
        <h3>{{ number_format($paymentsToday, 0, '.', ' ') }} ֏</h3>
        <p>Վճարումներ — այսօր</p>
      </div>
      <div class="icon">
        <i class="fas fa-calendar-day"></i>
      </div>
      <a href="{{ route('admin.payment.index') }}" class="small-box-footer">
        Մանրամասն <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  <div class="col-lg-3 col-6">
    <div class="small-box bg-warning">
      <div class="inner">
        <h3>{{ $pendingCount }}</h3>
        <p>Սպասման մեջ վճարումներ</p>
      </div>
      <div class="icon">
        <i class="fas fa-hourglass-half"></i>
      </div>
      <a href="{{ route('admin.payment.index') }}" class="small-box-footer">
        Մանրամասն <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  <div class="col-lg-3 col-6">
    <div class="small-box bg-danger">
      <div class="inner">
        <h3>{{ number_format($totalDebts, 0, '.', ' ') }} ֏</h3>
        <p>Ընդհանուր պարտքեր</p>
      </div>
      <div class="icon">
        <i class="fas fa-exclamation-circle"></i>
      </div>
      <a href="{{ route('admin.student.index') }}" class="small-box-footer">
        Մանրամասն <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-3 col-6">
    <div class="small-box bg-info">
      <div class="inner">
        <h3>{{ $activeStudents }}</h3>
        <p>Ակտիվ աշակերտներ</p>
      </div>
      <div class="icon">
        <i class="fas fa-user-graduate"></i>
      </div>
      <a href="{{ route('admin.student.index') }}" class="small-box-footer">
        Ցուցակ <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>
</div>

@endsection
