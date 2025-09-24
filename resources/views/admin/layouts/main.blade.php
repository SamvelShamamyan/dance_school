<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>CRM Համակարգ</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
   <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="{{ asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
  <!-- iCheck -->
  <link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
  <!-- JQVMap -->
  <link rel="stylesheet" href="{{ asset('plugins/jqvmap/jqvmap.min.css') }}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="{{ asset('plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="{{ asset('plugins/daterangepicker/daterangepicker.css') }}">
  <!-- summernote -->
  <link rel="stylesheet" href="{{ asset('plugins/summernote/summernote-bs4.min.css') }}">
  
  
<!-- CURRENT START_3 -->
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<!-- Select2 -->
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css">
<link rel="stylesheet" href="{{ asset('dist/css/main.css') }}">
<link rel="stylesheet" href="{{ asset('dist/css/scheduler.group.css') }}?v=2">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<!-- <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css"> -->





<!-- CURRENT START_3 -->

</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
    </ul>

   

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn-gradient" type="submit">Ելք</button>
        </form>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->

  <div id="preloader">
  <div class="spinner"></div>
  <div class="loader-text">Բեռնվում է...</div>
</div>

    @include('admin.includes.sidebar')
    
  

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0"></h1>
          </div>
          <div class="col-sm-6">
            
          </div>
        </div>
      </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
   

        @yield('content')


      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
<footer class="main-footer">
  @php($start = 2025)
  @php($year  = now()->year)
  <strong>
     Հեղինակային իրավունքներ &copy;
    {{ $start }}@if($year > $start)–{{ $year }}@endif
    <a href="{{ url('/') }}">CRM Համակարգ</a>
  </strong>
  <span>: Բոլոր իրավունքները պաշտպանված են։</span>
  <div class="float-right d-none d-sm-inline-block">
    <b>Version</b> {{ config('app.version', '1.0.0') }}
  </div>
</footer>


  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
<!-- jQuery UI 1.11.4 -->
<script src="{{ asset('plugins/jquery-ui/jquery-ui.min.js') }}"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- ChartJS -->
<script src="{{ asset('plugins/chart.js/Chart.min.js') }}"></script>
<!-- Sparkline -->
<script src="{{ asset('plugins/sparklines/sparkline.js') }}"></script>

<!-- CURRENT START_1 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>

<!-- <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap4.min.js"></script> -->


<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<!-- CURRENT END_1 -->



<!-- JQVMap -->
<script src="{{ asset('plugins/jqvmap/jquery.vmap.min.js') }}"></script>
<script src="{{ asset('plugins/jqvmap/maps/jquery.vmap.usa.js') }}"></script>
<!-- jQuery Knob Chart -->
<script src="{{ asset('plugins/jquery-knob/jquery.knob.min.js') }}"></script>
<!-- daterangepicker -->
<script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('plugins/moment/locale/hy-am.js') }}"></script>
<script src="{{ asset('plugins/daterangepicker/daterangepicker.js') }}"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="{{ asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
<!-- Summernote -->
<script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
<!-- overlayScrollbars -->
<script src="{{ asset('plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('dist/js/adminlte.js') }}"></script>
<!-- AdminLTE for demo purposes -->
<script src="{{ asset('dist/js/demo.js') }}"></script> 

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>


<!-- CURRENT START_2 -->
<script src="{{ asset('dist/js/helper.js') }}"></script>
<script src="{{ asset('dist/js/main.js') }}"></script>
<script src="{{ asset('dist/js/school.js') }}"></script>
<script src="{{ asset('dist/js/user.js') }}"></script>
<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
<script>
if (window.Dropzone) { Dropzone.autoDiscover = false; }
</script>
<script src="{{ asset('dist/js/group.js') }}"></script>
<script src="{{ asset('dist/js/staff.js') }}?v=2"></script>
<script src="{{ asset('dist/js/student.js') }}?v=2"></script>
<!-- <script src="{{ asset('dist/js/payment.js') }}"></script> -->
<!-- <script src="{{ asset('dist/js/payment.student.js') }}"></script> -->

<script src="{{ asset( 'dist/js/settings.js') }}"></script>


<!-- CURRENT END_2 -->


</body>
</html>                                       
