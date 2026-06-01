// Direction B — "Read-then-Decide"
// The description is condensed into tabs so the reader is never overwhelmed,
// then a hard visual break introduces a distinct "Build your booking" zone on
// a contrasting warm surface. A sticky bottom bar keeps the running total and
// CTA reachable at all times. Separation = vertical rhythm + surface + tabs.
function DirectionB() {
  const b = useBooking('quintet');
  const [tab, setTab] = useState('about');
  const [main, setMain] = useState(0);

  const tabs = [
    { id: 'about', label: 'Overview' },
    { id: 'included', label: 'What’s included' },
    { id: 'specs', label: 'Good to know' },
    { id: 'reviews', label: `Reviews · ${SERVICE.reviews}` },
  ];

  return (
    <div className="sv">
      <MiniNav />

      {/* hero: title left, gallery right */}
      <div style={{ background: '#FFFDFC', borderBottom: '1px solid var(--line-soft)' }}>
        <div style={{ maxWidth: 1140, margin: '0 auto', padding: '24px 40px 30px' }}>
          <Crumb />
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 44, alignItems: 'center', marginTop: 18 }}>
            <div>
              <div className="sv-eyebrow" style={{ marginBottom: 12 }}>{SERVICE.category} · {SERVICE.style}</div>
              <h1 className="sv-h1" style={{ fontSize: 46 }}>{SERVICE.title}</h1>
              <p className="sv-lead" style={{ margin: '14px 0 18px' }}>{SERVICE.tagline}</p>
              <RatingRow />
              <div style={{ display: 'flex', gap: 8, marginTop: 18, flexWrap: 'wrap' }}>
                <span className="sv-chip"><Icon name="pin" />{SERVICE.location}</span>
                <span className="sv-chip"><Icon name="bolt" />Replies {SERVICE.responseTime}</span>
              </div>
            </div>
            {/* gallery: main + filmstrip */}
            <div>
              <div className="sv-gallery-main" style={{ height: 300 }}>
                <div className="sv-ph" data-label={SERVICE.gallery[main]} style={{ width: '100%', height: '100%' }} />
              </div>
              <div style={{ display: 'grid', gridTemplateColumns: 'repeat(5,1fr)', gap: 8, marginTop: 8 }}>
                {SERVICE.gallery.map((g, i) => (
                  <div key={i} className={'sv-thumb' + (main === i ? ' is-active' : '')} onClick={() => setMain(i)} style={{ height: 56 }}>
                    <div className="sv-ph" data-label="" style={{ height: '100%' }} />
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* ---------- READ zone — tabbed description ---------- */}
      <div style={{ maxWidth: 1140, margin: '0 auto', padding: '36px 40px 8px' }}>
        <div style={{ display: 'flex', gap: 4, borderBottom: '1px solid var(--line)', marginBottom: 26 }}>
          {tabs.map((t) => (
            <button key={t.id} onClick={() => setTab(t.id)}
              style={{
                background: 'none', border: 'none', cursor: 'pointer', fontFamily: 'inherit',
                fontSize: 15, fontWeight: 600, padding: '12px 18px', position: 'relative',
                color: tab === t.id ? 'var(--ink)' : 'var(--muted)',
                borderBottom: '2.5px solid ' + (tab === t.id ? 'var(--accent)' : 'transparent'),
                marginBottom: -1, transition: 'color .15s',
              }}>{t.label}</button>
          ))}
        </div>

        <div style={{ minHeight: 250 }}>
          {tab === 'about' && (
            <div style={{ display: 'grid', gridTemplateColumns: '1.4fr 1fr', gap: 48 }}>
              <div className="sv-body" style={{ fontSize: 16.5 }}>
                {SERVICE.about.map((p, i) => <p key={i}>{p}</p>)}
              </div>
              <div className="sv-panel" style={{ padding: '20px 22px', alignSelf: 'start' }}>
                <VendorBlock />
                <p className="sv-body" style={{ fontSize: 14, marginTop: 14 }}>
                  “Tell me about your night and I’ll build the right line-up for it.”
                </p>
                <button className="sv-btn sv-btn-ghost" style={{ width: '100%', marginTop: 14, padding: '11px 16px' }}><Icon name="chat" style={{ width: 16, height: 16 }} />Message {SERVICE.vendor.name}</button>
              </div>
            </div>
          )}
          {tab === 'included' && (
            <div style={{ maxWidth: 760 }}>
              <p className="sv-body" style={{ fontSize: 16, marginBottom: 18 }}>Every package includes the essentials to run a great night — no hidden hire fees.</p>
              <div style={{ columns: 2, columnGap: 40 }}>
                <IncludedList />
              </div>
            </div>
          )}
          {tab === 'specs' && <SpecGrid />}
          {tab === 'reviews' && (
            <div style={{ display: 'grid', gridTemplateColumns: 'auto 1fr', gap: 44, alignItems: 'start' }}>
              <div style={{ textAlign: 'center', padding: '8px 24px 8px 0', borderRight: '1px solid var(--line)' }}>
                <div className="sv-h1" style={{ fontSize: 56 }}>{SERVICE.rating}</div>
                <Stars n={5} size={18} />
                <div className="sv-muted" style={{ fontSize: 13, marginTop: 6 }}>{SERVICE.reviews} reviews</div>
              </div>
              <Reviews limit={2} />
            </div>
          )}
        </div>
      </div>

      {/* ---------- the break ---------- */}
      <div style={{ maxWidth: 1140, margin: '0 auto', padding: '8px 40px' }}>
        <div className="sv-zone-rule"><Icon name="sparkle" style={{ width: 16, height: 16 }} />Build your booking</div>
      </div>

      {/* ---------- DECIDE zone — contrasting surface ---------- */}
      <div style={{ background: 'var(--warm)', borderTop: '1px solid var(--line)', borderBottom: '1px solid var(--line)', marginTop: 20 }}>
        <div style={{ maxWidth: 1140, margin: '0 auto', padding: '40px 40px 44px' }}>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 360px', gap: 48, alignItems: 'start' }}>
            <div>
              <h2 className="sv-h2" style={{ fontSize: 24, marginBottom: 4 }}>1 · Choose your line-up</h2>
              <p className="sv-muted" style={{ fontSize: 14, marginBottom: 18 }}>Pick the package that fits your event. You can adjust details with {SERVICE.vendor.name} after.</p>
              <div style={{ display: 'grid', gap: 12 }}>
                {SERVICE.packages.map((p) => <PackageCard key={p.id} p={p} active={b.pkgId === p.id} onSelect={b.setPkgId} />)}
              </div>

              <h2 className="sv-h2" style={{ fontSize: 24, margin: '34px 0 4px' }}>2 · Add the finishing touches</h2>
              <p className="sv-muted" style={{ fontSize: 14, marginBottom: 8 }}>Optional — bolt on extras to tailor your night.</p>
              <div className="sv-panel" style={{ padding: '6px 20px' }}>
                {SERVICE.extras.map((e) => <ExtraRow key={e.id} e={e} on={!!b.extras[e.id]} onToggle={b.toggleExtra} />)}
              </div>
            </div>

            {/* live summary */}
            <aside className="sv-panel" style={{ padding: '22px 24px', position: 'sticky', top: 88 }}>
              <div className="sv-eyebrow">Your selection</div>
              <div style={{ marginTop: 14, paddingBottom: 14, borderBottom: '1px solid var(--line-soft)' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 15 }}>
                  <span style={{ fontWeight: 700, color: 'var(--ink)' }}>{b.pkg.name}</span>
                  <span style={{ fontWeight: 700 }}>{gbp(b.pkg.price)}</span>
                </div>
                <div className="sv-muted" style={{ fontSize: 12.5 }}>{b.pkg.meta.join(' · ')}</div>
              </div>
              {b.chosenExtras.length > 0 ? (
                <div style={{ padding: '12px 0', borderBottom: '1px solid var(--line-soft)', display: 'grid', gap: 8 }}>
                  {b.chosenExtras.map((e) => (
                    <div key={e.id} style={{ display: 'flex', justifyContent: 'space-between', fontSize: 13.5, color: 'var(--ink-soft)' }}>
                      <span>{e.name}</span><span>+{gbp(e.price)}</span>
                    </div>
                  ))}
                </div>
              ) : (
                <div className="sv-muted" style={{ fontSize: 13, padding: '12px 0', borderBottom: '1px solid var(--line-soft)' }}>No extras added yet.</div>
              )}
              <div className="sv-total-row" style={{ marginTop: 16 }}>
                <span className="lbl">Estimated total</span>
                <span className="amt">{gbp(b.total)}</span>
              </div>
              <button className="sv-btn sv-btn-primary sv-btn-lg" style={{ marginTop: 16 }}><Icon name="calendar" style={{ width: 18, height: 18 }} />Add to my event</button>
              <p className="sv-total-sub" style={{ marginTop: 12, textAlign: 'center' }}>No charge until {SERVICE.vendor.name} confirms your quote.</p>
            </aside>
          </div>
        </div>
      </div>

      <div style={{ height: 40 }} />

      {/* ---------- sticky bottom decision bar ---------- */}
      <div className="sv-bottombar">
        <div style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
          <span className="sv-vendor-ava" style={{ width: 42, height: 42, fontSize: 15 }}>{SERVICE.vendor.initials}</span>
          <div>
            <div style={{ fontWeight: 700, fontSize: 15, color: 'var(--ink)', whiteSpace: 'nowrap' }}>{SERVICE.title} · {b.pkg.name}</div>
            <div className="sv-muted" style={{ fontSize: 12.5 }}>{b.chosenExtras.length} extra{b.chosenExtras.length === 1 ? '' : 's'} added</div>
          </div>
        </div>
        <div style={{ display: 'flex', alignItems: 'center', gap: 20 }}>
          <div style={{ textAlign: 'right' }}>
            <div className="sv-total-sub">Estimated total</div>
            <div className="sv-h2" style={{ fontSize: 24 }}>{gbp(b.total)}</div>
          </div>
          <button className="sv-btn sv-btn-primary" style={{ padding: '14px 26px' }}>Add to event<Icon name="arrow" style={{ width: 17, height: 17 }} /></button>
        </div>
      </div>
    </div>
  );
}
window.DirectionB = DirectionB;
