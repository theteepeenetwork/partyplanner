// Direction A — "Classic Split"
// The by-the-book marketplace refinement. Description content flows down the
// left column; every booking *choice* is corralled into a distinct, sticky,
// elevated panel on the right. Separation = surface + position.
function DirectionA() {
  const b = useBooking('quintet');
  const [main, setMain] = useState(0);

  return (
    <div className="sv">
      <MiniNav />
      <div style={{ maxWidth: 1140, margin: '0 auto', padding: '26px 40px 60px' }}>
        <Crumb />

        {/* title block */}
        <div style={{ display: 'flex', alignItems: 'flex-end', justifyContent: 'space-between', gap: 24, margin: '18px 0 22px' }}>
          <div>
            <div className="sv-eyebrow" style={{ marginBottom: 10 }}>{SERVICE.style}</div>
            <h1 className="sv-h1" style={{ fontSize: 42 }}>{SERVICE.title}</h1>
            <p className="sv-lead" style={{ margin: '10px 0 0', maxWidth: 620 }}>{SERVICE.tagline}</p>
            <div style={{ display: 'flex', alignItems: 'center', gap: 18, marginTop: 16, flexWrap: 'wrap' }}>
              <RatingRow />
              <span className="sv-chip"><Icon name="pin" />{SERVICE.location}</span>
            </div>
          </div>
          <div style={{ display: 'flex', gap: 8, flex: '0 0 auto' }}>
            <button className="sv-btn sv-btn-ghost" style={{ padding: '11px 15px' }}><Icon name="heart" style={{ width: 17, height: 17 }} />Save</button>
            <button className="sv-btn sv-btn-ghost" style={{ padding: '11px 15px' }}><Icon name="share" style={{ width: 17, height: 17 }} />Share</button>
          </div>
        </div>

        {/* gallery — large image + two stacked thumbs */}
        <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr', gap: 12, height: 420, marginBottom: 44 }}>
          <div className="sv-gallery-main" style={{ height: '100%' }}>
            <div className="sv-ph" data-label={SERVICE.gallery[main]} style={{ width: '100%', height: '100%' }} />
            <span className="sv-pop">Featured supplier</span>
          </div>
          <div style={{ display: 'grid', gridTemplateRows: '1fr 1fr', gap: 12 }}>
            {[1, 2].map((i) =>
            <div key={i} className={'sv-thumb' + (main === i ? ' is-active' : '')} onClick={() => setMain(i)} style={{ height: '100%' }}>
                <div className="sv-ph" data-label={SERVICE.gallery[i]} />
                {i === 2 &&
              <div style={{ position: 'absolute', inset: 0, background: 'rgba(34,27,24,0.45)', color: '#fff', display: 'grid', placeItems: 'center', fontWeight: 700, fontSize: 15 }}>
                    +{SERVICE.gallery.length - 2} photos
                  </div>
              }
              </div>
            )}
          </div>
        </div>

        {/* split: info (left) · booking (right, sticky) */}
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 384px', gap: 56, alignItems: 'start' }}>
          {/* ---------- LEFT — the description ---------- */}
          <div>
            <section>
              <h2 className="sv-section-label">About this band</h2>
              <div className="sv-body" style={{ fontSize: 16.5 }}>
                {SERVICE.about.map((p, i) => <p key={i}>{p}</p>)}
              </div>
            </section>

            <section style={{ marginTop: 40 }}>
              <h2 className="sv-section-label">What’s included as standard</h2>
              <IncludedList />
            </section>

            <section style={{ marginTop: 40 }}>
              <h2 className="sv-section-label">Good to know</h2>
              <SpecGrid />
            </section>

            <section style={{ marginTop: 40 }} data-comment-anchor="cbe138a16e-section-73-13">
              <h2 className="sv-section-label">Meet your host</h2>
              <MeetYourHost />
            </section>

            <section style={{ marginTop: 40 }}>
              <div style={{ display: 'flex', alignItems: 'baseline', justifyContent: 'space-between', marginBottom: 14 }}>
                <h2 className="sv-section-label" style={{ margin: 0 }}>Recent reviews</h2>
                <a className="sv-muted" style={{ fontSize: 13.5, fontWeight: 600, textDecoration: 'none', color: 'var(--accent)' }}>See all {SERVICE.reviews} →</a>
              </div>
              <Reviews />
            </section>
          </div>

          {/* ---------- RIGHT — the choices (sticky) ---------- */}
          <aside style={{ position: 'sticky', top: 88 }}>
            <div className="sv-panel" style={{ overflow: 'hidden' }}>
              <div style={{ padding: '18px 22px', borderBottom: '1px solid var(--line-soft)', background: 'var(--warm)' }}>
                <div className="sv-eyebrow">Your booking</div>
                <div style={{ display: 'flex', alignItems: 'baseline', gap: 8, marginTop: 6 }}>
                  <span className="sv-h2" style={{ fontSize: 26 }}>{gbp(b.total)}</span>
                  <span className="sv-total-sub">est. total{b.extrasTotal > 0 ? ` · incl. ${gbp(b.extrasTotal)} extras` : ''}</span>
                </div>
              </div>

              <div style={{ padding: '20px 22px' }}>
                <div style={{ fontSize: 12.5, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.08em', color: 'var(--muted)', marginBottom: 12 }}>Choose a package</div>
                <div style={{ display: 'grid', gap: 10 }}>
                  {SERVICE.packages.map((p) => <PackageCard key={p.id} p={p} active={b.pkgId === p.id} onSelect={b.setPkgId} />)}
                </div>

                <div style={{ fontSize: 12.5, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.08em', color: 'var(--muted)', margin: '22px 0 4px' }}>Add optional extras</div>
                <div>
                  {SERVICE.extras.map((e) => <ExtraRow key={e.id} e={e} on={!!b.extras[e.id]} onToggle={b.toggleExtra} />)}
                </div>

                <div className="sv-total-row" style={{ marginTop: 20, paddingTop: 18, borderTop: '1px solid var(--line)' }}>
                  <span className="lbl">Estimated total</span>
                  <span className="amt">{gbp(b.total)}</span>
                </div>
                <p className="sv-total-sub" style={{ marginTop: 4 }}>Final price confirmed by {SERVICE.vendor.name} for your date & guest count.</p>

                <button className="sv-btn sv-btn-primary sv-btn-lg" style={{ marginTop: 16 }}><Icon name="calendar" style={{ width: 18, height: 18 }} />Add to my event</button>
                <button className="sv-btn sv-btn-ghost sv-btn-lg" style={{ marginTop: 10 }}><Icon name="chat" style={{ width: 17, height: 17 }} />Message {SERVICE.vendor.name}</button>

                <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginTop: 16, fontSize: 12.5, color: 'var(--muted)' }}>
                  <Icon name="shield" style={{ width: 15, height: 15, color: 'var(--accent)' }} />
                  Free cancellation up to 30 days before
                </div>
              </div>
            </div>
          </aside>
        </div>
      </div>
    </div>);

}
window.DirectionA = DirectionA;