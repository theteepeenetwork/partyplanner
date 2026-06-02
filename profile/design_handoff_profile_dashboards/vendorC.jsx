// Vendor · Direction C — Command Center (dense ops console + sticky rail)
function VendorCommand() {
  const d = window.FYE.vendor;
  const s = d.stats;
  const kpis = [
    { l: 'Open req', ic: 'fa-inbox', v: s.pending, d: '2 urgent', cls: 'dn' },
    { l: 'Upcoming', ic: 'fa-calendar-check', v: s.upcoming, d: '+3 booked', cls: 'up' },
    { l: 'This month', ic: 'fa-pound-sign', v: '£6.5k', d: '▲ 41%', cls: 'up' },
    { l: 'Avg reply', ic: 'fa-reply', v: s.response, d: 'top 18%', cls: 'up' },
    { l: 'Views', ic: 'fa-eye', v: '1.2k', d: '▲ 12%', cls: 'up' },
  ];
  return (
    <div className="fye stack" style={{ minHeight: '100%' }}>
      <FyeTop role="vendor" />
      <FyeTabs tabs={['Main', 'Services', 'Bookings', 'Calendar', 'Host profile', 'Quotes']} active="Main" />
      <div className="cc-body">
        {/* MAIN */}
        <div className="cc-main">
          <div className="cc-head">
            <h1>Kitchen command centre</h1>
            <div className="meta">{s.pending} requests open · next payout 04 Jun</div>
          </div>

          <div className="cc-kpis">
            {kpis.map((k) => (
              <div className="cc-kpi" key={k.l}>
                <div className="l"><i className={`fa-solid ${k.ic}`} style={{ color: 'var(--terra)' }}></i>{k.l}</div>
                <div className="v">{k.v}</div>
                <div className={`d ${k.cls}`}>{k.d}</div>
              </div>
            ))}
          </div>

          <div className="cc-panel">
            <div className="cc-panel-h">
              <div className="t"><i className="fa-solid fa-inbox" style={{ color: 'var(--terra)' }}></i> Requests to action <span className="ct">{s.pending}</span></div>
              <div className="cc-filters"><span className="on">All</span><span>Urgent</span><span>This week</span></div>
            </div>
            {d.requests.map((r) => (
              <div className="cc-row" key={r.who + r.svc} style={{ gridTemplateColumns: '4px 38px 1fr auto auto auto', gap: 13 }}>
                <div className={`cc-prio ${r.prio}`}></div>
                <div className="cc-ic ic terra"><i className="fa-solid fa-utensils"></i></div>
                <div><div className="ti">{r.svc} · {r.guests} covers</div><div className="me">{r.who} — {r.ev}</div></div>
                <div className="when num">{r.date}</div>
                <div className="num" style={{ fontWeight: 800, fontSize: 13 }}>£{r.val.toLocaleString()}</div>
                <a className="btn primary sm" style={{ padding: '6px 11px' }}>Accept</a>
              </div>
            ))}
          </div>

          <div style={{ marginTop: 20 }} className="cc-panel">
            <div className="cc-panel-h"><div className="t"><i className="fa-solid fa-heart-pulse" style={{ color: 'var(--terra)' }}></i> Service health</div><a className="faint" style={{ fontSize: 12, fontWeight: 700 }}>Manage</a></div>
            {d.services.map((sv) => {
              const done = [sv.desc, sv.img, sv.price, sv.policy].filter(Boolean).length;
              return (
                <div className="cc-row" key={sv.title} style={{ gridTemplateColumns: '4px 1fr 120px auto', gap: 14 }}>
                  <div className="cc-prio lo" style={{ background: done === 4 ? 'var(--sage)' : 'var(--gold)' }}></div>
                  <div><div className="ti">{sv.title}</div><div className="me">{sv.bookings} bookings all-time</div></div>
                  <div className="ev-prog" style={{ alignSelf: 'center' }}><div className="bar"><div className="fill" style={{ width: `${done / 4 * 100}%`, background: done === 4 ? 'var(--sage)' : 'var(--gold)' }}></div></div></div>
                  <div className="when num">{done}/4</div>
                </div>
              );
            })}
          </div>
        </div>

        {/* RAIL */}
        <div className="cc-rail">
          <div className="cc-rblock">
            <h3>Payouts</h3>
            <div className="cc-gauge">
              <div className="lbl"><span className="muted">Settled</span><b className="num">£{d.payouts.settled.toLocaleString()}</b></div>
              <div className="track"><div className="seg" style={{ width: '81%', background: 'var(--sage)' }}></div></div>
              <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: 11, fontSize: 12.5 }}>
                <span className="muted">Pending</span><b className="num">£{d.payouts.pending.toLocaleString()}</b>
              </div>
              <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: 7, fontSize: 12.5 }}>
                <span className="muted">Next payout</span><b className="num">{d.payouts.next}</b>
              </div>
            </div>
          </div>

          <div className="cc-rblock">
            <h3>Upcoming</h3>
            {d.upcoming.map((b) => (
              <div className="cc-mini" key={b.ev}>
                <div className="db"><div className="m">{b.m}</div><div className="d">{b.d}</div></div>
                <div><div className="nm">{b.ev}</div><div className="sb">{b.who} · {b.loc}</div></div>
              </div>
            ))}
          </div>

          <div className="cc-rblock">
            <h3>Recent activity</h3>
            {d.activity.map((a) => (
              <div className="act" key={a.t} style={{ borderBottom: '1px solid var(--line)' }}>
                <span className="ad" style={{ background: `var(--${a.dot})` }}></span>
                <span style={{ flex: 1, fontSize: 12.5 }}>{a.t}</span>
              </div>
            ))}
          </div>

          <div className="cc-rblock">
            <h3>Quick actions</h3>
            <div className="cc-quick">
              <a><i className="fa-solid fa-plus"></i>Add service</a>
              <a><i className="fa-solid fa-calendar-day"></i>Availability</a>
              <a><i className="fa-solid fa-comments"></i>Messages</a>
              <a><i className="fa-solid fa-chart-line"></i>Analytics</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
Object.assign(window, { VendorCommand });
