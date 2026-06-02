// Canvas assembly — For Your Events /profile redesign explorations
const { createElement: h } = React;

function Brief() {
  const Pick = ({ role, name, desc }) => (
    <div style={{ flex: 1, borderTop: '3px solid var(--terra)', paddingTop: 14 }}>
      <div className="eyebrow" style={{ marginBottom: 6 }}>{role}</div>
      <div style={{ fontFamily: 'var(--display)', fontSize: 20, fontWeight: 600, marginBottom: 7 }}>{name}</div>
      <p className="muted" style={{ fontSize: 13.5, lineHeight: 1.55 }}>{desc}</p>
    </div>
  );
  return (
    <div className="fye" style={{ height: '100%', padding: '40px 44px', background: '#FFFDFB', overflow: 'hidden' }}>
      <div className="eyebrow">For Your Events · /profile redesign</div>
      <h1 style={{ fontFamily: 'var(--display)', fontWeight: 600, fontSize: 38, letterSpacing: '-.02em', marginTop: 10 }}>
        The <span style={{ color: 'var(--terra-deep)', fontStyle: 'italic' }}>chosen</span> directions
      </h1>
      <p className="muted" style={{ fontSize: 15, lineHeight: 1.6, maxWidth: 760, marginTop: 14 }}>
        Both dashboards are rebuilt in the real brand — terracotta + cream, Fraunces &amp; Manrope — replacing the
        generic Bootstrap blues from the live site, with realistic UK-marketplace data. The <b>customer</b> keeps its
        familiar layout, refined and brand-cohesive. The <b>vendor</b> moves to a focused ops console built around a
        single prioritised action queue. Drag to pan, scroll to zoom, click any frame’s ⤢ to focus it full-screen.
      </p>
      <div style={{ display: 'flex', gap: 32, marginTop: 30 }}>
        <Pick role="Customer dashboard" name="Refined" desc="The same information architecture as today, made brand-cohesive: warm accent palette, tighter density, serif headings, calmer cards. Attention items, events, payments and messages — at a glance." />
        <Pick role="Vendor dashboard" name="Command Centre" desc="A dense ops console. One prioritised action queue replaces the scattered cards, a compact KPI strip sits up top, and a sticky rail keeps payouts, upcoming events and quick actions in reach." />
      </div>
      <div style={{ display: 'flex', gap: 22, marginTop: 34, alignItems: 'center', flexWrap: 'wrap' }}>
        <span className="faint" style={{ fontSize: 12, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '.08em' }}>Palette</span>
        {[['Terracotta', 'var(--terra)'], ['Sage', 'var(--sage)'], ['Gold', 'var(--gold)'], ['Slate', 'var(--slate)'], ['Plum', 'var(--plum)'], ['Cream', 'var(--paper)']].map(([n, c]) => (
          <span key={n} style={{ display: 'inline-flex', alignItems: 'center', gap: 8, fontSize: 13, fontWeight: 600 }}>
            <span style={{ width: 18, height: 18, borderRadius: 5, background: c, border: '1px solid rgba(0,0,0,.08)' }}></span>{n}
          </span>
        ))}
      </div>
    </div>
  );
}

function App() {
  return (
    <DesignCanvas>
      <DCSection id="brief" title="Start here" subtitle="The two chosen directions and the brand system">
        <DCArtboard id="brief" label="Summary" width={1000} height={440}><Brief /></DCArtboard>
      </DCSection>

      <DCSection id="customer" title="Customer dashboard" subtitle="Refined — what a planner sees at /profile">
        <DCArtboard id="cust-a" label="Customer · Refined" width={1240} height={1640}><CustomerRefined /></DCArtboard>
      </DCSection>

      <DCSection id="vendor" title="Vendor dashboard" subtitle="Command Centre — what a supplier sees at /profile">
        <DCArtboard id="vend-c" label="Vendor · Command Centre" width={1240} height={1140}><VendorCommand /></DCArtboard>
      </DCSection>
    </DesignCanvas>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App />);
