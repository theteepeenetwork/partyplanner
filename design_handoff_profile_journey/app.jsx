// For Your Events — prototype router
function Root() {
  const route = useRoute();
  const seg = route.split('/');
  const role = seg[0] === 'v' ? 'vendor' : 'customer';
  const Comp = window.PAGES[route]
    || window.PAGES[seg.slice(0, 2).join('/')]
    || window.PAGES[role === 'vendor' ? 'v/dashboard' : 'c/dashboard'];
  return (
    <div className="fye fye-app">
      <TopBar role={role} />
      <Tabs role={role} route={route} />
      <div className="fye-main">
        <Comp params={seg.slice(2)} route={route} />
      </div>
    </div>
  );
}
ReactDOM.createRoot(document.getElementById('root')).render(<Root />);
