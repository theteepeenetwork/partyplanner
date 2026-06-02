// Customer · Direction A — Refined (brand-cohesive, tighter density)
function CustomerRefined() {
  const d = window.FYE.customer;
  const s = d.stats;
  const stats = [
    { v: s.pending, l: 'Pending', ic: 'fa-clock', tone: 'gold' },
    { v: s.accepted, l: 'Accepted', ic: 'fa-check', tone: 'sage' },
    { v: s.awaiting, l: 'Awaiting pay', ic: 'fa-pound-sign', tone: 'terra' },
    { v: s.confirmed, l: 'Confirmed', ic: 'fa-calendar-check', tone: 'slate' },
    { v: s.declined, l: 'Declined', ic: 'fa-xmark', tone: 'plum' },
  ];
  return (
    <div className="fye stack" style={{ minHeight: '100%' }}>
      <FyeTop role="customer" />
      <FyeTabs tabs={['Main', 'My events', 'Bookings', 'Messages', 'Payments', 'Favourites']} active="Main" />
      <div className="ra-body">
        <div className="ra-head">
          <h1>Welcome back, Amara <span className="wave">👋</span></h1>
          <p>Your private planning hub. Bookings, messages and payments live in one place, so you always know what’s next.</p>
        </div>

        <div className="ra-countdowns">
          {[...d.events].sort((a, b) => a.days - b.days).map((e, i) => (
            <div className={`cd ${i === 0 ? 'lead' : ''}`} key={e.title}>
              <div className="cd-top">
                <span className="pill accepted">{e.type}</span>
                {i === 0 && <span className="cd-flag"><i className="fa-solid fa-arrow-right"></i> Up next</span>}
              </div>
              <div className="cd-title">{e.title}</div>
              <div className="cd-num"><b className="num">{e.days}</b><span>days to go</span></div>
              <div className="cd-meta"><i className="fa-solid fa-calendar-day"></i>{e.date} · {e.loc}</div>
            </div>
          ))}
        </div>

        <div className="ra-stats">
          {stats.map((x) => (
            <div className="ra-stat" key={x.l}>
              <div className={`ic ${x.tone}`}><i className={`fa-solid ${x.ic}`}></i></div>
              <div className="v num">{x.v}</div>
              <div className="l">{x.l}</div>
            </div>
          ))}
        </div>

        <div className="ra-grid">
          {/* LEFT */}
          <div className="ra-col">
            <div className="card">
              <h2><i className="fa-solid fa-circle-exclamation"></i> Needs your attention</h2>
              <div style={{ marginTop: 16 }}>
                {d.attention.map((a) => (
                  <div className={`att ${a.tone}`} key={a.t}>
                    <div className={`ai ic ${a.tone}`}><i className={`fa-solid ${a.ic}`}></i></div>
                    <div>
                      <div className="at">{a.t}</div>
                      <div className="ad">{a.d}</div>
                    </div>
                    <a className="btn ghost sm aa">{a.cta}</a>
                  </div>
                ))}
              </div>
            </div>

            <div className="card">
              <div className="card-head">
                <div>
                  <h2><i className="fa-solid fa-calendar"></i> My events</h2>
                  <div className="sub">Each event holds the bookings and budget for that celebration.</div>
                </div>
                <a className="btn primary sm"><i className="fa-solid fa-plus"></i> New event</a>
              </div>
              {d.events.map((e) => (
                <div className="ev" key={e.title}>
                  <div className="ev-top">
                    <div>
                      <div className="ev-title">{e.title}</div>
                      <div className="ev-meta">
                        <span><i className="fa-solid fa-calendar-day"></i>{e.date}</span>
                        <span><i className="fa-solid fa-location-dot"></i>{e.loc}</span>
                        <span><i className="fa-solid fa-user-group"></i>{e.guests} guests</span>
                      </div>
                    </div>
                    <span className="pill accepted">{e.type}</span>
                  </div>
                  <div className="ev-prog">
                    <div className="lbl"><span>Key services booked</span><span className="num">{e.booked}/{e.max}</span></div>
                    <div className="bar"><div className="fill" style={{ width: `${(e.booked / e.max) * 100}%` }}></div></div>
                  </div>
                  {e.cost > 0 && <div style={{ marginTop: 11, fontSize: 13 }} className="muted">Estimated spend <b style={{ color: 'var(--ink)' }} className="num">£{e.cost.toLocaleString()}</b></div>}
                </div>
              ))}
            </div>
          </div>

          {/* RIGHT */}
          <div className="ra-col">
            <div className="card">
              <h2><i className="fa-solid fa-credit-card"></i> Payment summary</h2>
              <div style={{ marginTop: 14 }}>
                <div className="kv"><span className="k">Deposits paid</span><span className="v num">£{d.money.deposits.toLocaleString()}</span></div>
                <div className="kv"><span className="k">Remaining balance</span><span className="v num">£{d.money.remaining.toLocaleString()}</span></div>
                <div className="kv total"><span className="k" style={{ fontWeight: 700 }}>Total event spend</span><span className="v num">£{d.money.total.toLocaleString()}</span></div>
              </div>
            </div>

            <div className="card">
              <h2><i className="fa-solid fa-comments"></i> Messages</h2>
              <div style={{ marginTop: 10 }}>
                {d.messages.map((m) => (
                  <div className="msg" key={m.who}>
                    <div className="av">{m.i}</div>
                    <div style={{ minWidth: 0 }}>
                      <div className="who">{m.who}</div>
                      <div className="snip">{m.snip}</div>
                    </div>
                    <div className="t">{m.t}{m.unread && <span className="dot"></span>}</div>
                  </div>
                ))}
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  );
}
Object.assign(window, { CustomerRefined });
