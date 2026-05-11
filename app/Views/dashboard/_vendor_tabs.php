<ul class="nav dashboard-tabs border-bottom mb-4" role="tablist">
    <li class="nav-item">
        <a class="nav-link <?= ($currentTab ?? '') === 'main' ? 'active' : '' ?>" href="/profile">Main</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($currentTab ?? '') === 'services' ? 'active' : '' ?>" href="/profile/services">Services</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($currentTab ?? '') === 'bookings' ? 'active' : '' ?>" href="/profile/bookings">Bookings</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($currentTab ?? '') === 'calendar' ? 'active' : '' ?>" href="/profile/calendar">Calendar</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($currentTab ?? '') === 'messages' ? 'active' : '' ?>" href="/profile/messages">Messages</a>
    </li>
</ul>
