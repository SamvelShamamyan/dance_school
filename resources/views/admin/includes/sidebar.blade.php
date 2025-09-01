<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">

<a href="{{ route('admin.dashboard') }}" class="brand-link p-0" style="background-color: #1e1e2f;">
  <div style="
      background-image: url('{{ asset('dist/img/backgroud_iamge.jpg') }}');
      background-size: cover;
      background-position: center;
      height: 100px;
      position: relative;
  ">
    <div style="
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        background: rgba(0, 0, 0, 0.6);
        padding: 0.4rem 1rem;
        display: flex;
        align-items: center; /* 🛠 ВЫРАВНИВАЕТ ПО ВЕРТИКАЛИ */
        gap: 0.5rem;
    ">
      <div class="text-white" style="font-size: 1rem; font-weight: bold;">Sofi Devoyan</div>
      <div class="text-white-50" style="font-size: -0.2rem; line-height: 1;">Dance School</div>
    </div>
  </div>
</a>

  <!-- Sidebar -->
  <div class="sidebar">

    @php
        $user = Auth::user();
        $first = trim((string)($user->first_name ?? ''));
        $last  = trim((string)($user->last_name  ?? ''));
        $initials = mb_strtoupper(
            mb_substr($first, 0, 1) . mb_substr($last, 0, 1),
            'UTF-8'
        );
    @endphp

    <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
      <div class="image">
        <span class="avatar-square mr-2">{{ $initials }}</span>
      </div>
      <div class="info">
        <a href="#" class="d-block text-truncate" title="{{ $first }} {{ $last }}" >
          {{ $first }} {{ $last }}
        </a>
      </div>
    </div>

    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview"
          role="menu" data-accordion="false">

        @role('super-admin')
          <li class="nav-item">
            <a href="{{route('admin.school.index')}}"
               class="nav-link {{ request()->routeIs('admin.school.index') ? 'active' : '' }}">
              <i class="nav-icon fas fa-university"></i> <!-- icon for schools -->
              <p>Ուս․ հաստատություններ</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{route('admin.user.index')}}"
               class="nav-link {{ request()->routeIs('admin.user.index') ? 'active' : '' }}">
              <i class="nav-icon fas fa-user-shield"></i> <!-- coordinators -->
              <p>Համակարգողներ</p>
            </a>
          </li>
        @endrole

        @role('school-admin|super-admin')
          <li class="nav-item">
            <a href="{{route('admin.group.index')}}"
               class="nav-link {{ request()->routeIs('admin.group.index') ? 'active' : '' }}">
              <i class="nav-icon fas fa-users"></i> <!-- groups -->
              <p>Խմբեր</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{route('admin.staff.index')}}"
               class="nav-link {{ request()->routeIs('admin.staff.index') ? 'active' : '' }}">
              <i class="nav-icon fas fa-briefcase"></i> <!-- staff -->
              <p>Աշխատակազմ</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{route('admin.student.index')}}"
               class="nav-link {{ request()->routeIs('admin.student.index') ? 'active' : '' }}">
              <i class="nav-icon fas fa-user-graduate"></i> <!-- students -->
              <p>Աշակերտներ</p>
            </a>
          </li>
        @endrole

        @role('school-accountant|super-admin|super-accountant')
          <li class="nav-item">
            <a href="{{route('admin.payment.index')}}"
               class="nav-link {{ request()->routeIs('admin.payment.index') ? 'active' : '' }}">
              <i class="nav-icon fas fa-coins"></i> <!-- payments -->
              <p>Վճարումներ</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="{{route('admin.deleted.students.index')}}"
               class="nav-link {{ request()->routeIs('admin.deleted.students.index') ? 'active' : '' }}">
              <i class="nav-icon fas fa-archive"></i> 
              <p>Հեռացված աշակերտներ</p>
            </a>
          </li>

        @endrole 

      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>
