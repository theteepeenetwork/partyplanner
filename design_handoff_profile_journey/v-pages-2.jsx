// Vendor pages — services, service editor, earnings, calendar, messages, host profile
const VV2 = () => window.PROTO.vendor;

function VendorServices() {
  const d = VV2();
  return (
    <div className="page">
      <div className="page-head-row">
        <div><h1 className="page-title">My services</h1><p className="page-sub">The listings customers can request. Complete listings convert more enquiries.</p></div>
        <Link to="v/service/new" className="btn primary"><i className="fa-solid fa-plus"></i> Add a service</Link>
      </div>
      <div className="gal">
        {d.services.map((sv) => {
          const checks = [['Description', sv.hasDesc], ['Photos', sv.hasImg], ['Pricing', sv.hasPrice], ['Cancellation policy', sv.hasPolicy]];
          const done = checks.filter((c) => c[1]).length;
          return (
            <Link to={`v/service/${sv.id}`} className="gcard clickable" key={sv.id} style={{ display: 'block' }}>
              {sv.hasImg ? <PH label="service photo" /> : <div className="ph" style={{ height: 120, background: 'var(--gold-tint)', color: 'var(--gold)' }}><i className="fa-solid fa-image" style={{ fontSize: 20 }}></i></div>}
              <div className="gc-body">
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                  <div className="gn">{sv.title}</div>
                  <span className={`pill ${done === 4 ? 'confirmed' : 'pending'}`} style={{ fontSize: 11 }}>{done}/4</span>
                </div>
                <div className="gc" style={{ marginTop: 4 }}>from £{sv.priceFrom} {sv.unit} · {sv.bookings} bookings</div>
                <div className="ev-prog" style={{ marginTop: 12 }}><div className="bar"><div className="fill" style={{ width: `${done / 4 * 100}%`, background: done === 4 ? 'var(--sage)' : 'var(--gold)' }}></div></div></div>
              </div>
            </Link>
          );
        })}
      </div>
    </div>
  );
}

function VendorService({ params }) {
  const d = VV2();
  const isNew = params[0] === 'new';
  const sv = isNew ? { title: '', desc: '', priceFrom: '', unit: 'per head', hasDesc: false, hasImg: false, hasPrice: false, hasPolicy: false } : (d.services.find((x) => x.id === params[0]) || d.services[0]);
  const Field = ({ label, val, done, ph, area }) => (
    <div style={{ marginBottom: 18 }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 7 }}>
        <span style={{ fontSize: 12.5, fontWeight: 700 }}>{label}</span>
        <span className={`pill ${done ? 'confirmed' : 'pending'}`} style={{ fontSize: 10.5 }}>{done ? 'Complete' : 'To do'}</span>
      </div>
      <div style={{ background: 'var(--paper-2)', borderRadius: 10, padding: area ? '14px 15px' : '12px 15px', fontSize: 13.5, color: val ? 'var(--ink)' : 'var(--ink-3)', minHeight: area ? 64 : 'auto' }}>{val || ph}</div>
    </div>
  );
  return (
    <div className="page" style={{ maxWidth: 760 }}>
      <Back to="v/services">My services</Back>
      <div className="page-head-row">
        <div><h1 className="page-title">{isNew ? 'New service' : sv.title}</h1><p className="page-sub">{isNew ? 'Create a listing customers can request.' : 'Edit this listing. Complete every section to maximise enquiries.'}</p></div>
        {!isNew && <span className="pill confirmed" style={{ alignSelf: 'center' }}>Live</span>}
      </div>
      <div className="icard">
        <Field label="Service title" val={sv.title} done={!!sv.title} ph="e.g. Wedding Feast Package" />
        <Field label="Description" val={sv.desc} done={sv.hasDesc} ph="Describe what's included…" area />
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
          <Field label="Price from (£)" val={sv.priceFrom || ''} done={sv.hasPrice} ph="32" />
          <Field label="Unit" val={sv.unit} done={sv.hasPrice} ph="per head" />
        </div>
        <div style={{ marginBottom: 18 }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 7 }}><span style={{ fontSize: 12.5, fontWeight: 700 }}>Photos</span><span className={`pill ${sv.hasImg ? 'confirmed' : 'pending'}`} style={{ fontSize: 10.5 }}>{sv.hasImg ? '4 photos' : 'None yet'}</span></div>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4,1fr)', gap: 10 }}>
            {[0, 1, 2, 3].map((i) => sv.hasImg && i < 3 ? <PH key={i} label="" style={{ height: 70, borderRadius: 8 }} /> : <div key={i} className="ph" style={{ height: 70, borderRadius: 8, border: '1.5px dashed var(--line-2)', background: 'transparent' }}><i className="fa-solid fa-plus faint"></i></div>)}
          </div>
        </div>
        <Field label="Cancellation policy" val={sv.hasPolicy ? 'Flexible — full refund up to 30 days before.' : ''} done={sv.hasPolicy} ph="Set your cancellation terms…" />
        <div className="actions" style={{ marginTop: 8 }}>
          <Link to="v/services" className="btn primary"><i className="fa-solid fa-check"></i> Save service</Link>
          {!isNew && <a className="btn ghost">Preview</a>}
        </div>
      </div>
    </div>
  );
}

function VendorEarnings() {
  const d = VV2();
  const max = Math.max(...d.earnings.map((e) => e.v));
  const settled = d.payouts.filter((p) => p.status === 'settled').reduce((a, p) => a + p.amount, 0);
  const pending = d.payouts.filter((p) => p.status === 'pending').reduce((a, p) => a + p.amount, 0);
  return (
    <div className="page">
      <h1 className="page-title">Earnings &amp; payouts</h1>
      <p className="page-sub" style={{ marginBottom: 22 }}>What you’ve earned, what’s settled, and what’s on the way.</p>
      <div className="minis">
        <div className="mini-stat"><div className="v num">£6.5k</div><div className="l">This month <span style={{ color: 'var(--sage)' }}>▲ 41%</span></div></div>
        <div className="mini-stat"><div className="v num" style={{ color: 'var(--sage)' }}>{money(settled)}</div><div className="l">Settled (90 days)</div></div>
        <div className="mini-stat"><div className="v num" style={{ color: 'var(--gold)' }}>{money(pending)}</div><div className="l">Pending payout</div></div>
        <div className="mini-stat"><div className="v num">£3.2k</div><div className="l">Avg / month</div></div>
      </div>

      <div className="detail">
        <div className="col">
          <div className="icard">
            <h3><i className="fa-solid fa-chart-column"></i> Six-month trend</h3>
            <div style={{ display: 'flex', alignItems: 'flex-end', gap: 16, height: 160, marginTop: 18 }}>
              {d.earnings.map((e, i) => (
                <div key={e.m} style={{ flex: 1, display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 8 }}>
                  <div className="num" style={{ fontSize: 11, fontWeight: 700, color: i === 5 ? 'var(--terra-deep)' : 'var(--ink-3)' }}>£{(e.v / 1000).toFixed(1)}k</div>
                  <div style={{ width: '100%', maxWidth: 48, height: `${e.v / max * 120}px`, background: i === 5 ? 'var(--terra)' : 'var(--terra-tint)', borderRadius: '6px 6px 0 0' }}></div>
                  <div className="muted" style={{ fontSize: 11.5, fontWeight: 600 }}>{e.m}</div>
                </div>
              ))}
            </div>
          </div>
        </div>
        <div className="col">
          <div className="icard">
            <h3><i className="fa-solid fa-building-columns"></i> Payout history</h3>
            <div style={{ marginTop: 12 }}>
              {d.payouts.map((p) => (
                <div className="srow" key={p.date} style={{ display: 'flex' }}>
                  <div className="si" style={{ background: p.status === 'settled' ? 'var(--sage-tint)' : 'var(--gold-tint)', color: p.status === 'settled' ? 'var(--sage)' : 'var(--gold)' }}><i className={`fa-solid ${p.status === 'settled' ? 'fa-check' : 'fa-clock'}`}></i></div>
                  <div><div className="sn">{money(p.amount)}</div><div className="sc">{p.ref}</div></div>
                  <div className="right"><div className="sc num">{p.date}</div></div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

function VendorCalendar() {
  const d = VV2();
  const dow = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
  return (
    <div className="page">
      <div className="page-head-row">
        <div><h1 className="page-title">Calendar &amp; availability</h1><p className="page-sub">Confirmed bookings and pencilled enquiries. Block dates you’re unavailable.</p></div>
        <a className="btn ghost"><i className="fa-solid fa-ban"></i> Block dates</a>
      </div>
      <div style={{ display: 'flex', gap: 18, marginBottom: 18, fontSize: 12.5 }}>
        <span className="hint"><span style={{ width: 12, height: 12, borderRadius: 4, background: 'var(--terra)', display: 'inline-block' }}></span> Confirmed booking</span>
        <span className="hint"><span style={{ width: 12, height: 12, borderRadius: 4, background: 'var(--gold-tint)', display: 'inline-block' }}></span> Pencilled enquiry</span>
      </div>
      <div className="detail" style={{ gridTemplateColumns: '1fr 1fr' }}>
        {Object.entries(d.calendar).map(([month, cfg]) => {
          const cells = [];
          for (let i = 0; i < cfg.first - 1; i++) cells.push(null);
          for (let day = 1; day <= cfg.days; day++) cells.push(day);
          return (
            <div className="cal" key={month}>
              <h4>{month}</h4>
              <div className="cal-grid">
                {dow.map((x) => <div className="dow" key={x}>{x}</div>)}
                {cells.map((day, i) => {
                  if (!day) return <div key={i}></div>;
                  const mark = cfg.marks[day];
                  return (
                    <div key={i} className={`cal-cell ${mark || ''} ${mark ? '' : 'has'}`}>
                      <span>{day}</span>
                      {mark && <span className="tagdot">{mark === 'B' ? 'Booked' : 'Hold'}</span>}
                    </div>
                  );
                })}
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
}

function VendorMessages({ params }) { return <ConvView threads={VV2().threads} base="v/messages" params={params} />; }

function VendorHost() {
  const d = VV2();
  const a = d.analytics;
  return (
    <div className="page">
      <div className="page-head-row">
        <div><h1 className="page-title">Host profile</h1><p className="page-sub">Your public page. This is what customers see when they discover you.</p></div>
        <a className="btn ghost"><i className="fa-solid fa-up-right-from-square"></i> View public page</a>
      </div>
      <div className="hero-band">
        <PH label="cover photo" style={{ height: 170 }} />
        <div className="hb-body" style={{ display: 'flex', alignItems: 'flex-end', gap: 18 }}>
          <div className="lava" style={{ width: 72, height: 72, fontSize: 24, marginTop: -54, border: '4px solid #fff' }}>RK</div>
          <div style={{ flex: 1 }}>
            <h1 style={{ fontSize: 26 }}>{d.biz.name}</h1>
            <div className="hb-meta" style={{ marginTop: 6 }}>
              <span><i className="fa-solid fa-utensils"></i>Caterer</span>
              <span><i className="fa-solid fa-location-dot"></i>Bath &amp; the South West</span>
              <span className="stars" style={{ color: 'var(--gold)' }}>★★★★★ <span className="muted">4.9 (48)</span></span>
            </div>
          </div>
          <a className="btn primary"><i className="fa-solid fa-pen"></i> Edit profile</a>
        </div>
      </div>

      <div className="minis">
        <div className="mini-stat"><div className="v num">{a.views.toLocaleString()}</div><div className="l">Profile views <span style={{ color: 'var(--sage)' }}>▲ {a.viewsTrend}%</span></div></div>
        <div className="mini-stat"><div className="v num">{a.conversion}%</div><div className="l">Enquiry → booking</div></div>
        <div className="mini-stat"><div className="v num">{a.response}</div><div className="l">Avg response</div></div>
        <div className="mini-stat"><div className="v num">{a.repeat}%</div><div className="l">Repeat customers</div></div>
      </div>

      <div className="sec-h"><h3>Services on your profile</h3><span className="grow"></span><Link to="v/services" className="faint" style={{ fontSize: 12, fontWeight: 700 }}>Manage</Link></div>
      <div className="gal">
        {d.services.map((sv) => (
          <Link to={`v/service/${sv.id}`} className="gcard clickable" key={sv.id} style={{ display: 'block' }}>
            <PH label="service photo" />
            <div className="gc-body"><div className="gn">{sv.title}</div><div className="gc">from £{sv.priceFrom} {sv.unit}</div></div>
          </Link>
        ))}
      </div>
    </div>
  );
}

Object.assign(window.PAGES, {
  'v/services': VendorServices, 'v/service': VendorService, 'v/earnings': VendorEarnings,
  'v/calendar': VendorCalendar, 'v/messages': VendorMessages, 'v/host': VendorHost,
});
