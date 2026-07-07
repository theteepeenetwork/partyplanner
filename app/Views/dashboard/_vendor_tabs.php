<ul class="nav dashboard-tabs border-bottom mb-4" role="tablist">
    <li class="nav-item">
        <a class="nav-link <?= ($currentTab ?? '') === 'main' ? 'active' : '' ?>" href="/profile">Main</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($currentTab ?? '') === 'bookings' ? 'active' : '' ?>" href="/profile/bookings">Bookings</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($currentTab ?? '') === 'services' ? 'active' : '' ?>" href="/profile/services">Services</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($currentTab ?? '') === 'calendar' ? 'active' : '' ?>" href="/profile/calendar">Calendar</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($currentTab ?? '') === 'earnings' ? 'active' : '' ?>" href="/profile/earnings">Earnings</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($currentTab ?? '') === 'host-profile' ? 'active' : '' ?>" href="/profile/host-profile">Host profile</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($currentTab ?? '') === 'my-site' ? 'active' : '' ?>" href="/profile/my-site">My site</a>
    </li>
</ul>
