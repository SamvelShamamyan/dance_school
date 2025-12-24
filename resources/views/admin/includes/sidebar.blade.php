<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">

<a href="{{ route('admin.dashboard') }}" class="brand-link p-0 brand-custom">
  <div class="brand-bg">
    <div class="brand-overlay d-flex align-items-center">
      <div class="brand-title">Sofi Devoyan</div>
      <div class="brand-subtitle">Dance School</div>
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

        @role('super-admin|school-admin')
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
          <li class="nav-item">
            <a href="{{route('admin.room.index')}}"
               class="nav-link {{ request()->routeIs('admin.room.index') ? 'active' : '' }}">
              <i class="nav-icon fas fa-door-open"></i> 
              <p>Դահլիճներ</p>
            </a>
          </li>
        @endrole

        @role('super-admin|super-accountant|school-admin|school-accountant')
          <li class="nav-item">
            <a href="{{route('admin.payment.index')}}"
               class="nav-link {{ request()->routeIs('admin.payment.index') ? 'active' : '' }}">
              <i class="nav-icon fas fa-coins"></i> <!-- payments -->
              <p>Վճարումներ</p>
            </a>
          </li>
        @endrole 

        @role('super-admin|school-admin')
          <li class="nav-item">
            <a href="{{route('admin.deleted.students.index')}}"
               class="nav-link {{ request()->routeIs('admin.deleted.students.index') ? 'active' : '' }}">
              <i class="nav-icon fas fa-archive"></i> 
              <p>Հեռացված աշակերտներ</p>
            </a>
          </li>
        @endrole 
        @role('super-admin|school-admin')
          <li class="nav-item">
            <a href="{{route('admin.schedule.group.index')}}"
               class="nav-link {{ request()->routeIs('admin.schedule.group.index') ? 'active' : '' }}">
              <i class="nav-icon fas fa-calendar"></i> 
              <p>Դասացուցակ</p>
            </a>
          </li>
        @endrole 

        @role('super-admin|school-admin')
          <li class="nav-item">
            <a href="{{route('admin.student.attendances.index')}}"
               class="nav-link {{ request()->routeIs('admin.student.attendances.index') ? 'active' : '' }}">
              <i class="nav-icon fas fa-user-check"></i> 
              <p>Ներկա-բացակա</p>
            </a>
          </li>

           <li class="nav-item">
            <a href="{{route('admin.otherOffers.index')}}"
               class="nav-link {{ request()->routeIs('admin.otherOffers.index') ? 'active' : '' }}">
              <i class="nav-icon fas fa-th-large"></i>
              <p>Այլ</p>
            </a>
          </li>

        @endrole 

      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>
