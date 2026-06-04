/* Mock data for the For Your Events — Browse Services redesign */
(function () {
  const IMG = 'public/assets/images/';

  const categories = [
    { id: 'venues',     name: 'Venues',               icon: 'fa-building',               img: IMG + 'category-venues.jpg' },
    { id: 'catering',   name: 'Catering & Drinks',    icon: 'fa-champagne-glasses',      img: IMG + 'category-catering-drinks.jpg' },
    { id: 'entertain',  name: 'Entertainment',        icon: 'fa-music',                  img: IMG + 'category-entertainment.jpg' },
    { id: 'photo',      name: 'Photography & Video',  icon: 'fa-camera',                 img: IMG + 'category-photography-video.jpg' },
    { id: 'flowers',    name: 'Flowers & Styling',    icon: 'fa-seedling',               img: IMG + 'category-flowers-styling.jpg' },
    { id: 'beauty',     name: 'Beauty & Personal Care', icon: 'fa-wand-magic-sparkles',  img: IMG + 'category-beauty-personal-care.jpg' },
    { id: 'transport',  name: 'Transport & Cars',     icon: 'fa-car-side',               img: IMG + 'category-transport-cars.jpg' },
    { id: 'planning',   name: 'Event Planning',       icon: 'fa-clipboard-check',        img: IMG + 'category-event-planning-support.jpg' },
  ];

  const S = (id, title, vendor, cat, price, unit, rating, reviews, loc) => ({
    id, title, vendor, cat,
    price, unit, rating, reviews, loc,
    img: categories.find(c => c.id === cat).img,
    catName: categories.find(c => c.id === cat).name,
  });

  const services = [
    S(1,  'The Orangery at Holland Park', 'Holland Park Estates', 'venues',    2400, 'from', 4.9, 128, 'Kensington, London'),
    S(2,  'Côte — Seasonal Canapé Service', 'Côte Fine Catering',  'catering',   38, 'pp',   4.8, 94,  'Greater London'),
    S(3,  'The Sundowners · Live Band',    'Sundowners Music',     'entertain', 1650, 'from', 5.0, 61,  'Surrey'),
    S(4,  'Margot & Hale Photography',     'Margot & Hale',        'photo',     1200, 'from', 4.9, 143, 'East London'),
    S(5,  'Wild Stem Floral Design',       'Wild Stem',            'flowers',    680, 'from', 4.7, 52,  'Hackney, London'),
    S(6,  'Bloom Bridal Hair & Makeup',    'Bloom Beauty Co.',     'beauty',     420, 'from', 4.8, 77,  'Central London'),
    S(7,  'Noble Classic Car Hire',        'Noble Motors',         'transport',  540, 'from', 4.9, 38,  'Kent'),
    S(8,  'Atelier Day-of Coordination',   'Atelier Events',       'planning',    95, 'hr',   5.0, 29,  'London & Home Counties'),
    S(9,  'The Glasshouse, Riverside',     'Riverside Venues',     'venues',    3100, 'from', 4.8, 88,  'Richmond, London'),
    S(10, 'Pearl & Vine Mobile Bar',       'Pearl & Vine',         'catering',   600, 'from', 4.7, 66,  'London'),
    S(11, 'Velvet & Brass Jazz Trio',      'Velvet & Brass',       'entertain',  890, 'from', 4.9, 45,  'London'),
    S(12, 'Still Frame Film Co.',          'Still Frame',          'photo',     1450, 'from', 4.8, 54,  'London'),
  ];

  // The user's events — what services get "added to"
  const events = [
    { id: 'wed',  title: "Sophie & Daniel's Wedding", date: '2026-09-12', dateLabel: 'Sat 12 Sep 2026', guests: 120, type: 'Wedding',        basket: [4, 5] },
    { id: 'corp', title: 'Acme Co. Summer Party',     date: '2026-07-18', dateLabel: 'Sat 18 Jul 2026', guests: 80,  type: 'Corporate',      basket: [] },
    { id: 'bday', title: "Mum's 60th Birthday",       date: '2026-10-04', dateLabel: 'Sun 4 Oct 2026',  guests: 35,  type: 'Birthday',       basket: [8] },
  ];

  window.PP = { categories, services, events };
})();
