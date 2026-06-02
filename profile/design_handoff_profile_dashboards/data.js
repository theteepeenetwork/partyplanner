// For Your Events — realistic sample data for the /profile mockups
window.FYE = {
  customer: {
    name: 'Amara Okafor',
    initials: 'AO',
    nextEvent: { title: "Amara & Daniel's Wedding", days: 73, date: '14 Aug 2026' },
    stats: { pending: 3, accepted: 4, awaiting: 2, confirmed: 6, declined: 1 },
    money: { deposits: 2310, remaining: 11940, total: 17650 },
    events: [
      { title: "Amara & Daniel's Wedding", date: '14 Aug 2026', loc: 'The Old Barn, Cotswolds', type: 'Wedding',
        guests: 120, booked: 6, max: 8, cost: 14250, days: 73 },
      { title: "Mum's 60th Birthday", date: '03 Oct 2026', loc: 'Home marquee, Bristol', type: 'Birthday',
        guests: 45, booked: 2, max: 6, cost: 3400, days: 123 },
      { title: 'Studio Summer Social', date: '25 Jul 2026', loc: 'Riverside Rooms, Bath', type: 'Corporate',
        guests: 60, booked: 1, max: 5, cost: 0, days: 53 },
    ],
    attention: [
      { tone: 'sage',  ic: 'fa-check-circle', t: 'Vendor accepted a booking', d: 'The Roaming Kitchen accepted your catering request', cta: 'Review' },
      { tone: 'gold',  ic: 'fa-credit-card', t: 'Deposit required', d: '2 bookings are accepted and awaiting your deposit', cta: 'Pay now' },
      { tone: 'slate', ic: 'fa-envelope', t: 'New messages', d: '3 unread messages from your suppliers', cta: 'Open' },
      { tone: 'terra', ic: 'fa-times-circle', t: 'A request was declined', d: 'Sax & The City is unavailable on 14 Aug — find an alternative', cta: 'Browse' },
    ],
    messages: [
      { who: 'The Roaming Kitchen', i: 'RK', snip: "We'd love to cater your day — quick question on dietary…", t: '2h', unread: true },
      { who: 'Bloom & Wild Florals', i: 'BW', snip: 'Mood board attached for the arch and tablescapes 🌿', t: '5h', unread: true },
      { who: 'Tom · Wandering Lens', i: 'TL', snip: 'Confirmed for the 14th! Sending the timeline shortly.', t: 'Tue', unread: false },
    ],
    planning: [
      { label: 'Venue', icon: 'fa-location-dot', booked: true },
      { label: 'Catering', icon: 'fa-utensils', booked: true },
      { label: 'Photography', icon: 'fa-camera', booked: true },
      { label: 'Flowers & styling', icon: 'fa-seedling', booked: true },
      { label: 'Entertainment', icon: 'fa-music', booked: false },
      { label: 'Cake & desserts', icon: 'fa-cake-candles', booked: false },
      { label: 'Transport', icon: 'fa-car', booked: true },
      { label: 'Hair & beauty', icon: 'fa-wand-magic-sparkles', booked: false },
    ],
    recs: [
      { name: 'Entertainment', sub: 'Bands & DJs near the Cotswolds', tone: 'slate', icon: 'fa-music' },
      { name: 'Cake & desserts', sub: 'Wedding cakes & dessert tables', tone: 'plum', icon: 'fa-cake-candles' },
      { name: 'Hair & beauty', sub: 'Bridal hair & makeup artists', tone: 'gold', icon: 'fa-wand-magic-sparkles' },
    ],
    favourites: [
      { n: 'Velvet & Vine Bar Co.', c: 'Mobile bar · Bath', tag: 'Catering' },
      { n: 'The String Quartet', c: 'Live music · Bristol', tag: 'Entertainment' },
      { n: 'Petal & Co.', c: 'Florist · Cirencester', tag: 'Flowers' },
      { n: 'Sweet Tier Bakery', c: 'Cakes · Stroud', tag: 'Desserts' },
    ],
    journey: [
      { st: 'Event created', sd: 'Date & venue set', state: 'done', ic: 'fa-flag' },
      { st: 'Services found', sd: '8 suppliers shortlisted', state: 'done', ic: 'fa-magnifying-glass' },
      { st: 'Requests sent', sd: '6 of 8 accepted', state: 'done', ic: 'fa-paper-plane' },
      { st: 'Deposits', sd: '2 left to pay', state: 'now', ic: 'fa-credit-card' },
      { st: 'Final details', sd: 'From 1 Aug', state: 'todo', ic: 'fa-clipboard-check' },
    ],
  },

  vendor: {
    name: 'The Roaming Kitchen',
    owner: 'Marcus Bell',
    initials: 'RK',
    stats: { pending: 5, upcoming: 8, earnings: 6480, services: 4, views: 1204, response: '3h' },
    requests: [
      { who: 'Amara Okafor', ev: "Amara & Daniel's Wedding", svc: 'Wedding Feast Package', date: '14 Aug', guests: 120, val: 4200, prio: 'hi', when: '2h ago' },
      { who: 'James Whitfield', ev: 'Whitfield 25th Anniversary', svc: 'Canapé & Drinks Reception', date: '06 Sep', guests: 80, val: 1850, prio: 'hi', when: '5h ago' },
      { who: 'Priya Sharma', ev: 'Diwali Office Party', svc: 'Grazing Tables', date: '08 Nov', guests: 60, val: 1320, prio: 'md', when: 'Yesterday' },
      { who: 'Leah Bennett', ev: "Leah's 30th", svc: 'BBQ & Grill Station', date: '19 Jul', guests: 40, val: 980, prio: 'md', when: '2d ago' },
      { who: 'Connor Daly', ev: 'Summer Garden Party', svc: 'Grazing Tables', date: '02 Aug', guests: 35, val: 770, prio: 'lo', when: '3d ago' },
    ],
    upcoming: [
      { who: 'Sophie Tran', ev: 'Tran Wedding', date: '21 Jun', loc: 'Tortworth Court', m: 'Jun', d: '21' },
      { who: 'Office of M&G', ev: 'Q3 Town Hall', date: '28 Jun', loc: 'Bristol Harbour', m: 'Jun', d: '28' },
      { who: 'Dan Okafor', ev: 'Engagement Drinks', date: '05 Jul', loc: 'The Glasshouse', m: 'Jul', d: '05' },
      { who: 'Rachel Green', ev: "Green's 50th", date: '12 Jul', loc: 'Home, Clifton', m: 'Jul', d: '12' },
    ],
    services: [
      { title: 'Wedding Feast Package', desc: true, img: true, price: true, policy: true, bookings: 12 },
      { title: 'Canapé & Drinks Reception', desc: true, img: true, price: true, policy: false, bookings: 7 },
      { title: 'BBQ & Grill Station', desc: true, img: false, price: true, policy: false, bookings: 4 },
      { title: 'Grazing Tables', desc: true, img: true, price: true, policy: true, bookings: 9 },
    ],
    attention: [
      { tone: 'gold',  ic: 'fa-clock', t: '5 requests awaiting response', d: '2 are flagged urgent — events within 8 weeks', cta: 'Review' },
      { tone: 'slate', ic: 'fa-envelope', t: '3 unread customer messages', d: 'Fast replies win more bookings', cta: 'Open' },
      { tone: 'terra', ic: 'fa-image', t: '1 service missing photos', d: 'BBQ & Grill Station has no gallery images', cta: 'Fix' },
    ],
    activity: [
      { dot: 'gold',  t: 'New request from Amara Okafor for Wedding Feast Package', when: '2h ago' },
      { dot: 'sage',  t: 'Booking confirmed: Sophie Tran · Tran Wedding', when: '6h ago' },
      { dot: 'slate', t: 'Priya Sharma sent a message about Grazing Tables', when: 'Yesterday' },
      { dot: 'terra', t: 'Payout of £1,240 settled to your account', when: '2d ago' },
    ],
    earnings: [3200, 4100, 3800, 5200, 4600, 6480], // last 6 months
    payouts: { settled: 5240, pending: 1240, next: '04 Jun' },
  },
};
