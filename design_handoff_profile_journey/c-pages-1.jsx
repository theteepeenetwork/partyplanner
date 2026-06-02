// Customer pages — dashboard, my events, event detail
const CC = () => window.PROTO.customer;

function statusCounts(bookings) {
  const c = { pending: 0, accepted: 0, confirmed: 0, declined: 0, awaiting: 0 };
  bookings.forEach((b) => { c[b.status]++; if (b.status === 'accepted' && !b.depositPaid) c.awaiting++; });
  return c;
}

function CustomerDashboard() {
  const d = CC();
  const sc = statusCounts(d.bookings);
  const stats = [
    { v: sc.pending, l: 'Pending', ic: 'fa-clock', tone: 'gold', to: 'c/bookings' },
    { v: sc.accepted, l: 'Accepted', ic: 'fa-check', tone: 'sage', to: 'c/bookings' },
    { v: sc.awaiting, l: 'Awaiting pay', ic: 'fa-pound-sign', tone: 'terra', to: 'c/payments' },
    { v: sc.confirmed, l: 'Confirmed', ic: 'fa-calendar-check', tone: 'slate', to: 'c/bookings' },
    { v: sc.declined, l: 'Declined', ic: 'fa-xmark', tone: 'plum', to: 'c/bookings' },
  ];
  const events = [...d.events].sort((a, b) => a.days - b.days);
  const attention = [
    { tone: 'sage', ic: 'fa-check-circle', t: 'Vendor accepted a booking', dd: 'The Roaming Kitchen accepted your catering request', cta: 'Review', to: 'c/booking/b1' },
    { tone: 'gold', ic: 'fa-credit-card', t: 'Deposit required', dd: '2 bookings are accepted and awaiting your deposit', cta: 'Pay now', to: 'c/payments' },
    { tone: 'slate', ic: 'fa-envelope', t: 'New messages', dd: '3 unread messages from your suppliers', cta: 'Open', to: 'c/messages' },
    { tone: 'terra', ic: 'fa-times-circle', t: 'A request was declined', dd: 'Sax & The City is unavailable on 14 Aug — find an alternative', cta: 'Browse', to: 'c/browse' },
  ];
  const msgs = Object.entries(d.threads).slice(0, 3);
  return (
    <div className="ra-body page page-wide">
      <div className="ra-head">
        <h1 style={{ fontFamily: 'var(--display)', fontWeight: 600, fontSize: 30, letterSpacing: '-.01em' }}>Welcome back, Amara 👋</h1>
        <p style={{ marginTop: 6, color: 'var(--ink-2)', fontSize: 14.5, maxWidth: 640 }}>Your private planning hub. Bookings, messages and payments live in one place, so you always know what’s next.</p>
      </div>

      <div className="ra-countdowns">
        {events.map((e, i) => (
          <Link to={`c/event/${e.id}`} className={`cd clickable ${i === 0 ? 'lead' : ''}`} key={e.id}>
            <div className="cd-top">
              <span className="pill accepted">{e.type}</span>
              {i === 0 && <span className="cd-flag"><i className="fa-solid fa-arrow-right"></i> Up next</span>}
            </div>
            <div className="cd-title">{e.title}</div>
            <div className="cd-num"><b className="num">{e.days}</b><span>days to go</span></div>
            <div className="cd-meta"><i className="fa-solid fa-calendar-day"></i>{e.short} · {e.loc}</div>
          </Link>
        ))}
      </div>

      <div className="ra-stats">
        {stats.map((x) => (
          <Link to={x.to} className="ra-stat clickable" key={x.l}>
            <div className={`ic ${x.tone}`}><i className={`fa-solid ${x.ic}`}></i></div>
            <div className="v num">{x.v}</div>
            <div className="l">{x.l}</div>
          </Link>
        ))}
      </div>

      <div className="ra-grid">
        <div className="ra-col">
          <div className="card">
            <h2><i className="fa-solid fa-circle-exclamation"></i> Needs your attention</h2>
            <div style={{ marginTop: 16 }}>
              {attention.map((a) => (
                <div className={`att ${a.tone}`} key={a.t}>
                  <div className={`ai ic ${a.tone}`}><i className={`fa-solid ${a.ic}`}></i></div>
                  <div><div className="at">{a.t}</div><div className="ad">{a.dd}</div></div>
                  <Link to={a.to} className="btn ghost sm aa">{a.cta}</Link>
                </div>
              ))}
            </div>
          </div>

          <div className="card">
            <div className="card-head">
              <div><h2><i className="fa-solid fa-calendar"></i> My events</h2><div className="sub">Each event holds the bookings and budget for that celebration.</div></div>
              <Link to="c/event/new" className="btn primary sm"><i className="fa-solid fa-plus"></i> New event</Link>
            </div>
            {events.map((e) => {
              const evb = d.bookings.filter((b) => b.ev === e.id && b.status !== 'declined');
              return (
                <Link to={`c/event/${e.id}`} className="ev clickable" key={e.id} style={{ display: 'block' }}>
                  <div className="ev-top">
                    <div>
                      <div className="ev-title">{e.title}</div>
                      <div className="ev-meta">
                        <span><i className="fa-solid fa-calendar-day"></i>{e.short}</span>
                        <span><i className="fa-solid fa-location-dot"></i>{e.loc}</span>
                        <span><i className="fa-solid fa-user-group"></i>{e.guests} guests</span>
                      </div>
                    </div>
                    <span className="pill accepted">{e.type}</span>
                  </div>
                  <div className="ev-prog">
                    <div className="lbl"><span>Suppliers booked</span><span className="num">{evb.length} of {d.planning[e.id].length}</span></div>
                    <div className="bar"><div className="fill" style={{ width: `${evb.length / d.planning[e.id].length * 100}%` }}></div></div>
                  </div>
                </Link>
              );
            })}
          </div>
        </div>

        <div className="ra-col">
          <Link to="c/payments" className="card clickable" style={{ display: 'block' }}>
            <h2><i className="fa-solid fa-credit-card"></i> Payment summary</h2>
            <div style={{ marginTop: 14 }}>
              <div className="kv"><span className="k">Deposits paid</span><span className="v num">{money(d.money.deposits)}</span></div>
              <div className="kv"><span className="k">Remaining balance</span><span className="v num">{money(d.money.total - d.money.deposits)}</span></div>
              <div className="kv total"><span className="k" style={{ fontWeight: 700 }}>Total event spend</span><span className="v num">{money(d.money.total)}</span></div>
            </div>
          </Link>

          <div className="card">
            <h2><i className="fa-solid fa-comments"></i> Messages</h2>
            <div style={{ marginTop: 10 }}>
              {msgs.map(([id, t]) => {
                const last = t.msgs[t.msgs.length - 1];
                return (
                  <Link to={`c/messages/${id}`} className="msg clickable" key={id} style={{ display: 'flex' }}>
                    <div className="av">{t.vi}</div>
                    <div style={{ minWidth: 0 }}><div className="who">{t.vendor}</div><div className="snip">{last.t}</div></div>
                    <div className="t">{last.time.split(' ').pop()}{last.who === 'them' && <span className="dot"></span>}</div>
                  </Link>
                );
              })}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

function CustomerEvents() {
  const d = CC();
  return (
    <div className="page">
      <div className="page-head-row">
        <div><h1 className="page-title">My events</h1><p className="page-sub">Every celebration you’re planning. Open one to see its suppliers, budget and gaps.</p></div>
        <Link to="c/event/new" className="btn primary"><i className="fa-solid fa-plus"></i> New event</Link>
      </div>
      <div className="gal">
        {[...d.events].sort((a, b) => a.days - b.days).map((e) => {
          const evb = d.bookings.filter((b) => b.ev === e.id && b.status !== 'declined');
          return (
            <Link to={`c/event/${e.id}`} className="gcard clickable" key={e.id} style={{ display: 'block' }}>
              <PH label={e.loc} style={{ height: 130 }} />
              <div className="gc-body">
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                  <span className="pill accepted">{e.type}</span>
                  <span className="faint num" style={{ fontSize: 12, fontWeight: 700 }}>{e.days} days</span>
                </div>
                <div className="gn" style={{ marginTop: 9 }}>{e.title}</div>
                <div className="gc"><i className="fa-solid fa-calendar-day" style={{ color: 'var(--terra)', marginRight: 6 }}></i>{e.date}</div>
                <div className="ev-prog" style={{ marginTop: 12 }}>
                  <div className="lbl"><span>{evb.length} of {d.planning[e.id].length} booked</span><span className="num">{money(e.budget)}</span></div>
                  <div className="bar"><div className="fill" style={{ width: `${evb.length / d.planning[e.id].length * 100}%` }}></div></div>
                </div>
              </div>
            </Link>
          );
        })}
      </div>
    </div>
  );
}

function CustomerEvent({ params }) {
  const d = CC();
  const e = d.events.find((x) => x.id === params[0]) || d.events[0];
  const booked = d.bookings.filter((b) => b.ev === e.id);
  const live = booked.filter((b) => b.status !== 'declined');
  const cats = d.planning[e.id];
  const bookedCats = new Set(live.map((b) => b.cat));
  const spent = live.filter((b) => b.depositPaid).reduce((a, b) => a + b.deposit, 0);
  const committed = live.reduce((a, b) => a + b.amount, 0);
  return (
    <div className="page">
      <Back to="c/events">All events</Back>
      <div className="hero-band">
        <PH label={`${e.loc}`} />
        <div className="hb-body">
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: 14 }}>
            <div>
              <span className="pill accepted">{e.type}</span>
              <h1 style={{ marginTop: 10 }}>{e.title}</h1>
              <div className="hb-meta">
                <span><i className="fa-solid fa-calendar-day"></i>{e.date}</span>
                <span><i className="fa-solid fa-location-dot"></i>{e.loc}</span>
                <span><i className="fa-solid fa-user-group"></i>{e.guests} guests</span>
                <span><i className="fa-solid fa-hourglass-half"></i>{e.days} days to go</span>
              </div>
            </div>
            <Link to="c/browse" className="btn primary"><i className="fa-solid fa-plus"></i> Add a service</Link>
          </div>
        </div>
      </div>

      <div className="detail">
        <div className="col">
          <div className="icard">
            <h3><i className="fa-solid fa-handshake"></i> Booked suppliers</h3>
            <div className="csub">{live.length} of {cats.length} categories covered for this event.</div>
            <div style={{ marginTop: 14 }}>
              {live.map((b) => (
                <Link to={`c/booking/${b.id}`} className="srow clickable" key={b.id} style={{ display: 'flex' }}>
                  <div className="si"><i className={`fa-solid ${b.icon}`}></i></div>
                  <div><div className="sn">{b.vendor}</div><div className="sc">{b.cat} · {b.svc}</div></div>
                  <div className="right"><StatusPill status={b.status} /><div className="sc num" style={{ marginTop: 4 }}>{money(b.amount)}</div></div>
                </Link>
              ))}
            </div>
          </div>

          <div className="icard">
            <h3><i className="fa-solid fa-list-check"></i> Still to arrange</h3>
            <div className="csub">Categories without a confirmed supplier yet.</div>
            <div style={{ display: 'flex', flexWrap: 'wrap', gap: 9, marginTop: 14 }}>
              {cats.map((c) => {
                const has = bookedCats.has(c);
                return (
                  <span key={c} className={`pill ${has ? 'confirmed' : 'action'}`} style={{ fontSize: 12.5, padding: '6px 12px' }}>
                    <i className={`fa-solid ${has ? 'fa-check' : 'fa-plus'}`} style={{ marginRight: 2 }}></i>{c}
                  </span>
                );
              })}
            </div>
          </div>
        </div>

        <div className="col">
          <div className="icard budget">
            <h3><i className="fa-solid fa-wallet"></i> Budget</h3>
            <div style={{ display: 'flex', alignItems: 'baseline', gap: 8, margin: '12px 0 14px' }}>
              <span className="num" style={{ fontFamily: 'var(--display)', fontSize: 32, fontWeight: 600, color: 'var(--terra-deep)' }}>{money(committed)}</span>
              <span className="muted" style={{ fontSize: 13 }}>committed of {money(e.budget)}</span>
            </div>
            <div className="track">
              <div className="seg" style={{ width: `${spent / e.budget * 100}%`, background: 'var(--sage)' }}></div>
              <div className="seg" style={{ width: `${(committed - spent) / e.budget * 100}%`, background: 'var(--gold)' }}></div>
            </div>
            <div className="legend">
              <span><i className="fa-solid fa-circle" style={{ color: 'var(--sage)' }}></i>Paid {money(spent)}</span>
              <span><i className="fa-solid fa-circle" style={{ color: 'var(--gold)' }}></i>Due {money(committed - spent)}</span>
              <span><i className="fa-solid fa-circle" style={{ color: 'var(--paper-2)' }}></i>Unallocated {money(Math.max(0, e.budget - committed))}</span>
            </div>
            <Link to="c/payments" className="btn ghost sm block" style={{ marginTop: 16 }}>View payments</Link>
          </div>

          <div className="icard">
            <h3><i className="fa-solid fa-clipboard-list"></i> At a glance</h3>
            <div style={{ marginTop: 10 }}>
              <div className="kv"><span className="k">Guests</span><span className="v num">{e.guests}</span></div>
              <div className="kv"><span className="k">Suppliers booked</span><span className="v num">{live.length}</span></div>
              <div className="kv"><span className="k">Awaiting your action</span><span className="v num">{live.filter((b) => b.status === 'accepted' && !b.depositPaid).length}</span></div>
              <div className="kv"><span className="k">Days remaining</span><span className="v num">{e.days}</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

window.PAGES['c/dashboard'] = CustomerDashboard;
window.PAGES['c/events'] = CustomerEvents;
window.PAGES['c/event'] = CustomerEvent;
