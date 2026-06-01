// Direction C — "Editorial Configurator"
// The premium / novel take. A cinematic full-bleed gallery hero sets the tone,
// the description reads like an editorial feature (display type, a pull-quote),
// and the booking lives in a visually inverted dark "configurator" with a
// receipt that builds as you choose. Separation = a complete shift in visual
// language between reading and deciding.
function DirectionC() {
  const b = useBooking('full');
  const [main, setMain] = useState(0);

  return (
    <div className="sv">
      <MiniNav />

      {/* ---------- cinematic hero ---------- */}
      <div className="sv-c-hero">
        <div className="sv-ph sv-ph-dark" data-label={SERVICE.gallery[main]} />
        <div className="sv-c-hero-grad" />
        <div className="sv-c-hero-inner">
          <div>
            <div className="sv-eyebrow" style={{ color: 'var(--gold)', marginBottom: 12 }}>{SERVICE.style}</div>
            <h1 className="sv-h1" style={{ fontSize: 60, color: '#fff' }}>{SERVICE.title}</h1>
            <p style={{ fontSize: 18, color: 'rgba(255,255,255,0.9)', margin: '12px 0 16px', maxWidth: 560 }}>{SERVICE.tagline}</p>
            <div style={{ display: 'flex', alignItems: 'center', gap: 16, color: '#fff' }}>
              <span className="sv-stars" style={{ color: 'var(--gold)' }}>
                {Array.from({ length: 5 }).map((_, i) => <Icon key={i} name="star" style={{ width: 17, height: 17 }} />)}
              </span>
              <b style={{ fontSize: 15 }}>{SERVICE.rating}</b>
              <span style={{ fontSize: 14, opacity: 0.85 }}>· {SERVICE.reviews} reviews</span>
              <span style={{ fontSize: 14, opacity: 0.85, display: 'inline-flex', alignItems: 'center', gap: 6 }}><Icon name="pin" style={{ width: 15, height: 15 }} />{SERVICE.location}</span>
            </div>
          </div>
          <div style={{ display: 'flex', gap: 8, flex: '0 0 auto' }}>
            {SERVICE.gallery.slice(0, 4).map((g, i) => (
              <div key={i} onClick={() => setMain(i)} style={{ width: 64, height: 64, borderRadius: 10, overflow: 'hidden', cursor: 'pointer', border: '2px solid ' + (main === i ? 'var(--gold)' : 'rgba(255,255,255,0.4)') }}>
                <div className="sv-ph sv-ph-dark" data-label="" style={{ width: '100%', height: '100%' }} />
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* ---------- editorial description ---------- */}
      <div style={{ maxWidth: 1080, margin: '0 auto', padding: '56px 40px 24px' }}>
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 56, alignItems: 'start' }}>
          <div>
            <div className="sv-eyebrow" style={{ marginBottom: 14 }}>The act</div>
            <p style={{ fontFamily: 'var(--font-display)', fontSize: 23, lineHeight: 1.5, color: 'var(--ink)', fontWeight: 500 }}>{SERVICE.about[0]}</p>
          </div>
          <div className="sv-body" style={{ fontSize: 16 }}>
            <p>{SERVICE.about[1]}</p>
            <div className="sv-pull" style={{ marginTop: 22 }}>“The dancefloor was packed from the first note.”
              <div style={{ fontFamily: 'var(--font-sans)', fontStyle: 'normal', fontSize: 13, fontWeight: 600, color: 'var(--muted)', marginTop: 10, borderLeft: 'none', paddingLeft: 0 }}>— Sophie &amp; James, wedding</div>
            </div>
          </div>
        </div>

        {/* included + specs band */}
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1.1fr', gap: 56, marginTop: 52, paddingTop: 44, borderTop: '1px solid var(--line)' }}>
          <div>
            <h2 className="sv-section-label">Included as standard</h2>
            <IncludedList />
          </div>
          <div>
            <h2 className="sv-section-label">Good to know</h2>
            <SpecGrid />
          </div>
        </div>
      </div>

      {/* ---------- the dark configurator (decide) ---------- */}
      <div className="sv-dark" style={{ marginTop: 48 }}>
        <div style={{ maxWidth: 1080, margin: '0 auto', padding: '52px 40px 56px' }}>
          <div style={{ textAlign: 'center', marginBottom: 36 }}>
            <div className="sv-eyebrow">Make it yours</div>
            <h2 className="sv-h2" style={{ fontSize: 36, color: '#fff', marginTop: 8 }}>Build your booking</h2>
            <p style={{ color: 'rgba(243,235,226,0.66)', fontSize: 15.5, marginTop: 8 }}>Choose a line-up, add any extras, and we’ll send {SERVICE.vendor.name} your request.</p>
          </div>

          <div style={{ display: 'grid', gridTemplateColumns: '1fr 380px', gap: 44, alignItems: 'start' }}>
            <div>
              <div style={{ fontSize: 13, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.08em', color: 'var(--gold)', marginBottom: 14 }}>Choose your line-up</div>
              <div style={{ display: 'grid', gap: 12 }}>
                {SERVICE.packages.map((p) => (
                  <button key={p.id} type="button" className={'sv-pkg-dark' + (b.pkgId === p.id ? ' is-active' : '')} onClick={() => b.setPkgId(p.id)}>
                    <span className="nm">{p.name}{p.tag && <span className="tg">{p.tag}</span>}</span>
                    <span className="pr">{gbp(p.price)}</span>
                    <span className="ds">{p.desc} · {p.meta.join(' · ')}</span>
                  </button>
                ))}
              </div>

              <div style={{ fontSize: 13, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.08em', color: 'var(--gold)', margin: '32px 0 6px' }}>Optional extras</div>
              <div>
                {SERVICE.extras.map((e) => (
                  <div key={e.id} className={'sv-extra-dark' + (b.extras[e.id] ? ' is-on' : '')} onClick={() => b.toggleExtra(e.id)}>
                    <span className="ck"><Icon name="checkbold" /></span>
                    <span style={{ flex: 1 }}>
                      <span className="nm">{e.name}</span>
                      <span className="ds" style={{ display: 'block' }}>{e.desc}</span>
                    </span>
                    <span className="pr">+{gbp(e.price)}</span>
                  </div>
                ))}
              </div>
            </div>

            {/* receipt */}
            <aside className="sv-receipt" style={{ position: 'sticky', top: 88 }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 8, color: 'var(--gold)', fontSize: 12, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.08em', marginBottom: 8 }}>
                <Icon name="music" style={{ width: 15, height: 15 }} />Your booking
              </div>
              <div className="sv-receipt-line" style={{ paddingTop: 4 }}>
                <span className="l">{b.pkg.name}</span><span className="r">{gbp(b.pkg.price)}</span>
              </div>
              <div style={{ fontSize: 12, color: 'rgba(243,235,226,0.5)', marginTop: -4, marginBottom: 4 }}>{b.pkg.meta.join(' · ')}</div>
              {b.chosenExtras.map((e) => (
                <div key={e.id} className="sv-receipt-line"><span className="l">{e.name}</span><span className="r">+{gbp(e.price)}</span></div>
              ))}
              <hr className="sv-receipt-divider" />
              <div className="sv-receipt-line sv-receipt-total" style={{ alignItems: 'center' }}>
                <span className="l">Estimated total</span><span className="r">{gbp(b.total)}</span>
              </div>
              <button className="sv-btn sv-btn-gold sv-btn-lg" style={{ marginTop: 18 }}><Icon name="calendar" style={{ width: 18, height: 18 }} />Add to my event</button>
              <button className="sv-btn sv-btn-lg" style={{ marginTop: 10, background: 'transparent', color: '#F3EBE2', border: '1.5px solid rgba(255,255,255,0.2)' }}><Icon name="chat" style={{ width: 16, height: 16 }} />Ask {SERVICE.vendor.name} a question</button>
              <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8, marginTop: 16, fontSize: 12.5, color: 'rgba(243,235,226,0.6)' }}>
                <Icon name="shield" style={{ width: 15, height: 15, color: 'var(--gold)' }} />No charge until your quote is confirmed
              </div>
            </aside>
          </div>
        </div>
      </div>

      {/* ---------- vendor + reviews footer ---------- */}
      <div style={{ maxWidth: 1080, margin: '0 auto', padding: '52px 40px 64px' }}>
        <div style={{ display: 'grid', gridTemplateColumns: '320px 1fr', gap: 56, alignItems: 'start' }}>
          <div>
            <h2 className="sv-section-label">Your host</h2>
            <div className="sv-panel" style={{ padding: '20px 22px' }}>
              <VendorBlock compact />
              <div style={{ display: 'flex', gap: 8, marginTop: 14, flexWrap: 'wrap' }}>
                <span className="sv-chip"><Icon name="bolt" />Replies {SERVICE.responseTime}</span>
                <span className="sv-chip"><Icon name="calendar" />{SERVICE.bookings} bookings</span>
              </div>
            </div>
          </div>
          <div>
            <h2 className="sv-section-label">What couples &amp; companies say</h2>
            <Reviews limit={2} />
          </div>
        </div>
      </div>
    </div>
  );
}
window.DirectionC = DirectionC;
