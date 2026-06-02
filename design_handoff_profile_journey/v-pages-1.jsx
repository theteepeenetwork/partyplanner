// Vendor pages — dashboard (command centre), bookings queue, request detail
const VV = () => window.PROTO.vendor;

function vCounts(reqs) {
  const c = { new: 0, quoted: 0, confirmed: 0, declined: 0 };
  reqs.forEach((r) => c[r.status]++);
  return c;
}

function VendorDashboard() {
  const d = VV();
  const c = vCounts(d.requests);
  const earnMonth = d.earnings[d.earnings.length - 1].v;
  const kpis = [
    { l: 'Open req', ic: 'fa-inbox', v: c.new, d: '2 urgent', cls: 'dn', to: 'v/bookings' },
    { l: 'Upcoming', ic: 'fa-calendar-check', v: c.confirmed, d: '+3 booked', cls: 'up', to: 'v/calendar' },
    { l: 'This month', ic: 'fa-pound-sign', v: '£6.5k', d: '▲ 41%', cls: 'up', to: 'v/earnings' },
    { l: 'Avg reply', ic: 'fa-reply', v: d.analytics.response, d: 'top 18%', cls: 'up', to: 'v/host' },
    { l: 'Views', ic: 'fa-eye', v: '1.2k', d: '▲ 12%', cls: 'up', to: 'v/host' },
  ];
  const newReqs = d.requests.filter((r) => r.status === 'new' || r.status === 'quoted');
  const prioWrap = { new: 'New request', quoted: 'Quote sent' };
  const settled = d.payouts.filter((p) => p.status === 'settled').reduce((a, p) => a + p.amount, 0);
  const pending = d.payouts.filter((p) => p.status === 'pending').reduce((a, p) => a + p.amount, 0);
  const activity = [
    { dot: 'gold', t: 'New request from Amara Okafor for Wedding Feast Package', to: 'v/request/r1' },
    { dot: 'sage', t: 'Booking confirmed: Sophie Tran · Tran Wedding', to: 'v/bookings' },
    { dot: 'slate', t: 'Priya Sharma replied about Grazing Tables', to: 'v/messages/vt-priya' },
    { dot: 'terra', t: 'Payout of £1,180 settled to your account', to: 'v/earnings' },
  ];
  return (
    <div className="fye stack">
      <div className="cc-body" style={{ minHeight: 'calc(100vh - 110px)' }}>
        <div className="cc-main">
          <div className="cc-head">
            <h1>Kitchen command centre</h1>
            <div className="meta">{c.new} requests open · next payout 04 Jun</div>
          </div>

          <div className="cc-kpis">
            {kpis.map((k) => (
              <Link to={k.to} className="cc-kpi clickable" key={k.l} style={{ display: 'block' }}>
                <div className="l"><i className={`fa-solid ${k.ic}`} style={{ color: 'var(--terra)' }}></i>{k.l}</div>
                <div className="v">{k.v}</div>
                <div className={`d ${k.cls}`}>{k.d}</div>
              </Link>
            ))}
          </div>

          <div className="cc-panel">
            <div className="cc-panel-h">
              <div className="t"><i className="fa-solid fa-inbox" style={{ color: 'var(--terra)' }}></i> Requests to action <span className="ct">{newReqs.length}</span></div>
              <Link to="v/bookings" className="faint" style={{ fontSize: 12, fontWeight: 700 }}>View all</Link>
            </div>
            {newReqs.map((r) => (
              <Link to={`v/request/${r.id}`} className="cc-row clickable" key={r.id} style={{ gridTemplateColumns: '4px 38px 1fr auto auto auto', gap: 13, display: 'grid' }}>
                <div className={`cc-prio ${r.prio}`}></div>
                <div className="cc-ic ic terra"><i className="fa-solid fa-utensils"></i></div>
                <div><div className="ti">{r.svc} · {r.guests} covers</div><div className="me">{r.customer} — {r.ev}</div></div>
                <div className="when num">{r.short}</div>
                <div className="num" style={{ fontWeight: 800, fontSize: 13 }}>{money(r.value)}</div>
                <span className="pill action" style={{ fontSize: 11 }}>{prioWrap[r.status]}</span>
              </Link>
            ))}
          </div>

          <div style={{ marginTop: 20 }} className="cc-panel">
            <div className="cc-panel-h"><div className="t"><i className="fa-solid fa-heart-pulse" style={{ color: 'var(--terra)' }}></i> Service health</div><Link to="v/services" className="faint" style={{ fontSize: 12, fontWeight: 700 }}>Manage</Link></div>
            {d.services.map((sv) => {
              const done = [sv.hasDesc, sv.hasImg, sv.hasPrice, sv.hasPolicy].filter(Boolean).length;
              return (
                <Link to={`v/service/${sv.id}`} className="cc-row clickable" key={sv.id} style={{ gridTemplateColumns: '4px 1fr 120px auto', gap: 14, display: 'grid' }}>
                  <div className="cc-prio lo" style={{ background: done === 4 ? 'var(--sage)' : 'var(--gold)' }}></div>
                  <div><div className="ti">{sv.title}</div><div className="me">{sv.bookings} bookings all-time</div></div>
                  <div className="ev-prog" style={{ alignSelf: 'center' }}><div className="bar"><div className="fill" style={{ width: `${done / 4 * 100}%`, background: done === 4 ? 'var(--sage)' : 'var(--gold)' }}></div></div></div>
                  <div className="when num">{done}/4</div>
                </Link>
              );
            })}
          </div>
        </div>

        <div className="cc-rail">
          <Link to="v/earnings" className="cc-rblock clickable" style={{ display: 'block' }}>
            <h3>Payouts</h3>
            <div className="cc-gauge">
              <div className="lbl"><span className="muted">Settled</span><b className="num">{money(settled)}</b></div>
              <div className="track"><div className="seg" style={{ width: '81%', background: 'var(--sage)' }}></div></div>
              <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: 11, fontSize: 12.5 }}><span className="muted">Pending</span><b className="num">{money(pending)}</b></div>
              <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: 7, fontSize: 12.5 }}><span className="muted">Next payout</span><b className="num">04 Jun</b></div>
            </div>
          </Link>

          <div className="cc-rblock">
            <h3>Upcoming</h3>
            {d.requests.filter((r) => r.status === 'confirmed').map((b) => (
              <Link to="v/calendar" className="cc-mini clickable" key={b.id} style={{ display: 'flex' }}>
                <div className="db"><div className="m">{b.short.split(' ')[1]}</div><div className="d">{b.short.split(' ')[0]}</div></div>
                <div><div className="nm">{b.ev}</div><div className="sb">{b.customer} · {b.guests} covers</div></div>
              </Link>
            ))}
          </div>

          <div className="cc-rblock">
            <h3>Recent activity</h3>
            {activity.map((a) => (
              <Link to={a.to} className="act clickable" key={a.t} style={{ borderBottom: '1px solid var(--line)', display: 'flex' }}>
                <span className="ad" style={{ background: `var(--${a.dot})` }}></span>
                <span style={{ flex: 1, fontSize: 12.5 }}>{a.t}</span>
              </Link>
            ))}
          </div>

          <div className="cc-rblock">
            <h3>Quick actions</h3>
            <div className="cc-quick">
              <Link to="v/service/new"><i className="fa-solid fa-plus"></i>Add service</Link>
              <Link to="v/calendar"><i className="fa-solid fa-calendar-day"></i>Availability</Link>
              <Link to="v/messages"><i className="fa-solid fa-comments"></i>Messages</Link>
              <Link to="v/host"><i className="fa-solid fa-chart-line"></i>Analytics</Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

function VendorBookings() {
  const d = VV();
  const groups = [
    ['new', 'New — needs your response', 'fa-inbox'],
    ['quoted', 'Quoted — awaiting customer', 'fa-paper-plane'],
    ['confirmed', 'Confirmed & upcoming', 'fa-calendar-check'],
    ['declined', 'Declined / expired', 'fa-circle-xmark'],
  ];
  return (
    <div className="page">
      <div className="page-head-row">
        <div><h1 className="page-title">Requests &amp; bookings</h1><p className="page-sub">Every enquiry across your services, grouped by where it stands. Respond fast to win more work.</p></div>
        <Link to="v/calendar" className="btn ghost"><i className="fa-solid fa-calendar"></i> Calendar</Link>
      </div>
      {groups.map(([st, label, ic]) => {
        const rows = d.requests.filter((r) => r.status === st);
        if (!rows.length) return null;
        return (
          <div key={st}>
            <div className="glabel"><i className={`fa-solid ${ic}`}></i>{label}<span className="ln"></span><span>{rows.length}</span></div>
            {rows.map((r) => (
              <Link to={`v/request/${r.id}`} className="lrow clickable" key={r.id} style={{ gridTemplateColumns: '52px 1fr auto auto auto', gap: 14 }}>
                <div className="dchip"><div className="m">{r.short.split(' ')[1]}</div><div className="d">{r.short.split(' ')[0]}</div></div>
                <div><div className="ti">{r.svc} · {r.guests} covers</div><div className="me"><i className="fa-solid fa-user"></i>{r.customer} — {r.ev}</div></div>
                {r.prio === 'hi' && st === 'new' ? <span className="pill pending">Urgent</span> : <span style={{ width: 1 }}></span>}
                <StatusPill status={r.status} />
                <div className="amt-lg num">{money(r.value)}</div>
              </Link>
            ))}
          </div>
        );
      })}
    </div>
  );
}

function VendorRequest({ params }) {
  const d = VV();
  const r = d.requests.find((x) => x.id === params[0]) || d.requests[0];
  const thread = r.thread && d.threads[r.thread];
  const perHead = Math.round(r.value / r.guests);
  return (
    <div className="page">
      <Back to="v/bookings">Requests &amp; bookings</Back>
      <div className="detail">
        <div className="col">
          <div className="icard">
            <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
              <Avatar initials={r.ci} sq />
              <div style={{ flex: 1 }}><div style={{ fontWeight: 800, fontSize: 19 }}>{r.customer}</div><div className="muted" style={{ fontSize: 13 }}>{r.ev}</div></div>
              <StatusPill status={r.status} />
            </div>
            <div className="hb-meta" style={{ marginTop: 16 }}>
              <span><i className="fa-solid fa-utensils"></i>{r.svc}</span>
              <span><i className="fa-solid fa-calendar-day"></i>{r.date}</span>
              <span><i className="fa-solid fa-user-group"></i>{r.guests} guests</span>
            </div>
            <div className="quote" style={{ marginTop: 18 }}>
              <div className="qrow"><span className="muted">{r.svc} · {r.guests} × {money(perHead)}</span><span className="num">{money(r.value)}</span></div>
              <div className="qrow"><span className="muted">Service &amp; setup</span><span className="muted">included</span></div>
              <div className="qrow total"><span>Quote total</span><b className="num">{money(r.value)}</b></div>
            </div>
            <div className="actions" style={{ marginTop: 18 }}>
              {(r.status === 'new') && <><a className="btn primary"><i className="fa-solid fa-check"></i> Accept request</a><a className="btn ghost"><i className="fa-solid fa-file-invoice"></i> Send custom quote</a><a className="btn danger" style={{ marginLeft: 'auto' }}>Decline</a></>}
              {r.status === 'quoted' && <><span className="btn ghost"><i className="fa-solid fa-clock"></i> Awaiting customer</span><a className="btn ghost"><i className="fa-solid fa-pen"></i> Edit quote</a></>}
              {r.status === 'confirmed' && <span className="btn ghost" style={{ color: 'var(--sage)' }}><i className="fa-solid fa-check"></i> Confirmed booking</span>}
              {r.thread && <Link to={`v/messages/${r.thread}`} className="btn ghost"><i className="fa-solid fa-comment"></i> Message</Link>}
            </div>
          </div>

          {thread && (
            <div className="icard">
              <h3><i className="fa-solid fa-comments"></i> Conversation</h3>
              <div className="conv-body" style={{ background: 'transparent', padding: '16px 0 0' }}>
                {thread.msgs.slice(-3).map((m, i) => (<div className={`bubble ${m.who}`} key={i}>{m.t}<div className="bt">{m.time}</div></div>))}
              </div>
              <Link to={`v/messages/${r.thread}`} className="btn ghost sm" style={{ marginTop: 14 }}>Open conversation</Link>
            </div>
          )}
        </div>

        <div className="col">
          <div className="icard">
            <h3><i className="fa-solid fa-user"></i> Customer</h3>
            <div style={{ marginTop: 10 }}>
              <div className="kv"><span className="k">Name</span><span className="v">{r.customer}</span></div>
              <div className="kv"><span className="k">Event</span><span className="v">{r.ev}</span></div>
              <div className="kv"><span className="k">Requested</span><span className="v">{r.when}</span></div>
              <div className="kv"><span className="k">Quote expires</span><span className="v" style={{ color: 'var(--terra-deep)' }}>in 6 days</span></div>
            </div>
          </div>
          <div className="icard">
            <h3><i className="fa-solid fa-lightbulb"></i> Tip</h3>
            <div className="csub" style={{ marginTop: 8, lineHeight: 1.6 }}>Replies within 4 hours are 3× more likely to convert. This request is flagged {r.prio === 'hi' ? 'urgent' : 'standard'}.</div>
          </div>
        </div>
      </div>
    </div>
  );
}

Object.assign(window.PAGES, { 'v/dashboard': VendorDashboard, 'v/bookings': VendorBookings, 'v/request': VendorRequest });
