// Shared brand chrome for the mockups
const { useState } = React;

function FyeTop({ role }) {
  const isV = role === 'vendor';
  return (
    <div className="fye-top">
      <div className="fye-logo">
        <span>For <span className="acc">Your</span></span>
        <span>Events</span>
      </div>
      <div className="fye-topnav">
        <a>{isV ? 'How it works' : 'Find suppliers'}</a>
        <a>{isV ? 'My services' : 'How it works'}</a>
        <a>{isV ? 'My bookings' : 'My events'}</a>
        <a>My account</a>
        <a className="cta">{isV ? 'Add a service' : 'Start planning'}</a>
        <div className="fye-avatar">{isV ? 'RK' : 'AO'}</div>
      </div>
    </div>
  );
}

function FyeTabs({ tabs, active }) {
  return (
    <div className="fye-tabs">
      {tabs.map((t) => <a key={t} className={t === active ? 'on' : ''}>{t}</a>)}
    </div>
  );
}

Object.assign(window, { FyeTop, FyeTabs });
