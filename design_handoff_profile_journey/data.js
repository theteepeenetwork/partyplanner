// For Your Events — prototype data layer
// Self-contained: events, bookings, message threads, payments, services, calendar.
window.PROTO = (function () {
  // ---------------- CUSTOMER ----------------
  const cEvents = [
    { id: 'studio', title: 'Studio Summer Social', type: 'Corporate', date: 'Sat 25 Jul 2026', short: '25 Jul', days: 53, loc: 'Riverside Rooms, Bath', guests: 60, budget: 4200 },
    { id: 'wedding', title: "Amara & Daniel's Wedding", type: 'Wedding', date: 'Sat 14 Aug 2026', short: '14 Aug', days: 73, loc: 'The Old Barn, Cotswolds', guests: 120, budget: 18000 },
    { id: 'birthday', title: "Mum's 60th Birthday", type: 'Birthday', date: 'Sat 03 Oct 2026', short: '03 Oct', days: 123, loc: 'Home marquee, Bristol', guests: 45, budget: 3800 },
  ];

  // status: pending | accepted | confirmed | declined
  const cBookings = [
    { id: 'b1', ev: 'wedding', cat: 'Catering', vendor: 'The Roaming Kitchen', vi: 'RK', svc: 'Wedding Feast Package', status: 'accepted', amount: 4200, deposit: 630, depositPaid: false, when: '2h ago', thread: 't-rk', icon: 'fa-utensils' },
    { id: 'b2', ev: 'wedding', cat: 'Photography', vendor: 'Wandering Lens', vi: 'WL', svc: 'Full-day photography', status: 'confirmed', amount: 1850, deposit: 277, depositPaid: true, when: 'Tue', thread: 't-wl', icon: 'fa-camera' },
    { id: 'b3', ev: 'wedding', cat: 'Flowers & styling', vendor: 'Bloom & Wild Florals', vi: 'BW', svc: 'Arch, aisle & 12 tablescapes', status: 'accepted', amount: 2400, deposit: 360, depositPaid: false, when: '5h ago', thread: 't-bw', icon: 'fa-seedling' },
    { id: 'b4', ev: 'wedding', cat: 'Venue', vendor: 'The Old Barn', vi: 'OB', svc: 'Exclusive venue hire', status: 'confirmed', amount: 6500, deposit: 1500, depositPaid: true, when: '3 wk ago', thread: null, icon: 'fa-location-dot' },
    { id: 'b5', ev: 'wedding', cat: 'Entertainment', vendor: 'Sax & The City', vi: 'SC', svc: 'Live sax + DJ set', status: 'declined', amount: 1200, deposit: 0, depositPaid: false, when: 'Yesterday', thread: null, icon: 'fa-music' },
    { id: 'b6', ev: 'wedding', cat: 'Transport', vendor: 'Cotswold Classics', vi: 'CC', svc: 'Vintage car · 2 trips', status: 'confirmed', amount: 480, deposit: 100, depositPaid: true, when: '1 wk ago', thread: null, icon: 'fa-car' },
    { id: 'b7', ev: 'wedding', cat: 'Cake & desserts', vendor: 'Sweet Tier Bakery', vi: 'ST', svc: '3-tier wedding cake', status: 'pending', amount: 420, deposit: 0, depositPaid: false, when: '1d ago', thread: 't-st', icon: 'fa-cake-candles' },
    { id: 'b8', ev: 'birthday', cat: 'Bar', vendor: 'Velvet & Vine Bar Co.', vi: 'VV', svc: 'Mobile cocktail bar', status: 'pending', amount: 1100, deposit: 0, depositPaid: false, when: '2d ago', thread: 't-vv', icon: 'fa-martini-glass' },
    { id: 'b9', ev: 'birthday', cat: 'Catering', vendor: 'The Roaming Kitchen', vi: 'RK', svc: 'Grazing tables', status: 'pending', amount: 1320, deposit: 0, depositPaid: false, when: '2d ago', thread: 't-rk', icon: 'fa-utensils' },
    { id: 'b10', ev: 'studio', cat: 'Catering', vendor: 'The Roaming Kitchen', vi: 'RK', svc: 'Grazing tables · 60', status: 'accepted', amount: 770, deposit: 115, depositPaid: false, when: '4h ago', thread: 't-rk', icon: 'fa-utensils' },
  ];

  // planning categories per event (for event detail gaps)
  const cPlanning = {
    wedding: ['Venue', 'Catering', 'Photography', 'Flowers & styling', 'Transport', 'Entertainment', 'Cake & desserts', 'Hair & beauty'],
    studio: ['Venue', 'Catering', 'Drinks', 'AV & staging', 'Photography'],
    birthday: ['Marquee', 'Catering', 'Bar', 'Cake & desserts', 'Music', 'Decor'],
  };

  const cThreads = {
    't-rk': { vendor: 'The Roaming Kitchen', vi: 'RK', role: 'Caterer · Bath', msgs: [
      { who: 'them', t: "Hi Amara! We'd love to cater your wedding. Quick question — any dietary requirements across the 120 guests?", time: 'Mon 09:14' },
      { who: 'me', t: 'Hi! About 12 vegetarian, 4 vegan, and 2 gluten-free. Is that workable?', time: 'Mon 10:02' },
      { who: 'them', t: 'Absolutely — all covered in the package. I\u2019ve accepted the booking, deposit is 15% (£630) to lock the date.', time: '2h ago' },
    ] },
    't-bw': { vendor: 'Bloom & Wild Florals', vi: 'BW', role: 'Florist · Cirencester', msgs: [
      { who: 'them', t: 'Mood board attached for the arch and tablescapes 🌿 Let me know what you think!', time: '5h ago' },
      { who: 'me', t: 'These are stunning. Love the muted palette. Happy to go ahead.', time: '4h ago' },
    ] },
    't-wl': { vendor: 'Wandering Lens', vi: 'WL', role: 'Photographer · Bristol', msgs: [
      { who: 'them', t: 'Confirmed for the 14th! Sending the day timeline shortly.', time: 'Tue 16:40' },
    ] },
    't-st': { vendor: 'Sweet Tier Bakery', vi: 'ST', role: 'Cakes · Stroud', msgs: [
      { who: 'them', t: 'Thanks for the request! Would you like a tasting box posted before you decide?', time: '1d ago' },
    ] },
    't-vv': { vendor: 'Velvet & Vine Bar Co.', vi: 'VV', role: 'Mobile bar · Bath', msgs: [
      { who: 'them', t: 'Hi! For 45 guests we\u2019d suggest the cocktail + fizz package. Want a quote?', time: '2d ago' },
    ] },
  };

  // ---------------- VENDOR ----------------
  // status: new | quoted | confirmed | declined
  const vRequests = [
    { id: 'r1', customer: 'Amara Okafor', ci: 'AO', ev: "Amara & Daniel's Wedding", svc: 'Wedding Feast Package', date: '14 Aug 2026', short: '14 Aug', guests: 120, value: 4200, status: 'new', prio: 'hi', when: '2h ago', thread: 'vt-amara' },
    { id: 'r2', customer: 'James Whitfield', ci: 'JW', ev: 'Whitfield 25th Anniversary', svc: 'Canapé & Drinks Reception', date: '06 Sep 2026', short: '06 Sep', guests: 80, value: 1850, status: 'new', prio: 'hi', when: '5h ago', thread: 'vt-james' },
    { id: 'r3', customer: 'Priya Sharma', ci: 'PS', ev: 'Diwali Office Party', svc: 'Grazing Tables', date: '08 Nov 2026', short: '08 Nov', guests: 60, value: 1320, status: 'quoted', prio: 'md', when: 'Yesterday', thread: 'vt-priya' },
    { id: 'r4', customer: 'Leah Bennett', ci: 'LB', ev: "Leah's 30th", svc: 'BBQ & Grill Station', date: '19 Jul 2026', short: '19 Jul', guests: 40, value: 980, status: 'quoted', prio: 'md', when: '2d ago', thread: 'vt-leah' },
    { id: 'r5', customer: 'Connor Daly', ci: 'CD', ev: 'Summer Garden Party', svc: 'Grazing Tables', date: '02 Aug 2026', short: '02 Aug', guests: 35, value: 770, status: 'new', prio: 'lo', when: '3d ago', thread: 'vt-connor' },
    { id: 'r6', customer: 'Sophie Tran', ci: 'ST', ev: 'Tran Wedding', svc: 'Wedding Feast Package', date: '21 Jun 2026', short: '21 Jun', guests: 90, value: 3400, status: 'confirmed', prio: 'md', when: '1 wk ago', thread: null },
    { id: 'r7', customer: 'Office of M&G', ci: 'MG', ev: 'Q3 Town Hall', svc: 'Grazing Tables', date: '28 Jun 2026', short: '28 Jun', guests: 70, value: 1480, status: 'confirmed', prio: 'lo', when: '2 wk ago', thread: null },
    { id: 'r8', customer: 'Rachel Green', ci: 'RG', ev: "Green's 50th", svc: 'BBQ & Grill Station', date: '12 Jul 2026', short: '12 Jul', guests: 50, value: 1150, status: 'declined', prio: 'lo', when: '3 wk ago', thread: null },
  ];

  const vServices = [
    { id: 's1', title: 'Wedding Feast Package', desc: 'A three-course seasonal sit-down menu for weddings, with canapés, service staff and full setup.', priceFrom: 32, unit: 'per head', bookings: 12, hasDesc: true, hasImg: true, hasPrice: true, hasPolicy: true, active: true },
    { id: 's2', title: 'Canapé & Drinks Reception', desc: 'Eight canapés per guest plus a welcome drink, ideal for receptions and corporate events.', priceFrom: 18, unit: 'per head', bookings: 7, hasDesc: true, hasImg: true, hasPrice: true, hasPolicy: false, active: true },
    { id: 's3', title: 'BBQ & Grill Station', desc: 'Live-fire grazing station with seasonal sides. Relaxed dining for gardens and marquees.', priceFrom: 24, unit: 'per head', bookings: 4, hasDesc: true, hasImg: false, hasPrice: true, hasPolicy: false, active: true },
    { id: 's4', title: 'Grazing Tables', desc: 'Abundant shared grazing table of charcuterie, cheese, breads and seasonal produce.', priceFrom: 14, unit: 'per head', bookings: 9, hasDesc: true, hasImg: true, hasPrice: true, hasPolicy: true, active: true },
  ];

  const vThreads = {
    'vt-amara': { customer: 'Amara Okafor', ci: 'AO', ev: "Amara & Daniel's Wedding", msgs: [
      { who: 'them', t: 'Hi! We\u2019d love you to cater our wedding on 14 Aug — 120 guests at The Old Barn. Is the Feast Package available?', time: 'Mon 09:02' },
      { who: 'me', t: 'Hi Amara! Yes, we\u2019re free on the 14th and would love to. Any dietary requirements?', time: 'Mon 09:14' },
      { who: 'them', t: 'About 12 vegetarian, 4 vegan, 2 gluten-free.', time: 'Mon 10:02' },
    ] },
    'vt-james': { customer: 'James Whitfield', ci: 'JW', ev: 'Whitfield 25th Anniversary', msgs: [
      { who: 'them', t: 'Looking for canapés + drinks for 80 on 6 Sep. What would you suggest?', time: '5h ago' },
    ] },
    'vt-priya': { customer: 'Priya Sharma', ci: 'PS', ev: 'Diwali Office Party', msgs: [
      { who: 'me', t: 'Sent over a quote for the grazing tables — £1,320 for 60. Let me know!', time: 'Yesterday' },
    ] },
    'vt-leah': { customer: 'Leah Bennett', ci: 'LB', ev: "Leah's 30th", msgs: [
      { who: 'me', t: 'Quote sent: BBQ & Grill for 40 at £980. Happy to tweak the menu.', time: '2d ago' },
    ] },
    'vt-connor': { customer: 'Connor Daly', ci: 'CD', ev: 'Summer Garden Party', msgs: [
      { who: 'them', t: 'Hi — grazing tables for ~35 on 2 Aug. What\u2019s your availability?', time: '3d ago' },
    ] },
  };

  const vEarnings = [
    { m: 'Jan', v: 3200 }, { m: 'Feb', v: 4100 }, { m: 'Mar', v: 3800 },
    { m: 'Apr', v: 5200 }, { m: 'May', v: 4600 }, { m: 'Jun', v: 6480 },
  ];
  const vPayouts = [
    { date: '04 Jun 2026', amount: 1240, status: 'pending', ref: 'Tran Wedding deposit' },
    { date: '21 May 2026', amount: 3400, status: 'settled', ref: 'Q3 Town Hall · M&G' },
    { date: '08 May 2026', amount: 1180, status: 'settled', ref: "Rachel Green's 50th balance" },
    { date: '24 Apr 2026', amount: 920, status: 'settled', ref: 'Garden party · Daly' },
  ];
  // booked (B) / pencilled (P) days for Jun & Jul 2026
  const vCalendar = {
    'Jun 2026': { first: 1, days: 30, marks: { 21: 'B', 28: 'B', 14: 'P', 6: 'P' } }, // June 1 2026 = Monday
    'Jul 2026': { first: 3, days: 31, marks: { 12: 'B', 19: 'P', 25: 'P' } }, // July 1 2026 = Wednesday (offset 2 -> first col index 2)
  };

  return {
    customer: { user: { name: 'Amara Okafor', initials: 'AO' }, events: cEvents, bookings: cBookings, planning: cPlanning, threads: cThreads,
      money: { deposits: 1877 + 277 + 1500 + 100, total: cBookings.filter(b => b.status !== 'declined').reduce((a, b) => a + b.amount, 0) } },
    vendor: { biz: { name: 'The Roaming Kitchen', owner: 'Marcus Bell', initials: 'RK' }, requests: vRequests, services: vServices,
      threads: vThreads, earnings: vEarnings, payouts: vPayouts, calendar: vCalendar,
      analytics: { views: 1204, viewsTrend: 12, conversion: 38, response: '3h', repeat: 41 } },
  };
})();
