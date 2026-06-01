// Renders the three service-view directions side by side on a design canvas.
function App() {
  return (
    <DesignCanvas>
      <DCSection id="sv" title="Service detail — presentation directions"
        subtitle="Same band, same data · three ways to separate the description from the booking choices">
        <DCArtboard id="a" label="A · Classic Split — sticky booking sidebar" width={1200} height={2680} style={{ background: '#F6F1EB' }}>
          <DirectionA />
        </DCArtboard>
        <DCArtboard id="b" label="B · Read-then-Decide — tabs + decision zone + sticky bar" width={1200} height={1960} style={{ background: '#F6F1EB' }}>
          <DirectionB />
        </DCArtboard>
        <DCArtboard id="c" label="C · Editorial Configurator — cinematic + dark receipt" width={1200} height={2740} style={{ background: '#F6F1EB' }}>
          <DirectionC />
        </DCArtboard>
      </DCSection>
    </DesignCanvas>
  );
}
ReactDOM.createRoot(document.getElementById('root')).render(<App />);
