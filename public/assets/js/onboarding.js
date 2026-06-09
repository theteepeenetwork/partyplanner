/* ============================================================
   Partysmith · Vendor onboarding — "List your service"
   Adaptive, supplier-type-aware listing builder.

   Vanilla JS port of the Claude Design prototype (no build step,
   no framework — matches the project's plain-JS convention).
   The keystone: the supplier type chosen in step 1 reshapes the
   pricing models, requirement toggles and capacity/footprint
   blocks shown downstream.
   ============================================================ */
(function () {
  "use strict";

  var CFG = window.PS_ONBOARD || {};
  var ASSETS = CFG.assetsBase || "/assets/images/";
  var STORE_KEY = "partysmith_onboarding_v1";

  /* ---------------------------------------------------------
     Adaptive data model
     --------------------------------------------------------- */
  var REQ_CATALOG = {
    power:     { icon: "fa-plug",           name: "Mains power",        sub: "You need access to electricity on site" },
    water:     { icon: "fa-faucet",         name: "Water supply",       sub: "You need a tap or water connection" },
    vehicle:   { icon: "fa-truck",          name: "Vehicle access",     sub: "You must drive close to the setup spot" },
    ground:    { icon: "fa-ruler-combined", name: "Level, firm ground", sub: "Hard standing or level grass to set up safely" },
    shelter:   { icon: "fa-umbrella",       name: "Cover from weather", sub: "Need shelter or a wet-weather backup" },
    own_equip: { icon: "fa-toolbox",        name: "I bring everything", sub: "Fully self-sufficient — no venue kit needed" },
    parking:   { icon: "fa-square-parking", name: "Parking / unloading",sub: "Space to park and unload near the venue" },
    licence:   { icon: "fa-file-shield",    name: "Licensing / permits",sub: "Alcohol, SIA or safety certificates apply" }
  };

  var PRICING_MODELS = {
    packages: { icon: "fa-layer-group",     name: "Set packages",   desc: "Offer a few tiers and let customers pick (Bronze · Silver · Gold).", eg: "e.g. Essential £650 · Classic £950 · Premium £1,400" },
    guest:    { icon: "fa-users",           name: "Per guest",      desc: "Price scales with the headcount. Great when cost tracks numbers.",  eg: "e.g. £24 per guest, min 40 guests" },
    duration: { icon: "fa-clock",           name: "By time",        desc: "Charge an hourly or daily rate for how long you're booked.",        eg: "e.g. £150/hour · £900/day" },
    quantity: { icon: "fa-cubes-stacked",   name: "Per item",       desc: "Price per unit ordered — chairs, favours, cakes, covers.",          eg: "e.g. £3.50 per chair cover, min 50" },
    fixed:    { icon: "fa-tag",             name: "One flat price", desc: "A single set fee, the same for every booking.",                     eg: "e.g. £450 all-in" },
    pitch:    { icon: "fa-store",           name: "Event pitch fee",desc: "For trading at public events — a pitch fee, often by expected attendance.", eg: "e.g. £180 pitch · or 12% of takings" },
    quote:    { icon: "fa-pen-ruler",       name: "Request a quote",desc: "Price on request. For bespoke jobs you scope and quote individually.", eg: "Customers send their brief — you reply with a tailored quote" }
  };

  var SUPPLIER_GROUPS = [
    { id: "ent",    label: "Entertainment & music" },
    { id: "food",   label: "Food & drink" },
    { id: "photo",  label: "Photo & video" },
    { id: "style",  label: "Styling & decor" },
    { id: "struct", label: "Venues & structures" },
    { id: "trans",  label: "Transport" },
    { id: "plan",   label: "Planning & production" },
    { id: "exp",    label: "Activities & experiences" }
  ];

  var SUPPLIER_TYPES = [
    { id:"dj", group:"ent", icon:"fa-record-vinyl", name:"DJ", img:"entertainment.jpg",
      rec:"packages", pricing:["packages","duration","fixed","quote"],
      reqs:["power","own_equip","parking"], capacity:false, footprint:false, setup:true, availability:true,
      tags:["DJ","party","wedding","disco"], asks:"power on site and your set-up time" },
    { id:"band", group:"ent", icon:"fa-guitar", name:"Live band", img:"entertainment.jpg",
      rec:"packages", pricing:["packages","duration","quote"],
      reqs:["power","shelter","parking","own_equip"], capacity:false, footprint:true, setup:true, availability:true,
      tags:["live music","band","wedding"], asks:"power, stage space and your set-up time" },
    { id:"magician", group:"ent", icon:"fa-wand-magic-sparkles", name:"Magician / entertainer", img:"entertainment.jpg",
      rec:"fixed", pricing:["fixed","packages","duration"],
      reqs:["own_equip"], capacity:false, footprint:false, setup:false, availability:true,
      tags:["magician","entertainer","kids party"], asks:"very little — you're easy to host" },

    { id:"caterer", group:"food", icon:"fa-utensils", name:"Caterer", img:"catering.jpg",
      rec:"guest", pricing:["guest","packages","quote"],
      reqs:["power","water","vehicle","parking","licence"], capacity:true, footprint:false, setup:true, availability:true,
      tags:["catering","food","buffet","wedding"], asks:"power, water, kitchen access and guest numbers" },
    { id:"foodtruck", group:"food", icon:"fa-truck-fast", name:"Food truck / van", img:"catering.jpg",
      rec:"quantity", pricing:["quantity","fixed","pitch","quote"],
      reqs:["power","water","vehicle","ground","parking","licence"], capacity:false, footprint:true, setup:true, availability:true,
      tags:["street food","food truck","festival"], asks:"power, water, vehicle access and your pitch footprint" },
    { id:"bar", group:"food", icon:"fa-martini-glass", name:"Mobile bar", img:"catering.jpg",
      rec:"packages", pricing:["packages","guest","fixed","quote"],
      reqs:["power","water","vehicle","licence","ground"], capacity:true, footprint:true, setup:true, availability:true,
      tags:["mobile bar","cocktails","drinks"], asks:"power, water, a drinks licence and your footprint" },

    { id:"photographer", group:"photo", icon:"fa-camera", name:"Photographer", img:"photography.jpg",
      rec:"packages", pricing:["packages","duration","quote"],
      reqs:["own_equip"], capacity:false, footprint:false, setup:false, availability:true,
      tags:["photography","wedding","portrait"], asks:"almost nothing — just your packages and dates" },
    { id:"videographer", group:"photo", icon:"fa-video", name:"Videographer", img:"photography.jpg",
      rec:"packages", pricing:["packages","duration","quote"],
      reqs:["own_equip","power"], capacity:false, footprint:false, setup:false, availability:true,
      tags:["videography","film","wedding"], asks:"your packages, kit and dates" },
    { id:"booth", group:"photo", icon:"fa-camera-retro", name:"Photo booth", img:"photography.jpg",
      rec:"duration", pricing:["duration","fixed","packages"],
      reqs:["power","own_equip","parking"], capacity:false, footprint:true, setup:true, availability:true,
      tags:["photo booth","party","props"], asks:"power, the booth footprint and set-up time" },

    { id:"florist", group:"style", icon:"fa-fan", name:"Florist / stylist", img:"flowers.jpg",
      rec:"packages", pricing:["packages","quantity","quote"],
      reqs:["vehicle","parking"], capacity:false, footprint:false, setup:true, availability:true,
      tags:["florist","flowers","styling","wedding"], asks:"venue access and your delivery / set-up window" },
    { id:"hire", group:"style", icon:"fa-chair", name:"Furniture / decor hire", img:"flowers.jpg",
      rec:"quantity", pricing:["quantity","packages","fixed"],
      reqs:["vehicle","parking","ground"], capacity:false, footprint:false, setup:true, availability:true,
      tags:["hire","furniture","decor","chair covers"], asks:"delivery access, collection windows and min order" },
    { id:"dancefloor", group:"style", icon:"fa-border-all", name:"LED dancefloor / lighting", img:"entertainment.jpg",
      rec:"packages", pricing:["packages","quantity","quote"],
      reqs:["power","vehicle","ground","own_equip"], capacity:false, footprint:true, setup:true, availability:true,
      tags:["dancefloor","lighting","LED"], asks:"power load, footprint and install time" },

    { id:"marquee", group:"struct", icon:"fa-tent", name:"Marquee company", img:"venues.jpg",
      rec:"packages", pricing:["packages","quote"],
      reqs:["vehicle","ground","power","parking"], capacity:true, footprint:true, setup:true, availability:true,
      tags:["marquee","tipi","structure"], asks:"site access, ground type, capacity and build days" },

    { id:"transport", group:"trans", icon:"fa-car-side", name:"Transport / cars", img:"transport.jpg",
      rec:"fixed", pricing:["fixed","duration","quote"],
      reqs:["parking"], capacity:true, footprint:false, setup:false, availability:true,
      tags:["transport","wedding car","chauffeur"], asks:"vehicle type, capacity and travel distance" },

    { id:"planner", group:"plan", icon:"fa-clipboard-check", name:"Event / wedding planner", img:"hero.jpg",
      rec:"quote", pricing:["quote","packages"],
      reqs:[], capacity:false, footprint:false, setup:false, availability:true,
      tags:["wedding planner","event planning","coordination"], asks:"how you scope projects — we'll set you up for quotes" },
    { id:"av", group:"plan", icon:"fa-sliders", name:"AV / production company", img:"hero.jpg",
      rec:"quote", pricing:["quote","duration"],
      reqs:["power","vehicle","parking","own_equip"], capacity:false, footprint:true, setup:true, availability:true,
      tags:["AV","production","staging","sound"], asks:"power, rig space, crew and your quoting process" },

    { id:"inflatable", group:"exp", icon:"fa-children", name:"Inflatables / fairground", img:"entertainment.jpg",
      rec:"fixed", pricing:["fixed","duration","quote"],
      reqs:["power","vehicle","ground","shelter","parking","licence"], capacity:false, footprint:true, setup:true, availability:true,
      tags:["inflatable","bouncy castle","fairground"], asks:"power, vehicle access, footprint and safety checks" },
    { id:"workshop", group:"exp", icon:"fa-palette", name:"Workshop / team-building", img:"hero.jpg",
      rec:"duration", pricing:["duration","packages","guest","quote"],
      reqs:["own_equip","water"], capacity:true, footprint:false, setup:true, availability:true,
      tags:["workshop","team building","craft"], asks:"group size, the space you need and session length" },
    { id:"security", group:"exp", icon:"fa-user-shield", name:"Security / event staff", img:"hero.jpg",
      rec:"duration", pricing:["duration","quote"],
      reqs:["licence","parking"], capacity:true, footprint:false, setup:false, availability:true,
      tags:["security","SIA","event staff","stewards"], asks:"staff numbers, SIA licensing and shift hours" }
  ];

  // Map the prototype's sample filenames to the repo's real category images.
  var SAMPLE_IMG = {
    "entertainment.jpg": "category-entertainment.jpg",
    "catering.jpg":      "category-catering-drinks.jpg",
    "photography.jpg":   "category-photography-video.jpg",
    "flowers.jpg":       "category-flowers-styling.jpg",
    "venues.jpg":        "category-venues.jpg",
    "transport.jpg":     "category-transport-cars.jpg",
    "hero.jpg":          "category-event-planning-support.jpg",
    "beauty.jpg":        "category-beauty-personal-care.jpg"
  };
  function sampleImg(name) { return ASSETS + (SAMPLE_IMG[name] || name); }

  function getType(id) { for (var i = 0; i < SUPPLIER_TYPES.length; i++) if (SUPPLIER_TYPES[i].id === id) return SUPPLIER_TYPES[i]; return null; }
  function getGroupLabel(id) { for (var i = 0; i < SUPPLIER_GROUPS.length; i++) if (SUPPLIER_GROUPS[i].id === id) return SUPPLIER_GROUPS[i].label; return ""; }

  /* ---------------------------------------------------------
     State
     --------------------------------------------------------- */
  var INITIAL = {
    typeId: null, category: "", title: "", shortDesc: "", fullDesc: "", img: null, tags: [],
    pricing: null, startPrice: "", tiers: null, minGuests: 0, durationUnit: "hour", minDuration: 1,
    unitLabel: "", minQty: 1, pctTakings: "", quoteBrief: "", responseTime: "24h",
    reqs: [], indoorOutdoor: "both", footW: "", footD: "", capMin: 0, capMax: 0, setupTime: "", reqNotes: "",
    location: "", nationwide: false, radius: 30, freeRadius: 10, travelFee: "", leadTime: "", blocked: []
  };

  function loadData() {
    try { var s = localStorage.getItem(STORE_KEY); if (s) return assign(clone(INITIAL), JSON.parse(s)); } catch (e) {}
    return clone(INITIAL);
  }

  var state = { data: loadData(), step: 0, maxStep: 0, published: false, publishedInfo: null, calOffset: 0, busy: false };

  var STEPS = [
    { id: "type",   kicker: "Step 1", name: "Your craft" },
    { id: "basics", kicker: "Step 2", name: "The basics" },
    { id: "price",  kicker: "Step 3", name: "How you price" },
    { id: "reqs",   kicker: "Step 4", name: "On the day" },
    { id: "cover",  kicker: "Step 5", name: "Where & when" },
    { id: "review", kicker: "Step 6", name: "Review & publish" }
  ];

  function validFor(id, d) {
    switch (id) {
      case "type":   return !!d.typeId;
      case "basics": return !!(d.title && d.shortDesc);
      case "price":  return !!(d.pricing && (d.pricing === "quote" || d.startPrice));
      case "reqs":   return true;
      case "cover":  return !!d.location;
      case "review": return false;
      default:       return false;
    }
  }

  /* ---------------------------------------------------------
     Utilities
     --------------------------------------------------------- */
  function clone(o) { return JSON.parse(JSON.stringify(o)); }
  function assign(t, s) { for (var k in s) if (Object.prototype.hasOwnProperty.call(s, k)) t[k] = s[k]; return t; }
  function esc(v) {
    return String(v == null ? "" : v)
      .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;").replace(/'/g, "&#39;");
  }
  function persist() { try { localStorage.setItem(STORE_KEY, JSON.stringify(state.data)); } catch (e) {} }

  // set(patch[, render]) — render defaults true; pass false for live-typed fields.
  function set(patch, render) {
    assign(state.data, patch);
    persist();
    if (render === false) { updateChrome(); }
    else { renderStep(); updateChrome(); }
  }

  function go(i) {
    state.step = i;
    if (i > state.maxStep) state.maxStep = i;
    window.scrollTo({ top: 0, behavior: "auto" });
    renderStep();
    updateChrome();
  }

  /* ---------------------------------------------------------
     Derived
     --------------------------------------------------------- */
  function doneFlags() {
    return [
      validFor("type", state.data),
      validFor("basics", state.data),
      validFor("price", state.data),
      state.maxStep >= 3,
      validFor("cover", state.data)
    ];
  }
  function completeness() {
    var f = doneFlags(), n = 0;
    for (var i = 0; i < f.length; i++) if (f[i]) n++;
    return Math.round(n / 5 * 100);
  }

  /* ---------------------------------------------------------
     Shared markup helpers
     --------------------------------------------------------- */
  function fieldLabel(label, required, hint) {
    if (!label && !hint) return "";
    var h = hint ? '<span class="hint" style="display:block;margin-top:2px">' + esc(hint) + '</span>' : "";
    if (!label) return h ? '<label>' + h + '</label>' : "";
    return '<label>' + esc(label) + (required ? ' <span class="req">*</span>' : "") + h + '</label>';
  }
  function sectionLabel(t) { return '<p class="section-label">' + esc(t) + '</p>'; }
  function adaptBanner(html) {
    return '<div class="adapt-banner fade-in"><span class="ab-icon"><i class="fas fa-wand-magic-sparkles"></i></span><span class="ab-text">' + html + '</span></div>';
  }
  function selectHTML(field, value, opts) {
    var o = "";
    for (var i = 0; i < opts.length; i++) {
      o += '<option value="' + esc(opts[i].v) + '"' + (String(value) === String(opts[i].v) ? " selected" : "") + '>' + esc(opts[i].l) + '</option>';
    }
    return '<div class="select-wrap"><select class="select" data-field="' + field + '">' + o + '</select></div>';
  }
  function segmentedHTML(field, value, opts) {
    var b = "";
    for (var i = 0; i < opts.length; i++) {
      b += '<button type="button" class="' + (String(value) === String(opts[i].v) ? "is-on" : "") + '" data-action="segment" data-field="' + field + '" data-value="' + esc(opts[i].v) + '">' + esc(opts[i].l) + '</button>';
    }
    return '<div class="segmented">' + b + '</div>';
  }
  function stepperHTML(field, value, opts) {
    opts = opts || {};
    var min = opts.min != null ? opts.min : 0, max = opts.max != null ? opts.max : 9999, step = opts.step || 1;
    var suffix = opts.suffix ? '<span style="font-size:14px;color:var(--ink-soft);font-weight:600">' + esc(opts.suffix) + '</span>' : "";
    return '<div style="display:inline-flex;align-items:center;gap:10px"><div class="stepper">' +
      '<button type="button" data-action="step-dec" data-field="' + field + '" data-min="' + min + '" data-step="' + step + '">−</button>' +
      '<input data-stepper="' + field + '" data-min="' + min + '" data-max="' + max + '" value="' + esc(value) + '">' +
      '<button type="button" data-action="step-inc" data-field="' + field + '" data-max="' + max + '" data-step="' + step + '">+</button>' +
      '</div>' + suffix + '</div>';
  }
  function moneyHTML(field, value, placeholder, suffix) {
    var suf = suffix ? '<span style="font-size:14px;color:var(--ink-soft);font-weight:600">' + esc(suffix) + '</span>' : "";
    return '<div style="display:flex;align-items:center;gap:8px"><span style="font-weight:700">£</span>' +
      '<input class="input" style="max-width:130px" inputmode="decimal" data-field="' + field + '" data-sanitize="money" value="' + esc(value || "") + '" placeholder="' + esc(placeholder || "") + '">' + suf + '</div>';
  }

  /* ---------------------------------------------------------
     Step 1 · What do you offer (keystone)
     --------------------------------------------------------- */
  function renderType() {
    var q = (state.typeQuery || "").trim().toLowerCase();
    var filtered = SUPPLIER_TYPES.filter(function (t) {
      if (!q) return true;
      if (t.name.toLowerCase().indexOf(q) > -1) return true;
      if (getGroupLabel(t.group).toLowerCase().indexOf(q) > -1) return true;
      for (var i = 0; i < t.tags.length; i++) if (t.tags[i].toLowerCase().indexOf(q) > -1) return true;
      return false;
    });
    var groups = SUPPLIER_GROUPS.filter(function (g) {
      return filtered.some(function (t) { return t.group === g.id; });
    });

    var html = '<div class="fade-in">' +
      '<p class="step-eyebrow">Step 1 · Your craft</p>' +
      '<h1 class="step-title">What do you offer?</h1>' +
      '<p class="step-lead">Pick the closest match. This is the only big decision — we use it to shape the rest of your listing so you only answer what actually matters for your trade.</p>' +
      '<div class="type-search"><i class="fas fa-magnifying-glass"></i>' +
      '<input class="input" id="typeSearch" placeholder="Search: caterer, photo booth, marquee, DJ…" value="' + esc(state.typeQuery || "") + '"></div>';

    groups.forEach(function (g) {
      html += '<div class="type-group"><p class="type-group-h">' + esc(g.label) + '</p><div class="chip-wrap">';
      filtered.filter(function (t) { return t.group === g.id; }).forEach(function (t) {
        html += '<button type="button" class="type-chip' + (state.data.typeId === t.id ? " is-selected" : "") + '" data-action="select-type" data-type="' + t.id + '"><i class="fas ' + t.icon + '"></i> ' + esc(t.name) + '</button>';
      });
      html += '</div></div>';
    });
    if (!filtered.length) {
      html += '<p style="color:var(--ink-soft)">No match — try a broader word, or pick the closest trade. You can fine-tune your exact category later.</p>';
    }
    html += '</div>';
    return html;
  }

  function selectType(t) {
    var same = state.data.typeId === t.id;
    set({
      typeId: t.id,
      category: t.name,
      pricing: same ? state.data.pricing : t.rec,
      tags: same ? state.data.tags : t.tags.slice(),
      reqs: same ? state.data.reqs : []
    });
  }

  /* ---------------------------------------------------------
     Step 2 · The basics
     --------------------------------------------------------- */
  var TITLE_EG = { dj: "Sunset Sounds — Wedding & Party DJ", caterer: "The Long Table — Feast-Style Catering", foodtruck: "Smoke & Bun — Wood-Fired Burgers", photographer: "Coastal Wedding Photography", marquee: "Meadow & Pole — Sailcloth Tipis", planner: "Atelier Vows — Full Wedding Planning", booth: "Flashhouse Photo Booth", bar: "The Copper Still — Mobile Cocktail Bar" };
  function titleEg(id) { return TITLE_EG[id] || "Your standout service name"; }

  function renderBasics(type) {
    var d = state.data;
    var html = '<div class="fade-in">' +
      '<p class="step-eyebrow">Step 2 · The basics</p>' +
      '<h1 class="step-title">Introduce your service</h1>' +
      '<p class="step-lead">This is what customers see first when browsing. Keep it warm and specific.</p>';
    if (type) html += adaptBanner('Writing for a <b>' + esc(type.name.toLowerCase()) + '</b> listing — we\'ve pre-filled a few tags to get you going.');

    html += '<div class="field">' + fieldLabel("Listing title", true, "Lead with what you do and your style.") +
      '<input class="input" maxlength="80" data-field="title" value="' + esc(d.title) + '" placeholder="' + esc(type ? "e.g. " + titleEg(type.id) : "e.g. Coastal Wedding Photography") + '"></div>';

    html += '<div class="field">' + fieldLabel("Short description", true, "One line shown on your listing card.") +
      '<input class="input" maxlength="140" data-field="shortDesc" value="' + esc(d.shortDesc) + '" placeholder="A single sentence that sums up your offer">' +
      '<div class="char-count" data-count="shortDesc">' + (d.shortDesc || "").length + '/140</div></div>';

    html += '<div class="field">' + fieldLabel("Full description", false, "The detail customers read on your page — what's included, your approach, what makes you you.") +
      '<textarea class="textarea" rows="5" data-field="fullDesc" placeholder="Tell the story of your service…">' + esc(d.fullDesc) + '</textarea></div>';

    html += sectionLabel("Photos");
    html += '<div class="subcard" style="background:var(--white);border:1.5px dashed var(--line);text-align:center">';
    if (d.img) {
      html += '<div><img src="' + esc(d.img) + '" alt="" style="width:100%;max-height:200px;object-fit:cover;border-radius:11px;margin-bottom:12px">' +
        '<button type="button" class="btn btn-ghost btn-sm" data-action="remove-img"><i class="fas fa-rotate-left"></i> Remove</button></div>';
    } else {
      html += '<div style="padding:18px 0">' +
        '<div style="width:48px;height:48px;border-radius:12px;background:var(--paper-2);color:var(--accent);display:flex;align-items:center;justify-content:center;font-size:20px;margin:0 auto 12px"><i class="fas fa-cloud-arrow-up"></i></div>' +
        '<div style="font-weight:700;font-size:14.5px;margin-bottom:4px">Drag photos here, or browse</div>' +
        '<div style="font-size:12.5px;color:var(--ink-faint);margin-bottom:14px">JPG or PNG · up to 5MB each · first photo becomes your cover</div>' +
        (type ? '<button type="button" class="btn btn-ghost btn-sm" data-action="use-sample"><i class="fas fa-image"></i> Use a sample photo</button>' : "") +
        '</div>';
    }
    html += '</div>';

    html += sectionLabel("Tags");
    html += '<div class="field">' + fieldLabel(null, false, "Help the right customers find you. 3–6 works well.") + tagInputHTML(d.tags, type ? type.tags : []) + '</div>';
    html += '</div>';
    return html;
  }

  function tagInputHTML(tags, suggestions) {
    var t = "";
    tags.forEach(function (tag) {
      t += '<span class="tag">' + esc(tag) + '<button type="button" data-action="tag-remove" data-tag="' + esc(tag) + '" aria-label="Remove ' + esc(tag) + '">×</button></span>';
    });
    var box = '<div class="tagbox" data-action="tag-focus">' + t +
      '<input data-tag-input placeholder="' + (tags.length ? "" : "Type a tag and press Enter") + '"></div>';
    var unused = suggestions.filter(function (s) { return tags.indexOf(s) === -1; });
    var sug = "";
    if (unused.length) {
      sug = '<div style="display:flex;flex-wrap:wrap;gap:7px;margin-top:9px"><span style="font-size:12px;color:var(--ink-faint);font-weight:600;align-self:center">Suggested:</span>';
      unused.forEach(function (s) { sug += '<button type="button" class="tag-suggest" data-action="tag-add" data-tag="' + esc(s) + '"><i class="fas fa-plus" style="font-size:9px"></i> ' + esc(s) + '</button>'; });
      sug += '</div>';
    }
    return '<div>' + box + sug + '</div>';
  }

  /* ---------------------------------------------------------
     Step 3 · Pricing
     --------------------------------------------------------- */
  function renderPricing(type) {
    var d = state.data;
    var models = type ? type.pricing : Object.keys(PRICING_MODELS);
    var html = '<div class="fade-in">' +
      '<p class="step-eyebrow">Step 3 · How you price</p>' +
      '<h1 class="step-title">Choose how you charge</h1>' +
      '<p class="step-lead">Pick the one model that fits best. Customers get instant, honest pricing — and if your work is bespoke, "Request a quote" is a first-class option, no fake numbers needed.</p>';
    if (type) html += adaptBanner('Most <b>' + esc(type.name.toLowerCase()) + 's</b> use <b>' + esc(PRICING_MODELS[type.rec].name) + '</b> — it\'s marked below, but choose whatever fits you.');

    html += '<div class="choice-grid">';
    models.forEach(function (m) {
      var p = PRICING_MODELS[m];
      var sel = d.pricing === m;
      var rec = (type && type.rec === m);
      html += '<button type="button" class="choice' + (sel ? " is-selected" : "") + '" data-action="select-pricing" data-model="' + m + '">' +
        '<span class="choice-ic"><i class="fas ' + p.icon + '"></i></span>' +
        '<span class="choice-body">' +
        (rec ? '<span class="badge-rec"><i class="fas fa-star" style="font-size:9px"></i> Recommended</span>' : "") +
        '<div class="choice-name">' + esc(p.name) + '</div>' +
        '<div class="choice-desc">' + esc(p.desc) + '</div>' +
        (p.eg ? '<div class="choice-eg">' + esc(p.eg) + '</div>' : "") +
        '</span><span class="choice-check"><i class="fas fa-check"></i></span></button>';
    });
    html += '</div>';

    if (d.pricing) html += '<div class="divider"></div>' + pricingConfig(d);
    html += '</div>';
    return html;
  }

  function simpleConfig(title, icon, inner) {
    return '<div class="fade-in">' + sectionLabel(title) +
      '<div class="subcard"><p class="subcard-h"><i class="fas ' + icon + '" style="color:var(--accent)"></i> ' + esc(title) + '</p>' + inner + '</div></div>';
  }

  function pricingConfig(d) {
    var m = d.pricing;
    if (m === "quote") {
      return '<div class="fade-in">' + sectionLabel("Quote settings") + '<div class="subcard">' +
        '<p class="subcard-h"><i class="fas fa-pen-ruler" style="color:var(--accent)"></i> You\'ll quote each job individually</p>' +
        '<p class="subcard-sub">Customers send their brief and you reply with a tailored price. Set expectations so they know what to expect.</p>' +
        '<div class="field">' + fieldLabel("What you need from customers to quote", false, "Shown on your enquiry form.") +
        '<textarea class="textarea" rows="3" data-field="quoteBrief" placeholder="e.g. Event date, venue, guest numbers, run-of-day and your budget range.">' + esc(d.quoteBrief) + '</textarea></div>' +
        '<div class="field">' + fieldLabel("Typical response time", false, null) +
        segmentedHTML("responseTime", d.responseTime || "24h", [{ v: "few-hours", l: "A few hours" }, { v: "24h", l: "Within 24h" }, { v: "48h", l: "1–2 days" }]) + '</div>' +
        '<div class="field">' + fieldLabel("Indicative starting point (optional)", false, "A 'from' figure builds trust — leave blank to show 'Price on request'.") +
        '<div style="display:flex;align-items:center;gap:8px"><span style="font-weight:700">£</span>' +
        '<input class="input" style="max-width:160px" inputmode="numeric" data-field="startPrice" data-sanitize="moneycomma" value="' + esc(d.startPrice) + '" placeholder="2,500"></div></div>' +
        '</div></div>';
    }
    if (m === "packages" || m === "fixed") {
      if (m === "fixed") {
        return simpleConfig("Flat-rate pricing", "fa-tag",
          '<div class="field">' + fieldLabel("Your price", false, "The same total for every booking.") + moneyHTML("startPrice", d.startPrice, "450") + '</div>');
      }
      return tierConfig(d);
    }
    if (m === "guest") {
      return simpleConfig("Per-guest pricing", "fa-users",
        '<div class="field-row two">' +
        '<div class="field">' + fieldLabel("Price per guest", false, null) + moneyHTML("startPrice", d.startPrice, "24", "/guest") + '</div>' +
        '<div class="field">' + fieldLabel("Minimum guests", false, null) + stepperHTML("minGuests", d.minGuests || 0, { step: 5, suffix: "guests" }) + '</div></div>');
    }
    if (m === "duration") {
      var u = d.durationUnit || "hour";
      return simpleConfig("Time-based pricing", "fa-clock",
        '<div class="field">' + fieldLabel("Charge by", false, null) + segmentedHTML("durationUnit", u, [{ v: "hour", l: "Per hour" }, { v: "day", l: "Per day" }]) + '</div>' +
        '<div class="field-row two">' +
        '<div class="field">' + fieldLabel("Rate per " + u, false, null) + moneyHTML("startPrice", d.startPrice, "150", "/" + u) + '</div>' +
        '<div class="field">' + fieldLabel("Minimum booking", false, null) + stepperHTML("minDuration", d.minDuration || 1, { min: 1, suffix: u + "s" }) + '</div></div>');
    }
    if (m === "quantity") {
      return simpleConfig("Per-item pricing", "fa-cubes-stacked",
        '<div class="field-row three">' +
        '<div class="field">' + fieldLabel("Price per item", false, null) + moneyHTML("startPrice", d.startPrice, "3.50") + '</div>' +
        '<div class="field">' + fieldLabel("Unit name", false, null) + '<input class="input" data-field="unitLabel" value="' + esc(d.unitLabel) + '" placeholder="e.g. chair cover"></div>' +
        '<div class="field">' + fieldLabel("Minimum order", false, null) + stepperHTML("minQty", d.minQty || 1, { min: 1, step: 5 }) + '</div></div>');
    }
    if (m === "pitch") {
      return simpleConfig("Event pitch pricing", "fa-store",
        '<div class="field-row two">' +
        '<div class="field">' + fieldLabel("Pitch fee", false, null) + moneyHTML("startPrice", d.startPrice, "180", "pitch") + '</div>' +
        '<div class="field">' + fieldLabel("Or % of takings (optional)", false, null) + '<input class="input" data-field="pctTakings" data-sanitize="digits" value="' + esc(d.pctTakings) + '" placeholder="12"></div></div>');
    }
    return "";
  }

  function tiers() {
    var t = state.data.tiers;
    if (t && t.length) return t;
    return [{ name: "Essential", price: "" }, { name: "Classic", price: "" }, { name: "Premium", price: "" }];
  }
  function minPrice(list) {
    var nums = [];
    list.forEach(function (t) { var n = parseFloat(String(t.price).replace(/,/g, "")); if (!isNaN(n)) nums.push(n); });
    return nums.length ? String(Math.min.apply(null, nums)) : "";
  }
  function tierConfig(d) {
    var list = tiers();
    var rows = "";
    list.forEach(function (t, i) {
      rows += '<div class="tier-row">' +
        '<div class="field">' + (i === 0 ? fieldLabel("Package name", false, null) : "") + '<input class="input" data-tier="' + i + '" data-tier-key="name" value="' + esc(t.name) + '" placeholder="e.g. Classic"></div>' +
        '<div class="field">' + (i === 0 ? fieldLabel("Price", false, null) : "") + '<div style="display:flex;align-items:center;gap:8px"><span style="font-weight:700">£</span><input class="input" style="max-width:130px" inputmode="decimal" data-tier="' + i + '" data-tier-key="price" data-sanitize="money" value="' + esc(t.price) + '" placeholder="950"></div></div>' +
        '<button type="button" class="btn-del" data-action="tier-del" data-index="' + i + '"' + (list.length <= 1 ? " disabled" : "") + '><i class="fas fa-trash-can"></i></button>' +
        '</div>';
    });
    return '<div class="fade-in">' + sectionLabel("Your packages") + '<div class="subcard">' +
      '<p class="subcard-sub" style="margin-top:0">Add the tiers customers can choose from. Your lowest price shows as the "from" figure.</p>' +
      rows + '<button type="button" class="add-row" data-action="tier-add"><i class="fas fa-plus"></i> Add a package</button></div></div>';
  }

  /* ---------------------------------------------------------
     Step 4 · Requirements & logistics (adaptive)
     --------------------------------------------------------- */
  function renderReqs(type) {
    var d = state.data;
    var reqKeys = type ? type.reqs : [];
    var showCapacity = type && type.capacity, showFootprint = type && type.footprint, showSetup = type && type.setup;
    var nothing = reqKeys.length === 0 && !showCapacity && !showFootprint;

    var html = '<div class="fade-in">' +
      '<p class="step-eyebrow">Step 4 · On the day</p>' +
      '<h1 class="step-title">What do you need on site?</h1>' +
      '<p class="step-lead">This is what most marketplaces miss — and what causes day-of disasters. Tell customers exactly what you need so only suitable venues book you.</p>';

    if (type) {
      html += adaptBanner('Because you\'re a <b>' + esc(type.name.toLowerCase()) + '</b>, we\'re only asking about <b>' + esc(type.asks) + '</b>. ' +
        (nothing ? "You travel light — there's little to set here." : "Other trades' fields are hidden."));
    }

    if (reqKeys.length) {
      html += sectionLabel("Site needs");
      html += '<div class="req-grid" style="margin-bottom:var(--section-gap)">';
      reqKeys.forEach(function (k) {
        var r = REQ_CATALOG[k], on = d.reqs.indexOf(k) > -1;
        html += '<button type="button" class="req-toggle' + (on ? " is-on" : "") + '" data-action="toggle-req" data-req="' + k + '">' +
          '<span class="req-ic"><i class="fas ' + r.icon + '"></i></span>' +
          '<span class="req-info"><div class="req-name">' + esc(r.name) + '</div><div class="req-sub">' + esc(r.sub) + '</div></span>' +
          '<span class="req-switch"></span></button>';
      });
      html += '</div>';
    }

    html += sectionLabel("Setting");
    html += '<div class="field">' + fieldLabel(null, false, "Where can you work?") +
      segmentedHTML("indoorOutdoor", d.indoorOutdoor || "both", [{ v: "indoor", l: "Indoor only" }, { v: "outdoor", l: "Outdoor only" }, { v: "both", l: "Either" }]) + '</div>';

    if (showFootprint) {
      html += '<div class="subcard"><p class="subcard-h"><i class="fas fa-ruler-combined" style="color:var(--accent)"></i> Space you need</p>' +
        '<p class="subcard-sub">The footprint a venue must set aside for you.</p>' +
        '<div class="field-row two">' +
        '<div class="field">' + fieldLabel("Width", false, null) + '<div style="display:flex;align-items:center;gap:8px"><input class="input" style="max-width:90px" inputmode="decimal" data-field="footW" data-sanitize="decimal" value="' + esc(d.footW) + '" placeholder="3"><span style="font-weight:600;color:var(--ink-soft)">m</span></div></div>' +
        '<div class="field">' + fieldLabel("Depth", false, null) + '<div style="display:flex;align-items:center;gap:8px"><input class="input" style="max-width:90px" inputmode="decimal" data-field="footD" data-sanitize="decimal" value="' + esc(d.footD) + '" placeholder="5"><span style="font-weight:600;color:var(--ink-soft)">m</span></div></div>' +
        '</div></div>';
    }
    if (showCapacity) {
      html += '<div class="subcard"><p class="subcard-h"><i class="fas fa-users" style="color:var(--accent)"></i> Guest capacity</p>' +
        '<p class="subcard-sub">The range you can comfortably serve.</p>' +
        '<div class="field-row two">' +
        '<div class="field">' + fieldLabel("Minimum", false, null) + stepperHTML("capMin", d.capMin || 0, { step: 5, suffix: "guests" }) + '</div>' +
        '<div class="field">' + fieldLabel("Maximum", false, null) + stepperHTML("capMax", d.capMax || 0, { step: 10, suffix: "guests" }) + '</div></div></div>';
    }
    if (showSetup) {
      html += '<div class="field">' + fieldLabel("Setup & breakdown", false, "How long before and after you need.") +
        selectHTML("setupTime", d.setupTime || "", [
          { v: "", l: "Select…" }, { v: "30min", l: "About 30 minutes" }, { v: "1hr", l: "Around 1 hour" },
          { v: "2hr", l: "2–3 hours" }, { v: "halfday", l: "Half a day" }, { v: "fullday", l: "A full day" }, { v: "multiday", l: "Multiple days" }
        ]) + '</div>';
    }

    html += '<div class="field">' + fieldLabel("Anything else a venue should know?", false, "Optional — access quirks, noise limits, allergens, etc.") +
      '<textarea class="textarea" rows="2" data-field="reqNotes" placeholder="e.g. Generator available if no mains power. Quiet pack-down after 11pm.">' + esc(d.reqNotes) + '</textarea></div>';
    html += '</div>';
    return html;
  }

  /* ---------------------------------------------------------
     Step 5 · Coverage & availability
     --------------------------------------------------------- */
  function renderCoverage() {
    var d = state.data;
    var html = '<div class="fade-in">' +
      '<p class="step-eyebrow">Step 5 · Where & when</p>' +
      '<h1 class="step-title">Coverage & availability</h1>' +
      '<p class="step-lead">Where you\'ll travel, and when you\'re free. Customers can only book dates you\'ve left open — so this is the bit that stops double-bookings.</p>';

    html += sectionLabel("Coverage area");
    html += '<div class="field">' + fieldLabel("Based in", true, null) +
      '<input class="input" data-field="location" value="' + esc(d.location) + '" placeholder="e.g. Leeds, West Yorkshire"></div>';

    html += '<div style="margin-bottom:var(--gap)">' +
      '<button type="button" class="req-toggle' + (d.nationwide ? " is-on" : "") + '" data-action="toggle-nationwide">' +
      '<span class="req-ic"><i class="fas fa-location-arrow"></i></span>' +
      '<span class="req-info"><div class="req-name">Available nationwide</div><div class="req-sub">Turn off to set a travel radius from your base</div></span>' +
      '<span class="req-switch"></span></button></div>';

    if (!d.nationwide) {
      html += '<div class="subcard fade-in"><p class="subcard-h">Travel radius — <span style="color:var(--accent)" id="radiusLabel">' + (d.radius || 30) + ' miles</span></p>' +
        '<input class="range" type="range" min="5" max="150" step="5" data-range="radius" value="' + (d.radius || 30) + '">' +
        '<div style="display:flex;justify-content:space-between;font-size:11.5px;color:var(--ink-faint);margin-top:4px;font-weight:600"><span>5 mi</span><span>150 mi</span></div>' +
        '<div class="field-row two" style="margin-top:14px">' +
        '<div class="field">' + fieldLabel("Free travel within", false, null) + stepperHTML("freeRadius", d.freeRadius || 10, { step: 5, suffix: "mi" }) + '</div>' +
        '<div class="field">' + fieldLabel("Then per extra mile", false, null) + moneyHTML("travelFee", d.travelFee, "0.80") + '</div></div></div>';
    }

    html += sectionLabel("Availability");
    html += '<div class="field">' + fieldLabel("Minimum notice you need", false, "How far ahead must customers book?") +
      selectHTML("leadTime", d.leadTime || "", [
        { v: "", l: "Select…" }, { v: "0", l: "Same-day OK" }, { v: "1", l: "At least 1 day" }, { v: "3", l: "At least 3 days" },
        { v: "7", l: "At least 1 week" }, { v: "14", l: "At least 2 weeks" }, { v: "30", l: "A month or more" }
      ]) + '</div>';

    html += '<div class="field">' + fieldLabel("Block out dates you're not available", false, "Click dates to mark them unavailable. You can manage these any time from your calendar.") +
      miniCalendar() + '</div>';
    html += '</div>';
    return html;
  }

  function miniCalendar() {
    var d = state.data, blocked = d.blocked || [];
    var base = new Date(2026, 5, 1);
    var view = new Date(base.getFullYear(), base.getMonth() + state.calOffset, 1);
    var year = view.getFullYear(), month = view.getMonth();
    var monthName = view.toLocaleString("en-GB", { month: "long", year: "numeric" });
    var firstDay = (new Date(year, month, 1).getDay() + 6) % 7;
    var days = new Date(year, month + 1, 0).getDate();
    var key = function (dd) { return year + "-" + (month + 1) + "-" + dd; };

    var dow = "";
    ["M", "T", "W", "T", "F", "S", "S"].forEach(function (x) { dow += '<div style="text-align:center;font-size:10.5px;font-weight:700;color:var(--ink-faint)">' + x + '</div>'; });

    var cells = "";
    for (var i = 0; i < firstDay; i++) cells += '<div></div>';
    for (var dd = 1; dd <= days; dd++) {
      var k = key(dd), on = blocked.indexOf(k) > -1;
      cells += '<button type="button" data-action="cal-toggle" data-key="' + k + '" style="aspect-ratio:1;border:none;border-radius:9px;cursor:pointer;font-family:inherit;font-size:13px;font-weight:600;' +
        'background:' + (on ? "var(--accent)" : "var(--paper-2)") + ';color:' + (on ? "var(--white)" : "var(--ink-soft)") + ';' +
        (on ? "text-decoration:line-through;" : "") + 'transition:all .12s">' + dd + '</button>';
    }

    return '<div class="subcard" style="background:var(--white);border:1.5px solid var(--line)">' +
      '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">' +
      '<button type="button" class="btn-del" style="width:34px;height:34px" data-action="cal-prev"' + (state.calOffset <= 0 ? " disabled" : "") + '><i class="fas fa-chevron-left"></i></button>' +
      '<span style="font-weight:700;font-size:14.5px">' + esc(monthName) + '</span>' +
      '<button type="button" class="btn-del" style="width:34px;height:34px" data-action="cal-next"' + (state.calOffset >= 11 ? " disabled" : "") + '><i class="fas fa-chevron-right"></i></button></div>' +
      '<div style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;margin-bottom:4px">' + dow + '</div>' +
      '<div style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px">' + cells + '</div>' +
      '<div style="font-size:12px;color:var(--ink-faint);margin-top:11px;display:flex;align-items:center;gap:7px">' +
      '<span style="width:12px;height:12px;border-radius:4px;background:var(--accent);display:inline-block"></span> Blocked = customers can\'t book</div></div>';
  }

  /* ---------------------------------------------------------
     Step 6 · Review
     --------------------------------------------------------- */
  function reviewRow(label, value) {
    return '<div style="display:grid;grid-template-columns:120px 1fr;gap:12px;font-size:14px">' +
      '<span style="color:var(--ink-faint);font-weight:600">' + esc(label) + '</span>' +
      '<span style="font-weight:500">' + esc(value) + '</span></div>';
  }
  function reviewBlock(title, stepIndex, rows) {
    return '<div class="subcard" style="background:var(--white);border:1.5px solid var(--line)">' +
      '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">' +
      '<p class="subcard-h" style="margin:0">' + esc(title) + '</p>' +
      '<button type="button" class="btn-text" style="padding:2px 4px;font-size:13.5px;color:var(--accent-deep)" data-action="review-edit" data-step="' + stepIndex + '"><i class="fas fa-pen" style="font-size:11px"></i> Edit</button></div>' +
      '<div style="display:flex;flex-direction:column;gap:9px">' + rows + '</div></div>';
  }
  function priceSummary(d) {
    if (!d.pricing) return "—";
    if (d.pricing === "quote") return d.startPrice ? "From £" + d.startPrice + " · by quote" : "Price on request";
    if (!d.startPrice) return "Add a price";
    var unit = { guest: " per guest", duration: "/" + (d.durationUnit || "hour"), quantity: " per " + (d.unitLabel || "item"), pitch: " pitch fee" }[d.pricing] || "";
    return "From £" + d.startPrice + unit;
  }
  function renderReview(type) {
    var d = state.data;
    var reqNames = (d.reqs || []).map(function (k) { return REQ_CATALOG[k] ? REQ_CATALOG[k].name : null; }).filter(Boolean);
    var setting = { indoor: "Indoor only", outdoor: "Outdoor only", both: "Indoor or outdoor" }[d.indoorOutdoor] || "—";
    var html = '<div class="fade-in">' +
      '<p class="step-eyebrow">Step 6 · Review</p>' +
      '<h1 class="step-title">Looking good — ready to go live?</h1>' +
      '<p class="step-lead">Here\'s everything customers will see. Tweak anything, then publish. You can edit your listing any time afterwards.</p>';

    html += reviewBlock("Your craft", 0, reviewRow("Trade", type ? type.name : "—") + reviewRow("Tags", d.tags.length ? d.tags.join(", ") : "—"));
    html += reviewBlock("The basics", 1, reviewRow("Title", d.title || "—") + reviewRow("Summary", d.shortDesc || "—") + reviewRow("Photos", d.img ? "1 photo added" : "None yet"));
    html += reviewBlock("Pricing", 2, reviewRow("Model", d.pricing ? PRICING_MODELS[d.pricing].name : "—") + reviewRow("Customer sees", priceSummary(d)));
    var onDay = reviewRow("Site needs", reqNames.length ? reqNames.join(", ") : "Travels light") + reviewRow("Setting", setting);
    if (type && type.capacity && (d.capMin || d.capMax)) onDay += reviewRow("Capacity", (d.capMin || 0) + "–" + (d.capMax || "?") + " guests");
    html += reviewBlock("On the day", 3, onDay);
    var blocked = (d.blocked || []).length;
    html += reviewBlock("Where & when", 4,
      reviewRow("Based in", d.location || "—") +
      reviewRow("Coverage", d.nationwide ? "Nationwide" : (d.radius || 30) + " mile radius") +
      reviewRow("Blocked dates", blocked ? blocked + " date" + (blocked > 1 ? "s" : "") + " blocked" : "All dates open"));
    html += '</div>';
    return html;
  }

  /* ---------------------------------------------------------
     Live preview card
     --------------------------------------------------------- */
  function previewCard(d) {
    var type = getType(d.typeId);
    var price;
    if (d.pricing === "quote") price = '<span class="pv-price"><small>Price on request</small></span>';
    else if (!d.startPrice) price = '<span class="pv-price"><small>Add pricing</small></span>';
    else {
      var unit = { guest: "/guest", duration: "/hr", quantity: "/item", pitch: " pitch" }[d.pricing] || "";
      price = '<span class="pv-price"><small>From</small> £' + esc(d.startPrice) + esc(unit) + '</span>';
    }
    var attrs = [];
    if (type) {
      if (d.reqs && d.reqs.indexOf("power") > -1) attrs.push({ i: "fa-plug", t: "Powered" });
      if (d.indoorOutdoor === "outdoor" || d.indoorOutdoor === "both") attrs.push({ i: "fa-tree", t: "Outdoor OK" });
      if (d.reqs && d.reqs.indexOf("own_equip") > -1) attrs.push({ i: "fa-toolbox", t: "Self-sufficient" });
      if (d.capMax) attrs.push({ i: "fa-users", t: "Up to " + d.capMax });
      if (d.nationwide) attrs.push({ i: "fa-location-arrow", t: "Nationwide" });
    }
    var attrHTML = "";
    if (attrs.length) {
      attrHTML = '<div class="pv-attrs">';
      attrs.forEach(function (a) { attrHTML += '<span class="pv-attr"><i class="fas ' + a.i + '"></i>' + esc(a.t) + '</span>'; });
      attrHTML += '</div>';
    }
    var media = d.img
      ? '<img src="' + esc(d.img) + '" alt="">'
      : '<div class="pv-empty"><i class="fas fa-image" style="font-size:22px"></i><span>Your photos appear here</span></div>';

    return '<div class="pv-card">' +
      '<div class="pv-media">' + media + '<span class="pv-verified"><i class="fas fa-circle-check"></i> Verified</span></div>' +
      '<div class="pv-body">' +
      '<div class="pv-cat">' + esc(type ? type.name : "Your category") + '</div>' +
      '<h3 class="pv-title' + (d.title ? "" : " empty") + '">' + esc(d.title || "Your listing title") + '</h3>' +
      '<div class="pv-meta"><span class="pv-loc"><i class="fas fa-location-dot"></i>' + esc(d.location || "Your area") + '</span>' + price + '</div>' +
      '<p class="pv-desc">' + esc(d.shortDesc || "A short description of what you offer will show here for customers browsing the marketplace.") + '</p>' +
      attrHTML +
      '</div>' +
      '<div class="pv-foot"><span class="pv-rating"><span class="stars">★★★★★</span> New supplier</span>' +
      '<span class="pv-link">View details <i class="fas fa-arrow-right" style="font-size:11px"></i></span></div>' +
      '</div>';
  }

  /* ---------------------------------------------------------
     Render: shell, step, chrome
     --------------------------------------------------------- */
  var root, stageEl;

  function shellHTML() {
    return '<div class="app">' +
      '<aside class="rail-col">' +
      '<a class="brand" href="#" data-action="noop"><span class="ps">P<span class="dot">.</span>S<span class="dot">.</span></span><span class="name">Partysmith</span></a>' +
      '<div><div class="rail-eyebrow">List your service</div><ol class="steps-list" id="stepsList"></ol></div>' +
      '<div class="rail-foot"><a class="save-exit" href="' + esc(CFG.exitUrl || "/") + '"><i class="fas fa-arrow-left"></i> Save &amp; exit</a>' +
      '<div class="autosave"><span class="pulse"></span> Progress saved as you go</div></div>' +
      '</aside>' +
      '<div class="main-col"><div class="main-scroll"><div class="stage" id="stage"></div></div>' +
      '<div class="foot-nav" id="footNav"></div></div>' +
      '<aside class="preview-col" id="previewCol"></aside>' +
      '</div>';
  }

  function renderStep() {
    var cur = STEPS[state.step];
    var type = getType(state.data.typeId);
    var html;
    switch (cur.id) {
      case "type":   html = renderType(); break;
      case "basics": html = renderBasics(type); break;
      case "price":  html = renderPricing(type); break;
      case "reqs":   html = renderReqs(type); break;
      case "cover":  html = renderCoverage(); break;
      case "review": html = renderReview(type); break;
    }
    stageEl.innerHTML = html;
  }

  function updateChrome() {
    var d = state.data, type = getType(d.typeId), pct = completeness();

    // rail
    var list = "";
    STEPS.forEach(function (s, i) {
      var done = i < state.step && validFor(s.id, d);
      var active = i === state.step;
      var locked = i > state.maxStep && !validFor(STEPS[Math.max(0, i - 1)].id, d);
      list += '<li><button class="step-row' + (active ? " is-active" : "") + (done ? " is-done" : "") + (locked ? " is-locked" : "") + '" data-action="nav-go" data-step="' + i + '"' + (locked ? " disabled" : "") + '>' +
        '<span class="step-dot">' + (done ? '<i class="fas fa-check"></i>' : (i + 1)) + '</span>' +
        '<span class="step-meta"><span class="step-kicker">' + esc(s.kicker) + '</span><span class="step-name">' + esc(s.name) + '</span>' +
        (s.id === "reqs" && type && active ? '<span class="step-adaptive">Tailored to ' + esc(type.name.toLowerCase()) + '</span>' : "") +
        '</span></button></li>';
    });
    document.getElementById("stepsList").innerHTML = list;

    // foot nav
    var cur = STEPS[state.step];
    var back = state.step > 0
      ? '<button class="btn-text" data-action="nav-back"><i class="fas fa-arrow-left"></i> Back</button>'
      : '<span style="width:60px"></span>';
    var action = cur.id === "review"
      ? '<button class="btn btn-primary" data-action="publish"' + (state.busy ? " disabled" : "") + '><i class="fas fa-rocket"></i> ' + (state.busy ? "Publishing…" : "Publish listing") + '</button>'
      : '<button class="btn btn-primary" data-action="nav-continue"' + (validFor(cur.id, d) ? "" : " disabled") + '>Continue <i class="fas fa-arrow-right"></i></button>';
    document.getElementById("footNav").innerHTML =
      back +
      '<div class="progress-mini"><span class="pm-label">' + pct + '% ready to publish</span>' +
      '<div class="pm-track"><div class="pm-fill" style="width:' + pct + '%"></div></div></div>' +
      '<div class="foot-actions">' + action + '</div>';

    // preview column
    var checks = [
      { done: validFor("type", d), label: "Trade chosen" },
      { done: validFor("basics", d), label: "Title & description" },
      { done: validFor("price", d), label: "Pricing set" },
      { done: state.maxStep >= 3, label: "Site needs reviewed" },
      { done: validFor("cover", d), label: "Coverage & dates" }
    ];
    var checkHTML = "";
    checks.forEach(function (c) {
      checkHTML += '<div class="pv-check-item' + (c.done ? " done" : "") + '"><span class="ck">' + (c.done ? '<i class="fas fa-check"></i>' : "") + '</span>' + esc(c.label) + '</div>';
    });
    document.getElementById("previewCol").innerHTML =
      '<div class="preview-head"><span class="preview-kicker">Live preview</span>' +
      '<span class="completeness"><i class="fas fa-circle-check"></i> ' + pct + '%</span></div>' +
      previewCard(d) +
      '<div class="pv-note">P.S. looking good already.</div>' +
      '<div style="border-top:1px solid var(--line);padding-top:16px">' +
      '<span class="preview-kicker" style="display:block;margin-bottom:12px">Your checklist</span>' +
      '<div class="pv-checklist">' + checkHTML + '</div></div>';
  }

  /* ---------------------------------------------------------
     Live updates that must not re-render the step (preserve focus)
     --------------------------------------------------------- */
  function updateCharCount(field) {
    var el = stageEl.querySelector('[data-count="' + field + '"]');
    if (el) el.textContent = (state.data[field] || "").length + "/140";
  }

  /* ---------------------------------------------------------
     Event handling (delegation)
     --------------------------------------------------------- */
  function sanitize(kind, v) {
    if (kind === "money") return v.replace(/[^\d.,]/g, "");
    if (kind === "moneycomma") return v.replace(/[^\d,]/g, "");
    if (kind === "decimal") return v.replace(/[^\d.]/g, "");
    if (kind === "digits") return v.replace(/\D/g, "");
    return v;
  }

  function onInput(e) {
    var t = e.target;
    if (t.id === "typeSearch") { state.typeQuery = t.value; renderStep(); var s = document.getElementById("typeSearch"); if (s) { s.focus(); s.setSelectionRange(s.value.length, s.value.length); } return; }

    var field = t.getAttribute("data-field");
    if (field) {
      var val = t.value;
      var san = t.getAttribute("data-sanitize");
      if (san) { val = sanitize(san, val); if (val !== t.value) t.value = val; }
      var patch = {}; patch[field] = val;
      set(patch, false);
      if (field === "shortDesc") updateCharCount("shortDesc");
      return;
    }

    var stepperField = t.getAttribute("data-stepper");
    if (stepperField) {
      var min = parseInt(t.getAttribute("data-min"), 10) || 0;
      var max = parseInt(t.getAttribute("data-max"), 10);
      var n = parseInt(t.value.replace(/\D/g, ""), 10) || 0;
      n = Math.min(isNaN(max) ? 9999 : max, Math.max(min, n));
      var p = {}; p[stepperField] = n; set(p, false);
      return;
    }

    var tierIdx = t.getAttribute("data-tier");
    if (tierIdx != null) {
      var key = t.getAttribute("data-tier-key");
      var v = t.value;
      var san2 = t.getAttribute("data-sanitize");
      if (san2) { v = sanitize(san2, v); if (v !== t.value) t.value = v; }
      var list = tiers().slice();
      list[parseInt(tierIdx, 10)] = assign({}, list[parseInt(tierIdx, 10)]);
      list[parseInt(tierIdx, 10)][key] = v;
      set({ tiers: list, startPrice: minPrice(list) }, false);
      return;
    }

    if (t.getAttribute("data-range") === "radius") {
      var r = parseInt(t.value, 10);
      state.data.radius = r; persist();
      var lab = document.getElementById("radiusLabel"); if (lab) lab.textContent = r + " miles";
      return;
    }
  }

  function onChange(e) {
    var t = e.target, field = t.getAttribute("data-field");
    if (field && t.tagName === "SELECT") { var p = {}; p[field] = t.value; set(p); }
  }

  function onKeydown(e) {
    var t = e.target;
    if (!t.hasAttribute || !t.hasAttribute("data-tag-input")) return;
    var tags = state.data.tags.slice();
    if (e.key === "Enter" || e.key === ",") {
      e.preventDefault();
      var v = t.value.trim();
      if (v && tags.indexOf(v) === -1) { tags.push(v); set({ tags: tags }); }
      else t.value = "";
    } else if (e.key === "Backspace" && !t.value && tags.length) {
      tags.pop(); set({ tags: tags });
    }
  }

  function bumpStepper(field, dir, bound, step) {
    var v = parseInt(state.data[field], 10) || 0;
    v = v + dir * step;
    if (dir < 0) v = Math.max(bound, v); else v = Math.min(bound, v);
    var p = {}; p[field] = v; set(p);
  }

  function onClick(e) {
    var btn = e.target.closest("[data-action]");
    if (!btn) return;
    var action = btn.getAttribute("data-action");
    if (action === "noop") { e.preventDefault(); return; }
    var d = state.data;

    switch (action) {
      case "select-type": selectType(getType(btn.getAttribute("data-type"))); break;
      case "select-pricing": set({ pricing: btn.getAttribute("data-model") }); break;
      case "toggle-req": {
        var k = btn.getAttribute("data-req");
        var reqs = d.reqs.slice();
        var idx = reqs.indexOf(k);
        if (idx > -1) reqs.splice(idx, 1); else reqs.push(k);
        set({ reqs: reqs });
        break;
      }
      case "toggle-nationwide": set({ nationwide: !d.nationwide }); break;
      case "segment": { var p = {}; p[btn.getAttribute("data-field")] = btn.getAttribute("data-value"); set(p); break; }
      case "step-inc": bumpStepper(btn.getAttribute("data-field"), 1, parseInt(btn.getAttribute("data-max"), 10), parseInt(btn.getAttribute("data-step"), 10)); break;
      case "step-dec": bumpStepper(btn.getAttribute("data-field"), -1, parseInt(btn.getAttribute("data-min"), 10), parseInt(btn.getAttribute("data-step"), 10)); break;
      case "use-sample": { var ty = getType(d.typeId); if (ty) set({ img: sampleImg(ty.img) }); break; }
      case "remove-img": set({ img: null }); break;
      case "tag-remove": { var tg = btn.getAttribute("data-tag"); set({ tags: d.tags.filter(function (x) { return x !== tg; }) }); break; }
      case "tag-add": { var ta = btn.getAttribute("data-tag"); if (d.tags.indexOf(ta) === -1) set({ tags: d.tags.concat([ta]) }); break; }
      case "tag-focus": { var inp = btn.querySelector("[data-tag-input]"); if (inp) inp.focus(); break; }
      case "tier-add": set({ tiers: tiers().concat([{ name: "", price: "" }]) }); break;
      case "tier-del": { var i = parseInt(btn.getAttribute("data-index"), 10); var list = tiers().filter(function (_, idx) { return idx !== i; }); set({ tiers: list, startPrice: minPrice(list) }); break; }
      case "cal-prev": if (state.calOffset > 0) { state.calOffset--; renderStep(); } break;
      case "cal-next": if (state.calOffset < 11) { state.calOffset++; renderStep(); } break;
      case "cal-toggle": { var kk = btn.getAttribute("data-key"); var b = (d.blocked || []).slice(); var bi = b.indexOf(kk); if (bi > -1) b.splice(bi, 1); else b.push(kk); set({ blocked: b }); break; }
      case "nav-go": { var s = parseInt(btn.getAttribute("data-step"), 10); if (!btn.disabled) go(s); break; }
      case "nav-back": go(state.step - 1); break;
      case "nav-continue": if (validFor(STEPS[state.step].id, d)) go(state.step + 1); break;
      case "review-edit": go(parseInt(btn.getAttribute("data-step"), 10)); break;
      case "publish": publish(); break;
      case "published-again": restart(); break;
    }
  }

  /* ---------------------------------------------------------
     Publish → create the service for real
     --------------------------------------------------------- */
  function publish() {
    if (state.busy) return;
    state.busy = true;
    updateChrome();

    var body = "payload=" + encodeURIComponent(JSON.stringify(state.data));
    if (CFG.csrfName && CFG.csrfHash) body += "&" + encodeURIComponent(CFG.csrfName) + "=" + encodeURIComponent(CFG.csrfHash);

    fetch(CFG.publishUrl, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded", "X-Requested-With": "XMLHttpRequest" },
      body: body
    })
      .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
      .then(function (res) {
        state.busy = false;
        if (res.ok && res.j && res.j.success) {
          if (res.j.csrfHash) CFG.csrfHash = res.j.csrfHash;
          state.publishedInfo = res.j;
          showPublished();
          try { localStorage.removeItem(STORE_KEY); } catch (e) {}
        } else {
          updateChrome();
          alert((res.j && res.j.error) ? res.j.error : "Sorry — we couldn't publish your listing. Please try again.");
        }
      })
      .catch(function () {
        state.busy = false; updateChrome();
        alert("Network error — please check your connection and try again.");
      });
  }

  function showPublished() {
    var d = state.data, type = getType(d.typeId), info = state.publishedInfo || {};
    var viewUrl = info.viewUrl || "#";
    root.innerHTML = '<div class="app" style="display:block;height:auto;min-height:100vh;overflow:visible">' +
      '<div class="success-wrap fade-in">' +
      '<div class="success-script">P.S. you\'re live!</div>' +
      '<div class="success-ic"><i class="fas fa-check"></i></div>' +
      '<h1 class="step-title" style="font-size:32px">' + esc(d.title || "Your listing") + ' is published</h1>' +
      '<p class="step-lead" style="margin-inline:auto;text-align:center">Customers can now find and book you' + (type ? " as a " + esc(type.name.toLowerCase()) : "") + '. We\'ll email you the moment an enquiry lands.</p>' +
      '<div style="max-width:380px;margin:10px auto 26px">' + previewCard(d) + '</div>' +
      '<div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">' +
      '<a class="btn btn-primary" href="' + esc(viewUrl) + '"><i class="fas fa-eye"></i> View live listing</a>' +
      '<button class="btn btn-ghost" data-action="published-again"><i class="fas fa-plus"></i> Add another service</button>' +
      '</div></div></div>';
  }

  function restart() {
    state.data = clone(INITIAL);
    state.step = 0; state.maxStep = 0; state.calOffset = 0; state.published = false; state.publishedInfo = null;
    persist();
    root.innerHTML = shellHTML();
    stageEl = document.getElementById("stage");
    renderStep(); updateChrome();
  }

  /* ---------------------------------------------------------
     Boot
     --------------------------------------------------------- */
  function init() {
    root = document.getElementById("root");
    root.innerHTML = shellHTML();
    stageEl = document.getElementById("stage");
    root.addEventListener("click", onClick);
    root.addEventListener("input", onInput);
    root.addEventListener("change", onChange);
    root.addEventListener("keydown", onKeydown);
    renderStep();
    updateChrome();
  }

  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init);
  else init();
})();
