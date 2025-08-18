<style>
.fancy-logo { text-align:center; margin-bottom: 1.25rem; }
.fancy-logo .brand-link{
  display:inline-flex; align-items:center; gap:.75rem;
  text-decoration:none; color:#fff;
  padding:.6rem 1rem; border-radius:14px;
  background: linear-gradient(135deg,#4f46e5 0%, #06b6d4 50%, #22c55e 100%);
  box-shadow: 0 10px 20px rgba(79,70,229,.25);
  position:relative; overflow:hidden; transition:.25s ease;
}
.fancy-logo .brand-link::after{
  content:""; position:absolute; inset:0;
  background: radial-gradient(120px 60px at 10% 0%, rgba(255,255,255,.25), transparent 60%),
              radial-gradient(140px 80px at 90% 100%, rgba(255,255,255,.15), transparent 60%);
  pointer-events:none;
}
.fancy-logo .brand-link:hover{ transform: translateY(-2px); box-shadow:0 14px 28px rgba(79,70,229,.32); }

.fancy-logo .logo-badge{
  width:42px; height:42px; border-radius:12px;
  background: rgba(255,255,255,.18);
  display:inline-flex; align-items:center; justify-content:center;
  backdrop-filter: blur(6px);
  box-shadow: inset 0 0 0 1px rgba(255,255,255,.25);
  flex-shrink:0;
}
.fancy-logo .logo-badge i{ font-size:1.15rem; }

.fancy-logo .brand-text{ display:flex; align-items:baseline; gap:.35rem; line-height:1; }
.fancy-logo .brand-text strong{
  font-weight:800; letter-spacing:.2px; font-size:1.15rem;
  text-shadow: 0 1px 0 rgba(0,0,0,.08);
}
.fancy-logo .brand-text span{ font-weight:600; opacity:.95; }

.fancy-logo .decor-line{
  margin:.75rem auto .35rem; width:min(280px,80%);
  height:1px;
  background: linear-gradient(90deg, transparent, rgba(0,0,0,.15), transparent);
  position:relative;
}
.fancy-logo .decor-line::after{
  content:""; position:absolute; left:50%; top:-3px; transform:translateX(-50%);
  width:60px; height:7px; border-radius:999px;
  background: linear-gradient(90deg, rgba(255,255,255,.85), rgba(255,255,255,.4));
  opacity:.7;
}

.fancy-logo .brand-subtitle{
  display:block; color:#6b7280; font-size:.85rem; letter-spacing:.2px;
}

.dark-mode .fancy-logo .brand-subtitle{ color:#9aa0a6; }
.dark-mode .fancy-logo .decor-line{ background: linear-gradient(90deg, transparent, rgba(255,255,255,.18), transparent); }

</style>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AdminLTE 3 | Log in</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="../../plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <!-- <div class="login-logo">
    <a href="../../index2.html"><b>CRM</b>Համակարգ</a>
  </div> -->

<div class="login-logo fancy-logo">
  <a href="#" class="brand-link">
    <span class="logo-badge"><i class="fas fa-cube"></i></span>
    <span class="brand-text">
      <strong>CRM</strong><span>Համակարգ</span>
    </span>
  </a>

  <div class="decor-line" aria-hidden="true"></div>
  <small class="brand-subtitle">Դպրոցի կառավարման վահանակ</small>
</div>

  <!-- /.login-logo -->
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg"></p>
    
      @if ($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form action="{{ route('login') }}" method="POST">
        @csrf
        <div class="input-group mb-3">
          <input type="email" name="email" class="form-control" placeholder="Էլ․ հասցե">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" name="password" class="form-control" placeholder="Գաղտնաբառ">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-8">
            <div class="icheck-primary">
              <input type="checkbox" name="remember" id="remember">
              <label for="remember">Հիշել ինձ</label>
            </div>
          </div>
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block">Մուտք</button>
          </div>
        </div>
      </form>
      
    </div>
    <!-- /.login-card-body -->
  </div>
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.min.js"></script>
</body>
</html>
