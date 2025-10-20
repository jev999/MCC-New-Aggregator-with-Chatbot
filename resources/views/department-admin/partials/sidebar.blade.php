<!-- Mobile Toggle Button -->
<button class="mobile-toggle" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" onclick="closeSidebar()"></div>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-building"></i> Department Admin</h3>
        <div class="dept-info">{{ $admin->department }} Department</div>
    </div>
    <ul class="sidebar-menu">
        <li><a href="{{ route('department-admin.dashboard') }}" class="{{ request()->routeIs('department-admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-pie"></i> <span>Dashboard</span>
        </a></li>
        <li><a href="{{ route('department-admin.announcements.index') }}" class="{{ request()->routeIs('department-admin.announcements.*') ? 'active' : '' }}">
            <i class="fas fa-bullhorn"></i> <span>Announcements</span>
        </a></li>
        <li><a href="{{ route('department-admin.events.index') }}" class="{{ request()->routeIs('department-admin.events.*') ? 'active' : '' }}">
            <i class="fas fa-calendar-alt"></i> <span>Events</span>
        </a></li>
        <li><a href="{{ route('department-admin.news.index') }}" class="{{ request()->routeIs('department-admin.news.*') ? 'active' : '' }}">
            <i class="fas fa-newspaper"></i> <span>News</span>
        </a></li>
    </ul>
</div>
