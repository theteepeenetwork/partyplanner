// Customer pages — bookings, booking detail, payments, messages, favourites, browse, new event
const CC2 = () => window.PROTO.customer;

function CustomerBookings() {
  const d = CC2();
  const groups = [
    ['accepted', 'Accepted — action needed', 'fa-circle-check'],
    ['pending', 'Pending — awaiting vendor', 'fa-clock'],
    ['confirmed', 'Confirmed', 'fa-calendar-check'],
    ['declined', 'Declined', 'fa-circle-xmark'],
  ];
  const evName = (id) => d.events.find((e) => e.id === id).title;
  return (
    <div className="page">
      <div className="page-head-row">
        <div><h1 className="page-title">Bookings</h1><p className="page-sub">Every supplier request across your events, grouped by where it stands.</p></div>
        <Link to="c/browse" className="btn primary"><i className="fa-solid fa-magnifying-glass"></i> Find suppliers</Link>
      </div>
      {groups.map(([st, label, ic]) => {
        const rows = d.bookings.filter((b) => b.status === st);
        if (!rows.length) return null;
        return (
          <div key={st} id={st}>
            <div className="glabel"><i className={`fa-solid ${ic}`}></i>{label}<span className="ln"></span><span>{rows.length}</span></div>
            {rows.map((b) => (
              <Link to={`c/booking/${b.id}`} className="lrow clickable" key={b.id} style={{ gridTemplateColumns: '42px 1fr auto auto' }}>
                <Avatar initials={b.vi} sq />
                <div><div className="ti">{b.vendor}</div><div className="me">{b.cat} · {b.svc} — <span style={{ color: 'var(--ink-3)' }}>{evName(b.ev)}</span></div></div>
                <StatusPill status={b.status} />
                <div className="amt-lg num">{money(b.amount)}</div>
              </Link>
            ))}
          </div>
        );
      })}
    </div>
  );
}

function CustomerBooking({ params }) {
  const d = CC2();
  const b = d.bookings.find((x) => x.id === params[0]) || d.bookings[0];
  const ev = d.events.find((e) => e.id === b.ev);
  const thread = b.thread && d.threads[b.thread];
  const balance = b.amount - (b.depositPaid ? b.deposit : 0);
  return (
    <div className="page">
      <Back to="c/bookings">All bookings</Back>
      <div className="detail">
        <div className="col">
          <div className="icard">
            <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
              <Avatar initials={b.vi} sq />
              <div style={{ flex: 1 }}>
                <div style={{ fontWeight: 800, fontSize: 19 }}>{b.vendor}</div>
                <div className="muted" style={{ fontSize: 13 }}>{b.cat} · for {ev.title}</div>
              </div>
              <StatusPill status={b.status} />
            </div>
            <div className="quote" style={{ marginTop: 18 }}>
              <div className="qrow"><span className="muted">{b.svc}</span><span className="num">{money(b.amount)}</span></div>
              <div className="qrow"><span className="muted">Deposit (15%)</span><span className="num">{money(b.deposit || Math.round(b.amount * 0.15))}</span></div>
              <div className="qrow"><span className="muted">{b.depositPaid ? 'Deposit paid' : 'Balance on the day'}</span><span className="num">{money(balance)}</span></div>
              <div className="qrow total"><span>Total</span><b className="num">{money(b.amount)}</b></div>
            </div>
            <div className="actions" style={{ marginTop: 18 }}>
              {b.status === 'accepted' && !b.depositPaid && <Link to="c/payments" className="btn primary"><i className="fa-solid fa-lock"></i> Pay deposit {money(b.deposit)}</Link>}
              {b.status === 'pending' && <span className="btn ghost"><i className="fa-solid fa-clock"></i> Awaiting vendor reply</span>}
              {b.status === 'confirmed' && <span className="btn ghost" style={{ color: 'var(--sage)' }}><i className="fa-solid fa-check"></i> Confirmed & paid</span>}
              {b.thread && <Link to={`c/messages/${b.thread}`} className="btn ghost"><i className="fa-solid fa-comment"></i> Message vendor</Link>}
              {b.status === 'declined' && <Link to="c/browse" className="btn primary"><i className="fa-solid fa-rotate"></i> Find alternative</Link>}
            </div>
          </div>

          {thread && (
            <div className="icard">
              <h3><i className="fa-solid fa-comments"></i> Recent messages</h3>
              <div className="conv-body" style={{ background: 'transparent', padding: '16px 0 0' }}>
                {thread.msgs.slice(-2).map((m, i) => (
                  <div className={`bubble ${m.who}`} key={i}>{m.t}<div className="bt">{m.time}</div></div>
                ))}
              </div>
              <Link to={`c/messages/${b.thread}`} className="btn ghost sm" style={{ marginTop: 14 }}>Open conversation</Link>
            </div>
          )}
        </div>

        <div className="col">
          <div className="icard">
            <h3><i className="fa-solid fa-calendar-heart"></i> Event</h3>
            <Link to={`c/event/${ev.id}`} className="srow clickable" style={{ display: 'flex', borderBottom: 'none' }}>
              <div className="si"><i className="fa-solid fa-champagne-glasses"></i></div>
              <div><div className="sn">{ev.title}</div><div className="sc">{ev.date} · {ev.loc}</div></div>
              <i className="fa-solid fa-chevron-right faint" style={{ marginLeft: 'auto' }}></i>
            </Link>
          </div>
          <div className="icard">
            <h3><i className="fa-solid fa-circle-info"></i> What happens next</h3>
            <div className="csub" style={{ marginTop: 8, lineHeight: 1.6 }}>
              {b.status === 'accepted' && 'Pay the deposit to confirm this supplier. The balance is due on the event day.'}
              {b.status === 'pending' && 'The supplier has 7 days to respond to your request. We’ll notify you the moment they reply.'}
              {b.status === 'confirmed' && 'You’re all set. The supplier will be in touch closer to the date to finalise details.'}
              {b.status === 'declined' && 'This supplier couldn’t take the booking. Browse alternatives for this category.'}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

function CustomerPayments() {
  const d = CC2();
  const live = d.bookings.filter((b) => b.status !== 'declined' && b.status !== 'pending');
  const paid = live.filter((b) => b.depositPaid);
  const due = live.filter((b) => !b.depositPaid && b.status === 'accepted');
  const evName = (id) => d.events.find((e) => e.id === id).title;
  const totalDue = due.reduce((a, b) => a + b.deposit, 0);
  return (
    <div className="page">
      <h1 className="page-title">Payments</h1>
      <p className="page-sub" style={{ marginBottom: 22 }}>Deposits, balances and receipts across all your events.</p>
      <div className="minis">
        <div className="mini-stat"><div className="v num" style={{ color: 'var(--sage)' }}>{money(d.money.deposits)}</div><div className="l">Deposits paid</div></div>
        <div className="mini-stat"><div className="v num" style={{ color: 'var(--gold)' }}>{money(totalDue)}</div><div className="l">Due now</div></div>
        <div className="mini-stat"><div className="v num">{money(d.money.total - d.money.deposits)}</div><div className="l">Remaining balance</div></div>
        <div className="mini-stat"><div className="v num">{money(d.money.total)}</div><div className="l">Total event spend</div></div>
      </div>

      {due.length > 0 && (
        <div className="icard" style={{ borderColor: 'var(--gold-tint)', background: 'var(--gold-tint)', marginBottom: 22 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
            <div className="ic gold" style={{ width: 44, height: 44, borderRadius: 12 }}><i className="fa-solid fa-credit-card"></i></div>
            <div style={{ flex: 1 }}><div style={{ fontWeight: 800 }}>{due.length} deposits due — {money(totalDue)}</div><div className="muted" style={{ fontSize: 13 }}>Pay now to lock in your accepted suppliers.</div></div>
            <a className="btn primary"><i className="fa-solid fa-lock"></i> Pay all deposits</a>
          </div>
        </div>
      )}

      <div className="icard">
        <table className="ptable">
          <thead><tr><th>Supplier</th><th>Event</th><th>Status</th><th className="r">Deposit</th><th className="r">Balance</th><th></th></tr></thead>
          <tbody>
            {live.map((b) => (
              <tr key={b.id}>
                <td className="vendor">{b.vendor}<div className="muted" style={{ fontWeight: 400, fontSize: 12 }}>{b.svc}</div></td>
                <td className="muted">{evName(b.ev)}</td>
                <td>{b.depositPaid ? <span className="pill confirmed">Deposit paid</span> : <span className="pill pending">Due</span>}</td>
                <td className="r num">{money(b.deposit)}</td>
                <td className="r num">{money(b.amount - (b.depositPaid ? b.deposit : 0))}</td>
                <td className="r">{b.depositPaid ? <a className="faint" style={{ fontSize: 12, fontWeight: 700 }}>Receipt</a> : <a className="btn primary sm">Pay</a>}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}

function ConvView({ threads, base, params }) {
  const ids = Object.keys(threads);
  const active = params[0] && threads[params[0]] ? params[0] : ids[0];
  const t = threads[active];
  return (
    <div className="page">
      <h1 className="page-title" style={{ marginBottom: 16 }}>Messages</h1>
      <div className="thread">
        <div className="thread-list">
          {ids.map((id) => {
            const th = threads[id];
            const last = th.msgs[th.msgs.length - 1];
            return (
              <Link to={`${base}/${id}`} key={id} className={`tl-item ${id === active ? 'on' : ''}`} style={{ display: 'flex' }}>
                <Avatar initials={th.vi || th.ci} sq />
                <div style={{ minWidth: 0 }}><div className="nm">{th.vendor || th.customer}</div><div className="pv">{last.t}</div></div>
              </Link>
            );
          })}
        </div>
        <div className="thread-conv">
          <div className="conv-head">
            <Avatar initials={t.vi || t.ci} sq />
            <div><div style={{ fontWeight: 800, fontSize: 15 }}>{t.vendor || t.customer}</div><div className="muted" style={{ fontSize: 12 }}>{t.role || t.ev}</div></div>
          </div>
          <div className="conv-body">
            {t.msgs.map((m, i) => (<div className={`bubble ${m.who}`} key={i}>{m.t}<div className="bt">{m.time}</div></div>))}
          </div>
          <div className="conv-input"><div className="fake">Write a message…</div><a className="btn primary"><i className="fa-solid fa-paper-plane"></i></a></div>
        </div>
      </div>
    </div>
  );
}
function CustomerMessages({ params }) { return <ConvView threads={CC2().threads} base="c/messages" params={params} />; }

function CustomerFavourites() {
  const favs = [
    { n: 'Velvet & Vine Bar Co.', c: 'Mobile bar · Bath', tag: 'Bar', price: 'from £18pp', stars: 5 },
    { n: 'The String Quartet', c: 'Live music · Bristol', tag: 'Entertainment', price: 'from £650', stars: 5 },
    { n: 'Petal & Co.', c: 'Florist · Cirencester', tag: 'Flowers', price: 'from £400', stars: 4 },
    { n: 'Sweet Tier Bakery', c: 'Cakes · Stroud', tag: 'Desserts', price: 'from £120', stars: 5 },
    { n: 'Wandering Lens', c: 'Photography · Bristol', tag: 'Photo', price: 'from £1,200', stars: 5 },
    { n: 'Cotswold Classics', c: 'Transport · Stroud', tag: 'Transport', price: 'from £350', stars: 4 },
  ];
  return (
    <div className="page">
      <h1 className="page-title">Saved suppliers</h1>
      <p className="page-sub" style={{ marginBottom: 22 }}>Suppliers you’ve favourited while browsing. Send a request when you’re ready.</p>
      <div className="gal">
        {favs.map((f) => (
          <Link to="c/browse" className="gcard clickable" key={f.n} style={{ display: 'block' }}>
            <PH label={f.tag.toLowerCase()} />
            <div className="gc-body">
              <div className="gn">{f.n}</div>
              <div className="gc">{f.c}</div>
              <div className="gmeta"><span className="price">{f.price}</span><span className="stars">{'★'.repeat(f.stars)}<span className="faint">{'★'.repeat(5 - f.stars)}</span></span></div>
            </div>
          </Link>
        ))}
      </div>
    </div>
  );
}

function CustomerBrowse() {
  const cats = ['All', 'Catering', 'Entertainment', 'Flowers', 'Photography', 'Bar', 'Cake'];
  const sup = [
    { n: 'The Roaming Kitchen', c: 'Catering · Bath', price: 'from £32pp', stars: 5, tag: 'catering' },
    { n: 'Sax & The City', c: 'Live music · Bristol', price: 'from £800', stars: 4, tag: 'music' },
    { n: 'The Bloom Room', c: 'Florist · Bath', price: 'from £450', stars: 5, tag: 'flowers' },
    { n: 'Hitched Films', c: 'Videography · Cardiff', price: 'from £1,400', stars: 5, tag: 'film' },
    { n: 'Pour Decisions', c: 'Mobile bar · Bristol', price: 'from £20pp', stars: 4, tag: 'bar' },
    { n: 'Tiers of Joy', c: 'Cakes · Bath', price: 'from £140', stars: 5, tag: 'cake' },
  ];
  return (
    <div className="page">
      <Back to="c/dashboard">Dashboard</Back>
      <h1 className="page-title">Find suppliers</h1>
      <p className="page-sub">Discover and request suppliers near the Cotswolds.</p>
      <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap', margin: '18px 0 22px' }}>
        {cats.map((c, i) => <span key={c} className="pill" style={{ background: i === 0 ? 'var(--terra)' : 'var(--paper-2)', color: i === 0 ? '#fff' : 'var(--ink-2)', padding: '7px 14px', fontSize: 13 }}>{c}</span>)}
      </div>
      <div className="gal">
        {sup.map((f) => (
          <div className="gcard clickable" key={f.n}>
            <PH label={f.tag} />
            <div className="gc-body">
              <div className="gn">{f.n}</div>
              <div className="gc">{f.c}</div>
              <div className="gmeta"><span className="price">{f.price}</span><span className="stars">{'★'.repeat(f.stars)}<span className="faint">{'★'.repeat(5 - f.stars)}</span></span></div>
              <a className="btn ghost sm block" style={{ marginTop: 12 }}>Request a quote</a>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

function CustomerNewEvent() {
  return (
    <div className="page" style={{ maxWidth: 640 }}>
      <Back to="c/events">My events</Back>
      <h1 className="page-title">Create an event</h1>
      <p className="page-sub" style={{ marginBottom: 22 }}>Tell us the basics — you can add suppliers next.</p>
      <div className="icard">
        {[['Event name', 'e.g. Sarah’s 40th'], ['Event type', 'Wedding · Birthday · Corporate…'], ['Date', 'dd / mm / yyyy'], ['Location', 'Town or venue'], ['Guests', 'Approximate number'], ['Budget', '£ total']].map(([l, ph]) => (
          <div key={l} style={{ marginBottom: 15 }}>
            <div style={{ fontSize: 12.5, fontWeight: 700, marginBottom: 7 }}>{l}</div>
            <div style={{ background: 'var(--paper-2)', borderRadius: 10, padding: '12px 15px', fontSize: 13.5, color: 'var(--ink-3)' }}>{ph}</div>
          </div>
        ))}
        <Link to="c/events" className="btn primary block lg" style={{ marginTop: 8 }}><i className="fa-solid fa-check"></i> Create event</Link>
      </div>
    </div>
  );
}

Object.assign(window.PAGES, {
  'c/bookings': CustomerBookings, 'c/booking': CustomerBooking, 'c/payments': CustomerPayments,
  'c/messages': CustomerMessages, 'c/favourites': CustomerFavourites, 'c/browse': CustomerBrowse, 'c/event/new': CustomerNewEvent,
});
window.ConvView = ConvView;
