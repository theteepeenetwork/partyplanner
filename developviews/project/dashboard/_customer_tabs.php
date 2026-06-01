<ul class="nav dashboard-tabs border-bottom mb-4" role="tablist">
    <li class="nav-item">
        <a class="nav-link <?= ($currentTab ?? '') === 'main' ? 'active' : '' ?>" href="/profile">Main</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($currentTab ?? '') === 'events' ? 'active' : '' ?>" href="/profile/events">My Events</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($currentTab ?? '') === 'bookings' ? 'active' : '' ?>" href="/profile/my-bookings">Bookings</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($currentTab ?? '') === 'messages' ? 'active' : '' ?>" href="/profile/messages">Messages</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($currentTab ?? '') === 'payments' ? 'active' : '' ?>" href="/profile/payments">Payments</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($currentTab ?? '') === 'favourites' ? 'active' : '' ?>" href="/profile/favourites">Favourites</a>
    </li>
</ul>
