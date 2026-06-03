/* Shared primitives for Browse Services redesign — exported to window */
const { useState, useEffect, useRef, useCallback } = React;

/* ---------- helpers ---------- */
const money = (n) => '£' + Math.round(n).toLocaleString('en-GB');
function lineValue(s, guests) {
  if (s.unit === 'pp') return s.price * (guests || 1);
  if (s.unit === 'hr') return s.price * 4;
  return s.price;
}
function priceText(s) {
  if (s.unit === 'pp') return { main: money(s.price), sub: 'per person' };
  if (s.unit === 'hr') return { main: money(s.price), sub: 'per hour' };
  return { main: money(s.price), sub: 'from' };
}

/* ---------- the shopping state hook ---------- */
function useShopping(startEmpty) {
  const seed = startEmpty ? [] : window.PP.events.map(e => ({ ...e, basket: [...e.basket] }));
  const [events, setEvents] = useState(seed);
  const [activeId, setActiveId] = useState(seed[0] ? seed[0].id : null);
  const [favs, setFavs] = useState({});
  const [toast, setToast] = useState(null);
  const tRef = useRef();

  const activeEvent = events.find(e => e.id === activeId) || null;

  const flash = useCallback((msg) => {
    setToast(msg); clearTimeout(tRef.current);
    tRef.current = setTimeout(() => setToast(null), 2200);
  }, []);

  const addToBasket = useCallback((sid) => {
    setEvents(prev => prev.map(e => {
      if (e.id !== activeId) return e;
      if (e.basket.includes(sid)) return e;
      return { ...e, basket: [...e.basket, sid] };
    }));
    const ev = events.find(e => e.id === activeId);
    const svc = window.PP.services.find(s => s.id === sid);
    if (ev && svc && !ev.basket.includes(sid)) flash(`Added “${svc.title}” to ${ev.title}`);
  }, [activeId, events, flash]);

  const removeFromBasket = useCallback((sid) => {
    setEvents(prev => prev.map(e => e.id === activeId ? { ...e, basket: e.basket.filter(x => x !== sid) } : e));
  }, [activeId]);

  const toggleFav = useCallback((sid) => setFavs(p => ({ ...p, [sid]: !p[sid] })), []);

  const createEvent = useCallback((title, dateLabel, guests) => {
    const id = 'e' + Date.now();
    setEvents(prev => [...prev, { id, title, dateLabel, guests, type: 'Event', basket: [] }]);
    setActiveId(id);
    flash(`Created “${title}” — start adding services`);
  }, [flash]);

  const basketServices = activeEvent ? activeEvent.basket.map(id => window.PP.services.find(s => s.id === id)).filter(Boolean) : [];
  const total = activeEvent ? basketServices.reduce((a, s) => a + lineValue(s, activeEvent.guests), 0) : 0;

  return { events, activeId, setActiveId, activeEvent, addToBasket, removeFromBasket,
           favs, toggleFav, toast, flash, createEvent, basketServices, total };
}

/* ---------- stars ---------- */
function Stars({ rating, reviews }) {
  return (
    <div className="pp-rating">
      <i className="fa-solid fa-star"></i>
      <b>{rating.toFixed(1)}</b>
      {reviews != null && <span>({reviews})</span>}
    </div>
  );
}

/* ---------- service card ---------- */
function ServiceCard({ s, inBasket, onAdd, faved, onFav, addLabel = 'Add to event' }) {
  const pt = priceText(s);
  return (
    <article className="pp-card">
      <div className="pp-card-media">
        <img src={s.img} alt={s.title} loading="lazy" />
        <span className="pp-card-cat">{s.catName}</span>
        <button className={'pp-card-fav' + (faved ? ' on' : '')} onClick={() => onFav(s.id)} aria-label="Save">
          <i className={(faved ? 'fa-solid' : 'fa-regular') + ' fa-heart'}></i>
        </button>
      </div>
      <div className="pp-card-body">
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: 8 }}>
          <h3 className="pp-card-title">{s.title}</h3>
        </div>
        <div className="pp-card-loc"><i className="fa-solid fa-location-dot"></i>{s.loc}</div>
        <Stars rating={s.rating} reviews={s.reviews} />
        <div className="pp-card-foot">
          <div className="pp-price">{pt.main} <small>{pt.sub}</small></div>
          <button className={'pp-add' + (inBasket ? ' added' : '')} onClick={() => onAdd(s.id)}>
            {inBasket
              ? (<><i className="fa-solid fa-check"></i>Added</>)
              : (<><i className="fa-solid fa-plus"></i>{addLabel}</>)}
          </button>
        </div>
      </div>
    </article>
  );
}

/* ---------- search bar ---------- */
function SearchBar({ value, onChange, placeholder = 'Search services, vendors, styles…' }) {
  return (
    <div className="pp-search">
      <i className="fa-solid fa-magnifying-glass"></i>
      <input value={value} onChange={e => onChange(e.target.value)} placeholder={placeholder} />
    </div>
  );
}

/* ---------- category chips ---------- */
function CategoryChips({ active, onPick, showAll = true }) {
  return (
    <div className="pp-chips">
      {showAll && (
        <button className={'pp-chip' + (!active ? ' on' : '')} onClick={() => onPick(null)}>
          <i className="fa-solid fa-border-all"></i>All
        </button>
      )}
      {window.PP.categories.map(c => (
        <button key={c.id} className={'pp-chip' + (active === c.id ? ' on' : '')} onClick={() => onPick(c.id)}>
          <i className={'fa-solid ' + c.icon}></i>{c.name}
        </button>
      ))}
    </div>
  );
}

/* ---------- event switcher dropdown menu ---------- */
function EventMenu({ events, activeId, onPick, onClose, onCreate, anchorStyle }) {
  const ref = useRef();
  useEffect(() => {
    const h = (e) => { if (ref.current && !ref.current.contains(e.target)) onClose(); };
    document.addEventListener('mousedown', h); return () => document.removeEventListener('mousedown', h);
  }, [onClose]);
  return (
    <div className="pp-menu" ref={ref} style={anchorStyle}>
      <div style={{ fontSize: 11, fontWeight: 800, letterSpacing: '.12em', textTransform: 'uppercase', color: 'var(--pp-muted)', padding: '6px 10px 4px' }}>Your events</div>
      {events.map(e => (
        <div key={e.id} className={'pp-menu-item' + (e.id === activeId ? ' on' : '')} onClick={() => { onPick(e.id); onClose(); }}>
          <span className="pp-menu-dot"></span>
          <div style={{ flex: 1 }}>
            <div className="pp-menu-name">{e.title}</div>
            <div className="pp-menu-meta">{e.dateLabel} · {e.guests} guests · {e.basket.length} in basket</div>
          </div>
        </div>
      ))}
      <div className="pp-divider"></div>
      <div className="pp-menu-item" onClick={() => { onCreate && onCreate(); onClose(); }}>
        <span className="pp-menu-dot" style={{ borderStyle: 'dashed' }}><i className="fa-solid fa-plus" style={{ fontSize: 9, color: 'var(--pp-terracotta)' }}></i></span>
        <div className="pp-menu-name" style={{ color: 'var(--pp-terracotta)' }}>Create a new event</div>
      </div>
    </div>
  );
}

/* ---------- toast ---------- */
function Toast({ msg }) {
  return <div className={'pp-toast' + (msg ? ' show' : '')}><i className="fa-solid fa-circle-check"></i>{msg}</div>;
}

/* ---------- simple nav ---------- */
function Nav({ mobile, basketCount, onBasket }) {
  return (
    <nav className={'pp-nav' + (mobile ? ' mob' : '')}>
      <div className="pp-logo">For <b>Your</b><span>Events</span></div>
      {!mobile && (
        <div className="pp-nav-links">
          <a href="#" className="active">Find Suppliers</a>
          <a href="#">My Events</a>
          <a href="#">Inspiration</a>
          <a href="#" onClick={(e) => { e.preventDefault(); onBasket && onBasket(); }}>
            <i className="fa-solid fa-basket-shopping"></i> Basket{basketCount ? ` (${basketCount})` : ''}
          </a>
          <a href="#" className="pp-nav-cta" style={{ color: '#fff' }}>Start Planning</a>
        </div>
      )}
      {mobile && (
        <button className="pp-add" style={{ background: 'var(--pp-ink)', color: '#fff', border: 'none' }} onClick={onBasket}>
          <i className="fa-solid fa-basket-shopping"></i>{basketCount || 0}
        </button>
      )}
    </nav>
  );
}

/* filtering util */
function filterServices(active, query) {
  let list = window.PP.services;
  if (active) list = list.filter(s => s.cat === active);
  if (query && query.trim()) {
    const q = query.toLowerCase();
    list = list.filter(s => (s.title + ' ' + s.vendor + ' ' + s.catName + ' ' + s.loc).toLowerCase().includes(q));
  }
  return list;
}

Object.assign(window, {
  money, lineValue, priceText, useShopping, Stars, ServiceCard, SearchBar,
  CategoryChips, EventMenu, Toast, Nav, filterServices,
});
