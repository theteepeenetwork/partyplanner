// For Your Events — prototype shell: router, top bar, role-aware tabs, shared UI
const { useState, useEffect, createElement: h } = React;

/* ---------------- router ---------------- */
function parseHash() {
  const raw = (window.location.hash || '').replace(/^#\/?/, '').trim();
  return raw || 'c/dashboard';
}
function go(route) { window.location.hash = '#/' + route; }
function useRoute() {
  const [r, setR] = useState(parseHash());
  useEffect(() => {
    const on = () => { setR(parseHash()); window.scrollTo(0, 0); };
    window.addEventListener('hashchange', on);
    return () => window.removeEventListener('hashchange', on);
  }, []);
  return r;
}
function Link({ to, className = '', style, children, onClick }) {
  return (
    <a href={'#/' + to} className={className} style={style} onClick={onClick}>{children}</a>
  );
}
window.PAGES = window.PAGES || {};

/* ---------------- top bar ---------------- */
function TopBar({ role }) {
  const isV = role === 'vendor';
  return (
    <div className="fye-top">
      <Link to={isV ? 'v/dashboard' : 'c/dashboard'} className="fye-logo">
        <span>For <span className="acc">Your</span></span>
        <span>Events</span>
      </Link>
      <div className="fye-topnav">
        <div className="role-switch">
          <button className={!isV ? 'on' : ''} onClick={() => go('c/dashboard')}><i className="fa-solid fa-champagne-glasses"></i> Customer</button>
          <button className={isV ? 'on' : ''} onClick={() => go('v/dashboard')}><i className="fa-solid fa-store"></i> Vendor</button>
        </div>
        <Link to={isV ? 'v/messages' : 'c/messages'} style={{ color: 'var(--ink-2)' }}><i className="fa-solid fa-envelope"></i></Link>
        <div className="fye-avatar">{isV ? 'RK' : 'AO'}</div>
      </div>
    </div>
  );
}

const C_TABS = [
  ['Main', 'c/dashboard'], ['My events', 'c/events'], ['Bookings', 'c/bookings'],
  ['Payments', 'c/payments'], ['Messages', 'c/messages'], ['Favourites', 'c/favourites'],
];
const V_TABS = [
  ['Main', 'v/dashboard'], ['Bookings', 'v/bookings'], ['Services', 'v/services'],
  ['Calendar', 'v/calendar'], ['Earnings', 'v/earnings'], ['Host profile', 'v/host'],
];
function Tabs({ role, route }) {
  const tabs = role === 'vendor' ? V_TABS : C_TABS;
  const base = route.split('/').slice(0, 2).join('/');
  // map detail routes back to their parent tab
  const parent = {
    'c/event': 'c/events', 'c/booking': 'c/bookings',
    'v/request': 'v/bookings', 'v/service': 'v/services',
  }[base] || base;
  return (
    <div className="fye-tabs">
      {tabs.map(([label, to]) => (
        <Link key={to} to={to} className={to === parent ? 'on' : ''}>{label}</Link>
      ))}
    </div>
  );
}

/* ---------------- shared UI ---------------- */
function Back({ to, children }) {
  return <Link to={to} className="back"><i className="fa-solid fa-arrow-left"></i>{children}</Link>;
}
function StatusPill({ status }) {
  const map = {
    pending: ['pending', 'Pending'], accepted: ['accepted', 'Accepted'], confirmed: ['confirmed', 'Confirmed'],
    declined: ['declined', 'Declined'], new: ['action', 'New request'], quoted: ['pending', 'Quote sent'],
  };
  const [cls, lbl] = map[status] || ['action', status];
  return <span className={`pill ${cls}`}>{lbl}</span>;
}
function money(n) { return '£' + n.toLocaleString(); }
function Avatar({ initials, sq, tone }) {
  return <div className={`lava${sq ? ' sq' : ''}`} style={tone ? { background: `var(--${tone}-tint)`, color: `var(--${tone})` } : null}>{initials}</div>;
}
function PH({ label, style }) { return <div className="ph" style={style}>{label}</div>; }

Object.assign(window, { useRoute, go, Link, TopBar, Tabs, Back, StatusPill, money, Avatar, PH });
