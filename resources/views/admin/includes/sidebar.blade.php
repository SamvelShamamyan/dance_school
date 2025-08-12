 <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">

    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
      <img src="{{asset('dist/img/AdminLTELogo.png')}}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">TMBLY TASHI</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">

    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="{{ asset('../../dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</a>
        </div>
    </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class with font-awesome or any other icon font library -->
        @role('super-admin')
          <li class="nav-item">
              <a href="{{route('admin.school.index')}}" class="nav-link {{ request()->routeIs('admin.school.index') ? 'active' : '' }}">
                <i class="nav-icon fas fa-th"></i>            
                  Ավաելացնել </br>ուս. հաստատություն          
              </a>
          </li>
          <li class="nav-item">
              <a href="{{route('admin.user.index')}}" class="nav-link {{ request()->routeIs('admin.user.index') ? 'active' : '' }}">
                <i class="nav-icon fas fa-th"></i>
                <p>
                Համակարգողներ
                </p>
              </a>
          </li>
        @endrole
        @role('school-admin')
          <li class="nav-item">
              <a href="{{route('admin.group.index')}}" class="nav-link {{ request()->routeIs('admin.group.index') ? 'active' : '' }}">
                <i class="nav-icon fas fa-th"></i>
                <p>
                  Խմբեր
                </p>
              </a>
          </li>
           <li class="nav-item">
              <a href="{{route('admin.staff.index')}}" class="nav-link {{ request()->routeIs('admin.staff.index') ? 'active' : '' }}">
                <i class="nav-icon fas fa-th"></i>
                <p>
                  Աշխատակազմ
                </p>
              </a>
          </li>
          <li class="nav-item">
              <a href="{{route('admin.student.index')}}" class="nav-link {{ request()->routeIs('admin.student.index') ? 'active' : '' }}">
                <i class="nav-icon fas fa-th"></i>
                <p>
                  Աշակերտներ
                </p>
              </a>
          </li>
      @endrole
      @role('school-accountant|super-admin')
        <li class="nav-item">
            <a href="{{route('admin.payment.index')}}" class="nav-link {{ request()->routeIs('admin.payment.index') ? 'active' : '' }}">
              <i class="nav-icon fas fa-th"></i>            
                Վճարումներ          
            </a>
        </li>
      @endrole
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

