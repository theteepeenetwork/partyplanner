// Shared mock data, icons, hooks and primitives for the 3 service-view
// directions. Exported to window so each direction file can use them.
const { useState, useMemo } = React;

/* ───────────────────────── icons (inline, stroke-based) ───────────────────────── */
const P = (d, fill) => ({ d, fill });
function Icon({ name, style, className }) {
  const paths = {
    check:        { v: '0 0 24 24', el: <path d="M20 6L9 17l-5-5" /> },
    checkbold:    { v: '0 0 24 24', el: <path d="M20 6L9 17l-5-5" strokeWidth="3" /> },
    star:         { v: '0 0 24 24', fill: true, el: <path d="M12 2l2.9 6.3 6.8.7-5.1 4.6 1.4 6.7L12 17.8 6 20.6l1.4-6.7L2.3 9l6.8-.7z" /> },
    users:        { v: '0 0 24 24', el: <><circle cx="9" cy="8" r="3.2" /><path d="M3.5 19a5.5 5.5 0 0111 0M16 6.2a3 3 0 010 5.6M21 19a5 5 0 00-3.5-4.8" /></> },
    clock:        { v: '0 0 24 24', el: <><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></> },
    pin:          { v: '0 0 24 24', el: <><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0116 0z" /><circle cx="12" cy="10" r="2.6" /></> },
    music:        { v: '0 0 24 24', el: <><path d="M9 18V5l11-2v13" /><circle cx="6" cy="18" r="3" /><circle cx="17" cy="16" r="3" /></> },
    calendar:     { v: '0 0 24 24', el: <><rect x="3" y="5" width="18" height="16" rx="2" /><path d="M3 9h18M8 3v4M16 3v4" /></> },
    shield:       { v: '0 0 24 24', el: <><path d="M12 2l8 3v6c0 5-3.5 8.5-8 11-4.5-2.5-8-6-8-11V5z" /><path d="M9 12l2 2 4-4" /></> },
    bolt:         { v: '0 0 24 24', el: <path d="M13 2L4 14h7l-1 8 9-12h-7z" /> },
    chat:         { v: '0 0 24 24', el: <path d="M21 12a8 8 0 01-11.5 7.2L4 21l1.8-5A8 8 0 1121 12z" /> },
    heart:        { v: '0 0 24 24', el: <path d="M12 20s-7-4.5-9.5-9C1 8 2.5 4.5 6 4.5c2 0 3.2 1.2 4 2.3.8-1.1 2-2.3 4-2.3 3.5 0 5 3.5 3.5 6.5C19 15.5 12 20 12 20z" /> },
    share:        { v: '0 0 24 24', el: <><circle cx="6" cy="12" r="2.5" /><circle cx="18" cy="6" r="2.5" /><circle cx="18" cy="18" r="2.5" /><path d="M8.2 10.9l7.6-3.8M8.2 13.1l7.6 3.8" /></> },
    plus:         { v: '0 0 24 24', el: <path d="M12 5v14M5 12h14" /> },
    arrow:        { v: '0 0 24 24', el: <path d="M5 12h14M13 6l6 6-6 6" /> },
    sparkle:      { v: '0 0 24 24', el: <path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8z" /> },
    speaker:      { v: '0 0 24 24', el: <><rect x="6" y="3" width="12" height="18" rx="2" /><circle cx="12" cy="14" r="3" /><circle cx="12" cy="7" r="1" /></> },
    sun:          { v: '0 0 24 24', el: <><circle cx="12" cy="12" r="4" /><path d="M12 2v2M12 20v2M4 12H2M22 12h-2M5 5l1.5 1.5M17.5 17.5L19 19M19 5l-1.5 1.5M6.5 17.5L5 19" /></> },
    truck:        { v: '0 0 24 24', el: <><path d="M3 7h11v8H3zM14 10h4l3 3v2h-7" /><circle cx="7" cy="18" r="1.8" /><circle cx="17" cy="18" r="1.8" /></> },
    tool:         { v: '0 0 24 24', el: <path d="M14 7a4 4 0 01-5 5l-5 5 2 2 5-5a4 4 0 005-5l-2.5 2.5L13 11l1.5-1.5z" /> },
    quote:        { v: '0 0 24 24', fill: true, el: <path d="M7 7h4v4c0 3-1.5 4.5-4 5V14H4V7zm9 0h4v4c0 3-1.5 4.5-4 5V14h-3V7z" /> },
  };
  const p = paths[name] || paths.check;
  return (
    <svg className={className} style={style} viewBox={p.v} fill={p.fill ? 'currentColor' : 'none'}
      stroke={p.fill ? 'none' : 'currentColor'} strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
      {p.el}
    </svg>
  );
}

/* ───────────────────────── mock service ───────────────────────── */
const SERVICE = {
  title: 'The Velvet Hour',
  tagline: 'A seven-piece soul & funk band for weddings, parties and corporate nights',
  category: 'Entertainment',
  subcategory: 'Live Music',
  style: 'Soul & Funk Band',
  location: 'London · travels nationwide',
  rating: 4.9,
  reviews: 127,
  bookings: 340,
  responseTime: 'within 2 hours',
  vendor: {
    name: 'Marcus Reed', role: 'Bandleader & saxophone', initials: 'MR',
    since: 2016, verified: true, location: 'London',
    portrait: 'host portrait',
    // stats lifted from the vendor's own profile
    events: 340, responseTime: 'within 2 hours', responseRate: '100%', repeatRate: '63%',
    bio: 'After fifteen years touring as a session saxophonist, I put The Velvet Hour together to bring real live-band energy to private events. I play every booking myself and build each line-up around the room, the crowd and the moment you want on the floor.',
    plays: ['Weddings', 'Corporate parties', 'Private celebrations', 'Festivals'],
    quote: 'I’ve been putting bands together for a decade. Tell me about your night and I’ll build the right line-up for it.',
  },
  about: [
    'The Velvet Hour brings the unmistakable energy of vintage soul and funk to the dancefloor — think Stevie Wonder, Earth Wind & Fire and Amy Winehouse, played live by seasoned session musicians.',
    'We tailor every set to your event: a relaxed acoustic trio for the ceremony and drinks, building to a full horn-driven party band that keeps the floor packed until the last song. A professional DJ fills the gaps between live sets so the music never stops.',
  ],
  included: [
    'Professional PA & stage lighting',
    'DJ between live sets, all night',
    'Two 60-minute live performances',
    'Online song-request portal',
    'Public liability insurance & PAT-tested gear',
    'Smart dress code as standard',
  ],
  specs: [
    { k: 'Capacity', v: 'Up to 300 guests', icon: 'users' },
    { k: 'Performance', v: '2 × 60-min live sets', icon: 'clock' },
    { k: 'Setup', v: '90 min · breakdown 45 min', icon: 'tool' },
    { k: 'Setting', v: 'Indoor & outdoor', icon: 'sun' },
    { k: 'Travel', v: 'Free within 50 miles', icon: 'truck' },
    { k: 'Min. notice', v: '14 days', icon: 'calendar' },
  ],
  packages: [
    { id: 'trio', name: 'The Trio', tag: null, price: 850,
      desc: 'Acoustic three-piece — perfect for ceremonies and intimate drinks receptions.',
      meta: ['3 musicians', '2 × 45 min'] },
    { id: 'quintet', name: 'The Quintet', tag: 'Most popular', price: 1450,
      desc: 'Five-piece band with rhythm section and lead vocals. Our most-booked option.',
      meta: ['5 musicians', '2 × 60 min', 'DJ included'] },
    { id: 'full', name: 'The Full Band', tag: 'Showstopper', price: 2200,
      desc: 'Seven-piece with full horn section and DJ until late. Maximum dancefloor.',
      meta: ['7 musicians', '3 × 50 min', 'DJ until 1am'] },
  ],
  extras: [
    { id: 'set', name: 'Extra 45-minute set', desc: 'One more live set added to your night', price: 350 },
    { id: 'ceremony', name: 'Acoustic ceremony performance', desc: 'Walk down the aisle to a live arrangement', price: 250 },
    { id: 'dj', name: 'DJ extended until 2am', desc: 'Keep the party going an extra hour', price: 400 },
    { id: 'pa', name: 'PA system for speeches', desc: 'Wireless mics & mixer for the toasts', price: 120 },
  ],
  gallery: [
    'band on stage — hero',
    'horn section close-up',
    'packed dancefloor',
    'acoustic trio · ceremony',
    'lead vocalist',
  ],
  reviewList: [
    { quote: 'Absolutely made our wedding. The dancefloor was packed from the first note and the horn section was unreal. Guests are still talking about them.', by: 'Sophie & James', meta: 'Wedding · Soho Farmhouse', stars: 5 },
    { quote: 'Booked the full band for our company summer party. Marcus was brilliant to deal with and the DJ sets between were seamless.', by: 'Priya N.', meta: 'Corporate · 220 guests', stars: 5 },
  ],
  cancellation: 'Free cancellation up to 30 days before your event. A 50% deposit secures the date and is refundable within 48 hours of booking.',
};

const gbp = (n) => '£' + n.toLocaleString('en-GB');

/* ───────────────────────── booking state hook ───────────────────────── */
function useBooking(defaultPkg = 'quintet') {
  const [pkgId, setPkgId] = useState(defaultPkg);
  const [extras, setExtras] = useState({});
  const pkg = SERVICE.packages.find((p) => p.id === pkgId);
  const toggleExtra = (id) => setExtras((e) => ({ ...e, [id]: !e[id] }));
  const chosenExtras = SERVICE.extras.filter((e) => extras[e.id]);
  const extrasTotal = chosenExtras.reduce((s, e) => s + e.price, 0);
  const total = (pkg ? pkg.price : 0) + extrasTotal;
  return { pkgId, setPkgId, pkg, extras, toggleExtra, chosenExtras, extrasTotal, total };
}

/* ───────────────────────── small shared components ───────────────────────── */
function Stars({ n = 5, size = 16 }) {
  return (
    <span className="sv-stars" style={{ '--s': size }}>
      {Array.from({ length: 5 }).map((_, i) => (
        <Icon key={i} name="star" style={{ width: size, height: size, opacity: i < n ? 1 : 0.25 }} />
      ))}
    </span>
  );
}

function RatingRow() {
  return (
    <div className="sv-rate-row">
      <Stars n={5} />
      <b>{SERVICE.rating}</b>
      <span>· {SERVICE.reviews} reviews · {SERVICE.bookings} bookings</span>
    </div>
  );
}

function PackageCard({ p, active, onSelect }) {
  return (
    <button type="button" className={'sv-pkg' + (active ? ' is-active' : '')} onClick={() => onSelect(p.id)}>
      <span className="sv-pkg-radio" />
      <span>
        <span className="sv-pkg-name">{p.name}{p.tag && <span className="sv-pkg-tag">{p.tag}</span>}</span>
        <span className="sv-pkg-desc">{p.desc}</span>
        <span className="sv-pkg-meta">
          {p.meta.map((m, i) => <span key={i}><Icon name={i === 0 ? 'users' : i === 1 ? 'clock' : 'music'} style={{ width: 13, height: 13, color: 'var(--accent)' }} />{m}</span>)}
        </span>
      </span>
      <span className="sv-pkg-price">{gbp(p.price)}<small>from</small></span>
    </button>
  );
}

function ExtraRow({ e, on, onToggle }) {
  return (
    <div className={'sv-extra' + (on ? ' is-on' : '')} onClick={() => onToggle(e.id)}>
      <span className="sv-check"><Icon name="checkbold" /></span>
      <span className="sv-extra-body">
        <span className="sv-extra-name">{e.name}</span>
        <span className="sv-extra-desc">{e.desc}</span>
      </span>
      <span className="sv-extra-price">+{gbp(e.price)}</span>
    </div>
  );
}

function IncludedList({ items = SERVICE.included }) {
  return (
    <ul className="sv-incl">
      {items.map((t, i) => <li key={i}><Icon name="check" />{t}</li>)}
    </ul>
  );
}

function SpecGrid() {
  return (
    <div className="sv-specs">
      {SERVICE.specs.map((s, i) => (
        <div className="sv-spec" key={i}>
          <span className="k"><Icon name={s.icon} />{s.k}</span>
          <span className="v">{s.v}</span>
        </div>
      ))}
    </div>
  );
}

function VendorBlock({ compact }) {
  const v = SERVICE.vendor;
  return (
    <div className="sv-vendor">
      <span className="sv-vendor-ava">{v.initials}</span>
      <span style={{ flex: 1 }}>
        <span className="sv-vendor-name">{v.name}{v.verified && <span className="sv-badge-verified"><Icon name="shield" />Verified</span>}</span>
        <span className="sv-vendor-sub">{v.role} · hosting since {v.since}</span>
      </span>
      {!compact && <span className="sv-chip"><Icon name="bolt" />Replies {SERVICE.responseTime}</span>}
    </div>
  );
}

// Developed host block — surfaces info lifted from the vendor's own profile.
function MeetYourHost() {
  const v = SERVICE.vendor;
  const stats = [
    { k: 'Member since', v: v.since, icon: 'calendar' },
    { k: 'Events played', v: v.events, icon: 'music' },
    { k: 'Responds', v: v.responseTime.replace('within ', '~'), icon: 'bolt' },
    { k: 'Rebooked', v: v.repeatRate, icon: 'heart' },
  ];
  return (
    <div className="sv-panel sv-host">
      <div className="sv-host-head">
        <div className="sv-host-ava sv-ph" data-label={v.portrait} />
        <div className="sv-host-id">
          <div className="sv-host-name">
            {v.name}
            {v.verified && <span className="sv-badge-verified"><Icon name="shield" />Verified host</span>}
          </div>
          <div className="sv-host-role">{v.role}</div>
          <div className="sv-host-meta">
            <span className="sv-host-rate"><Icon name="star" style={{ width: 14, height: 14 }} /><b>{SERVICE.rating}</b> · {SERVICE.reviews} reviews</span>
            <span className="sv-host-dot">·</span>
            <span><Icon name="pin" style={{ width: 14, height: 14 }} />{v.location}</span>
          </div>
        </div>
        <a className="sv-host-link" href="#">View full profile<Icon name="arrow" style={{ width: 15, height: 15 }} /></a>
      </div>

      <div className="sv-host-stats">
        {stats.map((s, i) => (
          <div className="sv-host-stat" key={i}>
            <span className="k"><Icon name={s.icon} />{s.k}</span>
            <span className="v">{s.v}</span>
          </div>
        ))}
      </div>

      <p className="sv-host-bio">{v.bio}</p>

      <div className="sv-host-plays">
        <span className="sv-host-plays-label">Plays</span>
        {v.plays.map((p, i) => <span className="sv-tag" key={i}>{p}</span>)}
      </div>

      <blockquote className="sv-host-quote">
        {v.quote}
      </blockquote>

      <button className="sv-btn sv-btn-ghost" style={{ marginTop: 18, alignSelf: 'flex-start' }}>
        <Icon name="chat" style={{ width: 16, height: 16 }} />Message {v.name}
      </button>
    </div>
  );
}

function Reviews({ limit = 2 }) {
  return (
    <div style={{ display: 'grid', gap: 14 }}>
      {SERVICE.reviewList.slice(0, limit).map((r, i) => (
        <div className="sv-review" key={i}>
          <Stars n={r.stars} size={15} />
          <p className="sv-review-quote" style={{ marginTop: 10 }}>“{r.quote}”</p>
          <div className="sv-review-by"><b>{r.by}</b> · {r.meta}</div>
        </div>
      ))}
    </div>
  );
}

function MiniNav() {
  return (
    <div className="sv-nav">
      <span className="sv-logo">For <span className="acc">Your</span> <span className="mut">Events</span></span>
      <span className="sv-nav-links">
        <a>Find Suppliers</a><a>How It Works</a><a>My Events</a>
        <span className="sv-nav-cta">Start Planning</span>
      </span>
    </div>
  );
}

function Crumb() {
  return (
    <div className="sv-crumb">
      <a>Home</a><span className="sep">/</span>
      <a>Entertainment</a><span className="sep">/</span>
      <a>Live Music</a><span className="sep">/</span>
      <b>{SERVICE.title}</b>
    </div>
  );
}

Object.assign(window, {
  Icon, SERVICE, gbp, useBooking, Stars, RatingRow, PackageCard, ExtraRow,
  IncludedList, SpecGrid, VendorBlock, MeetYourHost, Reviews, MiniNav, Crumb,
});
