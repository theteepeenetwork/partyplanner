/* Direction A (v3) — Event Picker that becomes the context.
   ONE site nav. The picker is the hero; the selected card IS the "shopping for"
   indicator. On scroll, the picker condenses into a single slim sticky strip that
   only appears once the hero is gone — never two bars at once. No category tiles. */

const EVENT_THEME = {
  Wedding:   { icon: 'fa-heart',        c: '#B66A4D' },
  Corporate: { icon: 'fa-briefcase',    c: '#6E7E5B' },
  Birthday:  { icon: 'fa-cake-candles', c: '#7A5A78' },
  Event:     { icon: 'fa-calendar-day', c: '#B66A4D' },
};

function EventPickCard({ ev, active, guests, total, onClick, onView }) {
  const th = EVENT_THEME[ev.type] || EVENT_THEME.Event;
  return (
    <div onClick={onClick} role="button" tabIndex={0} className="ppa-pick" data-active={active ? '1' : '0'} style={{
      position: 'relative', cursor: 'pointer',
      background: active ? '#fff' : 'rgba(255,255,255,.55)',
      border: '1.5px solid ' + (active ? 'var(--pp-terracotta)' : 'var(--pp-line)'),
      boxShadow: active ? '0 0 0 3px rgba(182,106,77,.16), var(--pp-shadow-lift)' : 'none',
      borderRadius: 16, padding: '15px 16px', transition: 'all .2s ease',
      display: 'flex', flexDirection: 'column', gap: active ? 13 : 0,
    }}>
      <div style={{ display: 'flex', gap: 13, alignItems: 'center' }}>
        <span style={{ width: 44, height: 44, borderRadius: 13, flex: 'none', display: 'grid', placeItems: 'center',
          background: active ? th.c : 'color-mix(in srgb, ' + th.c + ', #fff 85%)',
          color: active ? '#fff' : th.c, transition: 'all .2s' }}>
          <i className={'fa-solid ' + th.icon} style={{ fontSize: 17 }}></i>
        </span>
        <div style={{ flex: 1, minWidth: 0 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 7 }}>
            <span style={{ fontSize: 10, fontWeight: 800, letterSpacing: '.1em', textTransform: 'uppercase', color: 'var(--pp-muted)' }}>{ev.type}</span>
            {ev.basket.length > 0 && (
              <span style={{ fontSize: 10.5, fontWeight: 800, color: 'var(--pp-terracotta-deep)', background: 'rgba(182,106,77,.14)', padding: '1px 7px', borderRadius: 99 }}>{ev.basket.length} added</span>
            )}
          </div>
          <div style={{ fontFamily: 'var(--pp-display)', fontWeight: 600, fontSize: 16.5, lineHeight: 1.2, color: 'var(--pp-ink)',
            overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', marginTop: 1 }}>{ev.title}</div>
          <div style={{ fontSize: 12.5, color: 'var(--pp-muted)', marginTop: 2, display: 'flex', alignItems: 'center', gap: 10, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
            <span style={{ flex: 'none' }}><i className="fa-solid fa-calendar-day" style={{ fontSize: 10.5, marginRight: 4, opacity: .7 }}></i>{ev.dateLabel}</span>
            <span style={{ flex: 'none' }}><i className="fa-solid fa-user-group" style={{ fontSize: 10.5, marginRight: 4, opacity: .7 }}></i>{ev.guests}</span>
          </div>
        </div>
        <span style={{ width: 22, height: 22, flex: 'none', alignSelf: 'flex-start', marginTop: 2, borderRadius: '50%', display: 'grid', placeItems: 'center',
          border: active ? 'none' : '2px solid var(--pp-line)', background: active ? 'var(--pp-terracotta)' : 'transparent',
          color: '#fff', fontSize: 10, transition: 'all .2s' }}>
          {active && <i className="fa-solid fa-check"></i>}
        </span>
      </div>

      {/* the selected card carries the basket context — replaces the old dark bar */}
      {active && (
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 10,
          paddingTop: 12, borderTop: '1px solid var(--pp-line-soft)' }}>
          <div style={{ fontSize: 13, color: 'var(--pp-dark)', fontWeight: 600 }}>
            {ev.basket.length === 0
              ? <span style={{ color: 'var(--pp-muted)' }}>Basket empty — add services below</span>
              : <>est. <span style={{ fontWeight: 800, color: 'var(--pp-ink)' }}>{money(total)}</span></>}
          </div>
          <button className="pp-btn pp-btn-primary pp-btn-sm" onClick={(e) => { e.stopPropagation(); onView && onView(); }}
            disabled={ev.basket.length === 0}>
            View basket<i className="fa-solid fa-arrow-right" style={{ fontSize: 11 }}></i>
          </button>
        </div>
      )}
    </div>
  );
}

function DirectionA({ mobile, startEmpty }) {
  const shop = useShopping(startEmpty);
  const [cat, setCat] = useState(null);
  const [q, setQ] = useState('');
  const [menu, setMenu] = useState(false);
  const [scrolled, setScrolled] = useState(false);
  const list = filterServices(cat, q);
  const inB = (id) => shop.activeEvent && shop.activeEvent.basket.includes(id);

  const onScroll = useCallback((e) => {
    const t = e.target.scrollTop;
    setScrolled(prev => {
      if (!prev && t > 150) return true;
      if (prev && t < 90) return false;
      return prev;
    });
  }, []);

  const th = shop.activeEvent ? (EVENT_THEME[shop.activeEvent.type] || EVENT_THEME.Event) : null;
  const showCondensed = scrolled && shop.activeEvent;

  return (
    <div className="pp-root" style={{ height: '100%', display: 'flex', flexDirection: 'column', position: 'relative' }}>
      <Nav mobile={mobile} basketCount={shop.activeEvent ? shop.activeEvent.basket.length : 0} />

      {/* ===== condensed context strip — mounts ONLY after the hero scrolls away ===== */}
      {showCondensed && (
        <div className="ppa-condensed" style={{ '--accent': th ? th.c : 'var(--pp-terracotta)' }}>
          <div className="ppa-condensed-inner" style={{ display: 'flex', alignItems: 'center', gap: 12, padding: mobile ? '11px 16px' : '11px 28px' }}>
            <span style={{ width: 30, height: 30, borderRadius: 9, flex: 'none', display: 'grid', placeItems: 'center',
              background: th.c, color: '#fff' }}>
              <i className={'fa-solid ' + th.icon} style={{ fontSize: 13 }}></i>
            </span>
            <div style={{ display: 'flex', flexDirection: 'column', minWidth: 0, flex: 1 }}>
              <span style={{ fontSize: 9.5, fontWeight: 800, letterSpacing: '.12em', textTransform: 'uppercase', color: 'var(--pp-muted)', lineHeight: 1.2 }}>Shopping for</span>
              <div style={{ position: 'relative' }}>
                <button onClick={() => setMenu(m => !m)} style={{ background: 'none', border: 'none', cursor: 'pointer', padding: 0,
                  display: 'flex', alignItems: 'center', gap: 7, fontFamily: 'var(--pp-display)', fontWeight: 600,
                  fontSize: mobile ? 14 : 15.5, color: 'var(--pp-ink)', lineHeight: 1.2, maxWidth: '100%' }}>
                  <span style={{ overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{shop.activeEvent.title}</span>
                  <i className="fa-solid fa-chevron-down" style={{ fontSize: 10, color: 'var(--pp-muted)' }}></i>
                </button>
                {menu && (
                  <EventMenu events={shop.events} activeId={shop.activeId} onPick={shop.setActiveId}
                    onClose={() => setMenu(false)} onCreate={() => shop.createEvent('New Celebration', 'Date TBC', 50)}
                    anchorStyle={{ top: '128%', left: 0 }} />
                )}
              </div>
            </div>
            <div style={{ display: 'flex', alignItems: 'center', gap: 12, flex: 'none' }}>
              <span style={{ textAlign: 'right', whiteSpace: 'nowrap', lineHeight: 1.15 }}>
                <span style={{ display: 'block', fontWeight: 800, fontSize: 13.5, color: 'var(--pp-ink)' }}>{shop.activeEvent.basket.length} {shop.activeEvent.basket.length === 1 ? 'service' : 'services'}</span>
                <span style={{ display: 'block', fontSize: 11.5, color: 'var(--pp-muted)' }}>est. {money(shop.total)}</span>
              </span>
              <button className="pp-btn pp-btn-primary pp-btn-sm"><i className="fa-solid fa-basket-shopping"></i>{mobile ? '' : 'View basket'}</button>
            </div>
          </div>
        </div>
      )}

      <div className="pp-scroll" style={{ flex: 1 }} onScroll={onScroll}>
        {/* ===== STEP 1 · the picker (hero) ===== */}
        <div style={{ background: 'linear-gradient(180deg, var(--pp-warm) 0%, var(--pp-cream) 100%)',
          borderBottom: '1px solid var(--pp-line-soft)', padding: mobile ? '20px 16px 22px' : '28px 28px 26px' }}>
          <div className="pp-eyebrow" style={{ marginBottom: 6 }}>Step 1 · Who are we shopping for?</div>
          <h1 className="pp-h1" style={{ fontSize: mobile ? 25 : 32, marginBottom: 18 }}>Pick the event you're planning.</h1>

          {shop.events.length > 0 ? (
            <div style={{ display: mobile ? 'flex' : 'grid',
              gridTemplateColumns: 'repeat(auto-fill, minmax(290px, 1fr))', gap: 12, alignItems: 'start',
              overflowX: mobile ? 'auto' : 'visible', paddingBottom: mobile ? 4 : 0 }}>
              {shop.events.map(ev => (
                <div key={ev.id} style={{ flex: mobile ? '0 0 84%' : 'unset', minWidth: 0 }}>
                  <EventPickCard ev={ev} active={ev.id === shop.activeId} guests={ev.guests}
                    total={ev.id === shop.activeId ? shop.total : 0}
                    onClick={() => shop.setActiveId(ev.id)} onView={() => {}} />
                </div>
              ))}
              <button onClick={() => shop.createEvent('New Celebration', 'Date TBC', 50)} style={{
                flex: mobile ? '0 0 64%' : 'unset', cursor: 'pointer', background: 'transparent',
                border: '1.5px dashed var(--pp-line)', borderRadius: 16, padding: '15px 16px', color: 'var(--pp-terracotta-deep)',
                fontWeight: 700, fontSize: 14, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 9,
                minHeight: 76, alignSelf: 'stretch' }}>
                <i className="fa-solid fa-plus"></i>New event
              </button>
            </div>
          ) : (
            <div style={{ background: '#fff', border: '1.5px solid var(--pp-line)', borderRadius: 16, padding: 22, maxWidth: 460 }}>
              <div style={{ fontFamily: 'var(--pp-display)', fontWeight: 600, fontSize: 18, marginBottom: 4 }}>No events yet</div>
              <div className="pp-sub" style={{ marginBottom: 14 }}>Create an event and every service you add will collect under its basket.</div>
              <button className="pp-btn pp-btn-primary" onClick={() => shop.createEvent('My Celebration', 'Date TBC', 50)}>
                <i className="fa-solid fa-plus"></i>Create your first event
              </button>
            </div>
          )}
        </div>

        {/* ===== STEP 2 · browse & add ===== */}
        <div style={{ padding: mobile ? '18px 16px 40px' : '24px 28px 48px' }}>
          <div className="pp-eyebrow" style={{ marginBottom: 12, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
            <span>Step 2 · Add services</span>
            {shop.activeEvent && <span style={{ color: 'var(--pp-muted)', fontWeight: 700, letterSpacing: 0, textTransform: 'none', marginLeft: 7 }}>to {shop.activeEvent.title}</span>}
          </div>
          <div style={{ display: 'flex', gap: 10, alignItems: 'center', flexWrap: mobile ? 'wrap' : 'nowrap', marginBottom: 16 }}>
            <div style={{ flex: 1, minWidth: mobile ? '100%' : 280 }}><SearchBar value={q} onChange={setQ} /></div>
            <button className="pp-btn pp-btn-ghost"><i className="fa-solid fa-sliders"></i>Filters</button>
            {!mobile && (
              <select className="pp-btn pp-btn-ghost" style={{ appearance: 'none', paddingRight: 30 }}>
                <option>Recommended</option><option>Price: low to high</option><option>Top rated</option>
              </select>
            )}
          </div>

          <div style={{ marginBottom: 16, overflowX: 'auto', paddingBottom: 4 }}>
            <CategoryChips active={cat} onPick={setCat} />
          </div>

          <div style={{ display: 'flex', alignItems: 'baseline', justifyContent: 'space-between', marginBottom: 14 }}>
            <div className="pp-h2">{cat ? window.PP.categories.find(c => c.id === cat).name : 'All services'}</div>
            <div className="pp-sub">{list.length} found</div>
          </div>

          <div className={'pp-grid ' + (mobile ? 'c1' : 'c3')}>
            {list.map(s => (
              <ServiceCard key={s.id} s={s} inBasket={inB(s.id)} faved={!!shop.favs[s.id]}
                onAdd={shop.addToBasket} onFav={shop.toggleFav}
                addLabel={shop.activeEvent ? 'Add to event' : 'Add'} />
            ))}
          </div>
        </div>
      </div>

      <Toast msg={shop.toast} />
    </div>
  );
}
window.DirectionA = DirectionA;
