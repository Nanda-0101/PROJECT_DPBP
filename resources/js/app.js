/* ===========================================================
   CamRent — App JS (Data layer + Shared + Page Controllers)
   Laravel + Vite + Tailwind v4
=========================================================== */

import './bootstrap';

/* ===========================================================
   1. DATA LAYER (mock backend via localStorage)
=========================================================== */
const DB_KEYS = {
  products: 'camrent_products',
  bookings: 'camrent_bookings',
  market:   'camrent_market',
  tickets:  'camrent_tickets',
  users:    'camrent_users',
  session:  'camrent_session'
};

const SEED_PRODUCTS = [
  // ---------- KAMERA ----------
  {id:'p1',  name:'Sony A7III',           category:'Kamera',   image:'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=500&h=400&fit=crop', pricePerHalfDay:280000, pricePerDay:480000, priceSale:18500000, stock:4,  rating:4.9, reviews:212, trending:true,  seller:'Alpha Rental Studio', icon:'bi-camera',       brand:'Sony',      desc:'Mirrorless full-frame andalan untuk foto & video.',    specs:['Sensor full-frame 24.2MP','Video 4K 30fps','Dual slot SD card']},
  {id:'p2',  name:'Canon EOS R6',         category:'Kamera',   image:'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=500&h=400&fit=crop', pricePerHalfDay:300000, pricePerDay:520000, priceSale:24900000, stock:3,  rating:4.8, reviews:154, trending:true,  seller:'Lensa Kita',          icon:'bi-camera',       brand:'Canon',     desc:'Mirrorless dengan stabilizer 5-axis.',                 specs:['20MP full-frame','4K60 10-bit','IBIS 5-axis']},
  {id:'p3',  name:'Fujifilm X-T5',        category:'Kamera',   image:'https://images.unsplash.com/photo-1500634245200-e5245c7574ef?w=500&h=400&fit=crop', pricePerHalfDay:230000, pricePerDay:400000, priceSale:0,         stock:2,  rating:4.7, reviews:98,  trending:false, seller:'Alpha Rental Studio', icon:'bi-camera',       brand:'Fujifilm',  desc:'APS-C dengan warna khas film simulation.',             specs:['40MP APS-C','Film simulation','IBIS 7 stop']},

  // ---------- LENSA ----------
  {id:'p4',  name:'Sigma 24-70mm f/2.8',  category:'Lensa',    image:'https://images.unsplash.com/photo-1617005082133-548c4dd27f35?w=500&h=400&fit=crop', pricePerHalfDay:150000, pricePerDay:260000, priceSale:9800000,   stock:5,  rating:4.6, reviews:76,  trending:true,  seller:'Lensa Kita',          icon:'bi-camera2',      brand:'Sigma',     desc:'Lensa zoom serbaguna aperture konstan.',               specs:['Mount E/EF/L','f/2.8 konstan','Optical stabilizer']},
  {id:'p5',  name:'Tamron 70-200mm',      category:'Lensa',    image:'https://images.unsplash.com/photo-1606986628253-05620e9f76bb?w=500&h=400&fit=crop', pricePerHalfDay:180000, pricePerDay:320000, priceSale:0,         stock:2,  rating:4.5, reviews:41,  trending:false, seller:'Alpha Rental Studio', icon:'bi-camera2',      brand:'Tamron',    desc:'Telephoto ringan, tajam untuk potret & olahraga.',     specs:['f/2.8','VC stabilizer','67cm min focus']},

  // ---------- LIGHTING ----------
  {id:'p6',  name:'Aputure 120D II',      category:'Lighting', image:'https://images.unsplash.com/photo-1595856619767-ab951ee9f5b5?w=500&h=400&fit=crop', pricePerHalfDay:190000, pricePerDay:340000, priceSale:6500000,   stock:6,  rating:4.8, reviews:132, trending:true,  seller:'Cahaya Studio',       icon:'bi-lightbulb',    brand:'Aputure',   desc:'LED daylight tenaga besar, standar industri.',         specs:['120W daylight 5500K','Bowens mount','Kontrol via aplikasi']},
  {id:'p7',  name:'Godox SL60W',          category:'Lighting', image:'https://images.unsplash.com/photo-1583912784187-9c9c4cd98fd8?w=500&h=400&fit=crop', pricePerHalfDay:90000,  pricePerDay:160000, priceSale:1850000,   stock:8,  rating:4.4, reviews:59,  trending:false, seller:'Cahaya Studio',       icon:'bi-lightbulb',    brand:'Godox',     desc:'LED continuous ekonomis untuk studio kecil.',          specs:['60W','Bowens mount','Remote termasuk']},

  // ---------- AUDIO ----------
  {id:'p8',  name:'RØDE NTG5',            category:'Audio',    image:'https://images.unsplash.com/photo-1590602847861-f357a9332bbc?w=500&h=400&fit=crop', pricePerHalfDay:120000, pricePerDay:210000, priceSale:4200000,   stock:3,  rating:4.7, reviews:64,  trending:false, seller:'Suara Prima',         icon:'bi-mic',          brand:'RØDE',      desc:'Shotgun mic broadcast-grade, minim noise.',            specs:['Shotgun condenser','Low noise 12dB','Tahan cuaca']},
  {id:'p9',  name:'Shure SM7B',           category:'Audio',    image:'https://images.unsplash.com/photo-1520170350707-b2da59970118?w=500&h=400&fit=crop', pricePerHalfDay:130000, pricePerDay:230000, priceSale:4900000,   stock:2,  rating:4.9, reviews:88,  trending:true,  seller:'Suara Prima',         icon:'bi-mic',          brand:'Shure',     desc:'Mic dinamis studio favorit podcaster & musisi.',       specs:['Dynamic cardioid','Built-in pop filter','Konektor XLR']},

  // ---------- GIMBAL / TRIPOD / ACTION CAM ----------
  {id:'p10', name:'DJI RS3 Gimbal',       category:'Gimbal',   image:'https://images.unsplash.com/photo-1589656966895-2f33e7653819?w=500&h=400&fit=crop', pricePerHalfDay:140000, pricePerDay:250000, priceSale:6900000,   stock:4,  rating:4.8, reviews:120, trending:true,  seller:'Gerak Video',         icon:'bi-camera-reels', brand:'DJI',       desc:'Stabilizer 3-axis payload 3kg.',                       specs:['Payload 3kg','LiDAR autofocus','Baterai 12 jam']},
  {id:'p11', name:'Manfrotto Tripod 190', category:'Tripod',   image:'https://images.unsplash.com/photo-1606986628253-05620e9f76bb?w=500&h=400&fit=crop', pricePerHalfDay:60000,  pricePerDay:100000, priceSale:1650000,   stock:10, rating:4.5, reviews:47,  trending:false, seller:'Gerak Video',         icon:'bi-camera-reels', brand:'Manfrotto', desc:'Tripod aluminium kokoh dengan ballhead presisi.',       specs:['Tinggi maks 150cm','Beban maks 6kg']},
  {id:'p12', name:'GoPro Hero 12',        category:'Video',    image:'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=500&h=400&fit=crop', pricePerHalfDay:110000, pricePerDay:190000, priceSale:5600000,   stock:5,  rating:4.6, reviews:93,  trending:true,  seller:'Gerak Video',         icon:'bi-play-btn',     brand:'GoPro',     desc:'Action cam tahan air, cocok konten outdoor.',           specs:['5.3K60','HyperSmooth 6.0','Waterproof 10m']},
];

const CREATORS = [
  {name:'Alpha Rental Studio', role:'Mitra sejak 2022', rating:4.9, items:38, icon:'bi-camera-reels-fill'},
  {name:'Cahaya Studio',       role:'Mitra sejak 2023', rating:4.8, items:22, icon:'bi-lightbulb-fill'},
  {name:'Suara Prima',         role:'Mitra sejak 2021', rating:4.9, items:15, icon:'bi-mic-fill'},
  {name:'Gerak Video',         role:'Mitra sejak 2023', rating:4.7, items:29, icon:'bi-camera-video-fill'},
];

/* ---------- Storage helpers ---------- */
function seedIfEmpty(key, value){
  if(localStorage.getItem(key) === null) localStorage.setItem(key, JSON.stringify(value));
}

function initData(){
  seedIfEmpty(DB_KEYS.products, SEED_PRODUCTS);
  seedIfEmpty(DB_KEYS.users, [
    {id:'u1', name:'Rangga Pratama', email:'user@camrent.id',  password:'user123',  role:'user',  phone:'081234567890'},
    {id:'m1', name:'Alpha Rental Studio', email:'mitra@camrent.id', password:'mitra123', role:'mitra', phone:'081298765432', status:'Terverifikasi'}
  ]);
  seedIfEmpty(DB_KEYS.bookings, [
    {id:'BKG-1001', productId:'p1',  productName:'Sony A7III',      renter:'Rangga Pratama', phone:'081234567890', start:'2026-09-05', end:'2026-09-06', duration:'1 Hari', paymentType:'DP',           paymentMethod:'QRIS',     guarantee:'KTP', total:480000, dp:144000, status:'Selesai',             created:'2026-09-01'},
    {id:'BKG-1002', productId:'p6',  productName:'Aputure 120D II', renter:'Rangga Pratama', phone:'081234567890', start:'2026-09-14', end:'2026-09-14', duration:'12 Jam', paymentType:'Bayar Penuh',  paymentMethod:'Transfer', guarantee:'SIM', total:190000, dp:190000, status:'Dikonfirmasi',        created:'2026-09-10'},
    {id:'BKG-1003', productId:'p10', productName:'DJI RS3 Gimbal',  renter:'Dinda Amelia',   phone:'081311122233', start:'2026-09-20', end:'2026-09-22', duration:'2 Hari', paymentType:'DP',           paymentMethod:'Cash',     guarantee:'KTP', total:500000, dp:150000, status:'Menunggu Konfirmasi', created:'2026-09-11'},
  ]);
  seedIfEmpty(DB_KEYS.market, []);
  seedIfEmpty(DB_KEYS.tickets, []);
}

function getData(key){ return JSON.parse(localStorage.getItem(key) || '[]'); }
function setData(key, value){ localStorage.setItem(key, JSON.stringify(value)); }

function getSession(){ return JSON.parse(localStorage.getItem(DB_KEYS.session) || 'null'); }
function setSession(user){ localStorage.setItem(DB_KEYS.session, JSON.stringify(user)); }
function clearSession(){ localStorage.removeItem(DB_KEYS.session); }

function formatRupiah(n){ return 'Rp ' + Math.round(n).toLocaleString('id-ID'); }
function genId(prefix){ return prefix + '-' + Math.floor(1000 + Math.random()*9000); }
function slugStatus(s){ return String(s).toLowerCase().replace(/\s+/g,'-'); }

/* ===========================================================
   2. SHARED FUNCTIONS
=========================================================== */
function ensureToastWrap(){
  let wrap = document.getElementById('toast-wrap');
  if(!wrap){
    wrap = document.createElement('div');
    wrap.id = 'toast-wrap';
    document.body.appendChild(wrap);
  }
  return wrap;
}

function toast(message, type='info', icon){
  const wrap = ensureToastWrap();
  const el = document.createElement('div');
  el.className = 'toast ' + type;
  const ic = icon || (type==='success' ? 'bi-check-circle-fill' : type==='error' ? 'bi-x-circle-fill' : 'bi-info-circle-fill');
  const col = type==='success' ? 'var(--success)' : type==='error' ? 'var(--danger)' : 'var(--info)';
  el.innerHTML = `<i class="bi ${ic}" style="font-size:1.2rem;color:${col}"></i><span>${message}</span>`;
  wrap.appendChild(el);
  setTimeout(()=>{ el.style.transition='opacity .3s'; el.style.opacity='0'; setTimeout(()=>el.remove(),300); }, 3500);
}

function renderNavbar(active){
  const mount = document.getElementById('nav-mount');
  if(!mount) return;
  const session = getSession();
  const links = [
    {href:'/',            label:'Beranda',      key:'beranda'},
    {href:'/marketplace', label:'Marketplace',  key:'marketplace'},
    {href:'/booking',     label:'Booking',      key:'booking'},
    {href:'/mitra',       label:'Jadi Mitra',   key:'mitra'},
    {href:'/cs',          label:'Bantuan (CS)', key:'cs'},
  ];
  const linksHtml = links.map(l => `<a href="${l.href}" class="${active===l.key?'active':''}">${l.label}</a>`).join('');

  let actionsHtml = '';
  if(session){
    actionsHtml = `
      <a href="/dashboard" class="btn btn-secondary btn-sm"><i class="bi bi-speedometer2"></i> Dashboard</a>
      <button class="btn btn-ghost btn-sm" onclick="doLogout()"><i class="bi bi-box-arrow-right"></i> Keluar</button>`;
  } else {
    actionsHtml = `<a href="/login" class="btn btn-primary btn-sm"><i class="bi bi-person-circle"></i> Masuk / Daftar</a>`;
  }

  mount.innerHTML = `
  <nav class="nav-glass">
    <div class="nav-inner">
      <a href="/" class="brand">
        <span class="brand-mark"><i class="bi bi-camera-reels-fill"></i></span>
        <span>CamRent</span>
      </a>
      <div class="nav-links" id="navLinks">${linksHtml}</div>
      <div class="nav-actions">${actionsHtml}</div>
      <button class="nav-toggle" id="navToggle" aria-label="Buka menu"><i class="bi bi-list"></i></button>
    </div>
  </nav>`;

  const toggle = document.getElementById('navToggle');
  if(toggle){
    toggle.addEventListener('click', ()=>{
      document.getElementById('navLinks').classList.toggle('open');
    });
  }
}

function doLogout(){
  clearSession();
  toast('Kamu berhasil keluar.', 'info');
  setTimeout(()=> location.href = '/', 500);
}

function renderFooter(){
  const mount = document.getElementById('footer-mount');
  if(!mount) return;
  mount.innerHTML = `
  <footer class="footer">
    <div class="app-container">
      <div class="footer-grid">
        <div class="footer-col" style="max-width:260px;">
          <div class="brand" style="margin-bottom:10px;">
            <span class="brand-mark"><i class="bi bi-camera-reels-fill"></i></span><span>CamRent</span>
          </div>
          <p>Platform sewa & jual-beli peralatan kreatif — kamera, lensa, lighting, audio, hingga gimbal, dengan booking dan pembayaran online yang aman.</p>
        </div>
        <div class="footer-col">
          <h5>Layanan</h5>
          <a href="/marketplace">Marketplace</a>
          <a href="/booking">Booking Kamera</a>
          <a href="/mitra">Daftar Jadi Mitra</a>
        </div>
        <div class="footer-col">
          <h5>Dukungan</h5>
          <a href="/cs">Pusat Bantuan (CS)</a>
          <a href="/cs#faq">FAQ</a>
          <a href="/dashboard">Status Pesanan</a>
        </div>
        <div class="footer-col">
          <h5>Ikuti kami</h5>
          <div style="display:flex;gap:12px;font-size:1.2rem;color:var(--muted);">
            <i class="bi bi-instagram"></i><i class="bi bi-tiktok"></i><i class="bi bi-youtube"></i>
          </div>
        </div>
      </div>
      <div class="footer-bottom">
        <span>© 2026 CamRent. Seluruh hak cipta dilindungi.</span>
        <span>Dibuat untuk kreator Indonesia 🇮🇩</span>
      </div>
    </div>
  </footer>`;
}

function openModal(id){ const el = document.getElementById(id); if(el) el.classList.add('open'); }
function closeModal(id){ const el = document.getElementById(id); if(el) el.classList.remove('open'); }

function requireLogin(redirectMsg){
  const session = getSession();
  if(!session){
    toast(redirectMsg || 'Silakan masuk terlebih dahulu.', 'error');
    setTimeout(()=> location.href = '/login', 900);
    return null;
  }
  return session;
}

function fileToDataUrl(file){
  return new Promise((resolve, reject)=>{
    const reader = new FileReader();
    reader.onload = ()=> resolve(reader.result);
    reader.onerror = reject;
    reader.readAsDataURL(file);
  });
}

/* ===========================================================
   3. PAGE CONTROLLERS
=========================================================== */
const Pages = {

  /* ---------- HOME ---------- */
  home(){
    const products = getData(DB_KEYS.products);

    const heroStats = document.getElementById('heroStats');
    if(heroStats){
      heroStats.innerHTML = `
        <div class="stat"><b>${products.length}+</b><span>alat tersedia</span></div>
        <div class="stat"><b>${CREATORS.length * 12}+</b><span>kreator aktif</span></div>
        <div class="stat"><b>⭐ 4.9</b><span>rating rata-rata</span></div>
      `;
    }

    const heroMiniGrid = document.getElementById('heroMiniGrid');
    if(heroMiniGrid){
      heroMiniGrid.innerHTML = products.filter(p=>p.trending).slice(0,4).map(p => `
        <div class="hero-mini-card">
          ${p.image ? `<img class="hero-mini-photo" src="${p.image}" alt="${p.name}">` : `<i class="bi ${p.icon}"></i>`}
          <span style="font-weight:700;">${p.name}</span>
          <div class="mm-price"><span>${formatRupiah(p.pricePerDay)}/hari</span><span class="badge badge-rent">sewa</span></div>
        </div>`).join('');
    }

    const catGrid = document.getElementById('catGrid');
    if(catGrid){
      const categories = [...new Set(products.map(p=>p.category))];
      const catIcon = {Kamera:'bi-camera',Lensa:'bi-camera2',Lighting:'bi-lightbulb',Audio:'bi-mic',Gimbal:'bi-camera-reels',Tripod:'bi-camera-reels',Video:'bi-play-btn'};
      catGrid.innerHTML = categories.map(c => `
        <a href="/marketplace?category=${encodeURIComponent(c)}" class="cat-card">
          <i class="bi ${catIcon[c]||'bi-box'}"></i><span>${c}</span>
        </a>`).join('');
    }

    const productCardHtml = (p) => `
      <div class="product-card">
        ${p.image ? `<img class="pc-photo" src="${p.image}" alt="${p.name}">` : `<div class="pc-icon"><i class="bi ${p.icon}"></i></div>`}
        <div class="pc-name">${p.name}</div>
        <div class="pc-meta"><span>${p.category}</span><span class="rating"><i class="bi bi-star-fill"></i> ${p.rating}</span></div>
        <div class="pc-price-row">
          <div class="pc-price"><b>${formatRupiah(p.pricePerDay)}</b><div class="hint">per hari</div></div>
          <span class="badge badge-rent">sewa</span>
        </div>
        <div class="pc-actions">
          <a href="/booking?product=${p.id}" class="btn btn-primary btn-sm"><i class="bi bi-calendar-plus"></i> Booking</a>
          <a href="/marketplace?highlight=${p.id}" class="btn btn-secondary btn-sm"><i class="bi bi-eye"></i> Detail</a>
        </div>
      </div>`;

    const trendGrid = document.getElementById('trendGrid');
    if(trendGrid){
      const trend = products.filter(p=>p.trending).concat(products.filter(p=>!p.trending)).slice(0,8);
      trendGrid.innerHTML = trend.map(productCardHtml).join('');
    }

    const creatorGrid = document.getElementById('creatorGrid');
    if(creatorGrid){
      creatorGrid.innerHTML = CREATORS.map(c => `
        <div class="creator-card">
          <div class="creator-avatar"><i class="bi ${c.icon}"></i></div>
          <h5>${c.name}</h5>
          <div class="role">${c.role}</div>
          <div class="rating"><i class="bi bi-star-fill"></i> ${c.rating} · ${c.items} alat</div>
        </div>`).join('');
    }
  },

  /* ---------- LOGIN ---------- */
  login(){
    document.querySelectorAll('.login-tab').forEach(tab=>{
      tab.addEventListener('click', ()=>{
        document.querySelectorAll('.login-tab').forEach(t=>t.classList.remove('active'));
        document.querySelectorAll('.login-form').forEach(f=>f.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById(tab.dataset.tab === 'login' ? 'loginForm' : 'registerForm').classList.add('active');
      });
    });

    document.querySelectorAll('[data-role]').forEach(card=>{
      card.addEventListener('click', ()=>{
        document.querySelectorAll('[data-role]').forEach(c=>c.classList.remove('selected'));
        card.classList.add('selected');
      });
    });

    const loginForm = document.getElementById('loginForm');
    if(loginForm){
      loginForm.addEventListener('submit', e=>{
        e.preventDefault();
        const email = document.getElementById('loginEmail').value.trim().toLowerCase();
        const pass  = document.getElementById('loginPassword').value;
        const users = getData(DB_KEYS.users);
        const user  = users.find(u => u.email.toLowerCase() === email && u.password === pass);
        if(!user){ toast('Email atau kata sandi salah.', 'error'); return; }
        setSession(user);
        toast(`Selamat datang, ${user.name}!`, 'success');
        setTimeout(()=> location.href = '/dashboard', 700);
      });
    }

    const registerForm = document.getElementById('registerForm');
    if(registerForm){
      registerForm.addEventListener('submit', e=>{
        e.preventDefault();
        const email    = document.getElementById('regEmail').value.trim().toLowerCase();
        const password = document.getElementById('regPassword').value;
        if(password.length < 6){ toast('Kata sandi minimal 6 karakter.', 'error'); return; }
        const users = getData(DB_KEYS.users);
        if(users.some(u=>u.email.toLowerCase() === email)){ toast('Email sudah terdaftar. Silakan masuk.', 'error'); return; }
        const role = document.querySelector('input[name="regRole"]:checked').value;
        const newUser = {
          id: genId('u'),
          name: document.getElementById('regName').value.trim(),
          email, password,
          phone: document.getElementById('regPhone').value.trim(),
          role,
          status: role === 'mitra' ? 'Menunggu Verifikasi' : undefined
        };
        users.push(newUser);
        setData(DB_KEYS.users, users);
        setSession(newUser);
        toast('Akun berhasil dibuat!', 'success');
        setTimeout(()=> location.href = role === 'mitra' ? '/mitra' : '/dashboard', 700);
      });
    }
  },

  /* ---------- MARKETPLACE ---------- */
  marketplace(){
    const params = new URLSearchParams(location.search);
    const state = {
      q:'', category: params.get('category') || '', type: params.get('type') || '', sort:'trending'
    };

    function allItems(){
      const base = getData(DB_KEYS.products).map(p => ({...p, isUserListing:false}));
      const user = getData(DB_KEYS.market);
      return base.concat(user);
    }

    const catFilter = document.getElementById('categoryFilter');
    if(catFilter){
      const cats = [...new Set(allItems().map(p=>p.category))];
      cats.forEach(c=>{
        const opt = document.createElement('option');
        opt.value = c; opt.textContent = c;
        if(c === state.category) opt.selected = true;
        catFilter.appendChild(opt);
      });
    }
    const typeFilter = document.getElementById('typeFilter');
    if(typeFilter) typeFilter.value = state.type;

    function renderChips(){
      const row = document.getElementById('chipRow');
      if(!row) return;
      const cats = [...new Set(allItems().map(p=>p.category))];
      row.innerHTML = ['Semua', ...cats].map(c => {
        const val = c==='Semua' ? '' : c;
        return `<span class="mp-chip ${state.category===val?'active':''}" data-cat="${val}">${c}</span>`;
      }).join('');
      row.querySelectorAll('.mp-chip').forEach(chip=>{
        chip.addEventListener('click', ()=>{
          state.category = chip.dataset.cat;
          const cf = document.getElementById('categoryFilter');
          if(cf) cf.value = state.category;
          render();
        });
      });
    }

    function matches(p){
      const q = state.q.toLowerCase();
      const inQ = !q || p.name.toLowerCase().includes(q) || p.category.toLowerCase().includes(q) || (p.brand||'').toLowerCase().includes(q);
      const inCat = !state.category || p.category === state.category;
      const forSale = p.priceSale > 0;
      const forRent = p.pricePerDay > 0;
      let inType = true;
      if(state.type === 'sewa') inType = forRent;
      if(state.type === 'jual') inType = forSale;
      return inQ && inCat && inType;
    }

    function sortItems(items){
      switch(state.sort){
        case 'price-asc':  return items.sort((a,b)=> (a.pricePerDay||a.priceSale) - (b.pricePerDay||b.priceSale));
        case 'price-desc': return items.sort((a,b)=> (b.pricePerDay||b.priceSale) - (a.pricePerDay||a.priceSale));
        case 'rating':     return items.sort((a,b)=> (b.rating||0) - (a.rating||0));
        default:           return items.sort((a,b)=> (b.trending?1:0) - (a.trending?1:0));
      }
    }

    function cardHtml(p){
      const highlight = params.get('highlight') === p.id ? 'highlighted' : '';
      return `
      <div class="product-card ${highlight}" data-id="${p.id}">
        ${p.image ? `<img class="pc-photo" src="${p.image}" alt="${p.name}" onclick="showDetail('${p.id}')">` : `<div class="pc-icon"><i class="bi ${p.icon || 'bi-box-seam'}"></i></div>`}
        <div class="pc-name" onclick="showDetail('${p.id}')">${p.name}</div>
        <div class="pc-meta"><span>${p.category}</span>${p.rating?`<span class="rating"><i class="bi bi-star-fill"></i> ${p.rating}</span>`:'<span>Baru diajukan</span>'}</div>
        <div class="pc-seller"><i class="bi bi-shop-window"></i> ${p.seller || 'Kamu (menunggu verifikasi)'}</div>
        <div class="pc-price-row">
          <div class="pc-price"><b>${p.pricePerDay ? formatRupiah(p.pricePerDay) : (p.priceSale? formatRupiah(p.priceSale):'-')}</b><div class="hint">${p.pricePerDay ? 'per hari' : 'harga jual'}</div></div>
          ${p.priceSale && p.pricePerDay ? '<span class="badge badge-rent">sewa & jual</span>' : p.pricePerDay ? '<span class="badge badge-rent">sewa</span>' : '<span class="badge badge-buy">jual</span>'}
        </div>
        <div class="pc-actions">
          ${p.pricePerDay ? `<a href="/booking?product=${p.id}" class="btn btn-primary btn-sm"><i class="bi bi-calendar-plus"></i> Booking</a>` : ''}
          ${p.priceSale ? `<button class="btn btn-secondary btn-sm" onclick="openBuy('${p.id}')"><i class="bi bi-cart"></i> Beli</button>` : ''}
        </div>
      </div>`;
    }

    function render(){
      const items = sortItems(allItems().filter(matches));
      const grid = document.getElementById('marketGrid');
      if(grid) grid.innerHTML = items.map(cardHtml).join('');
      const empty = document.getElementById('emptyState');
      if(empty) empty.style.display = items.length ? 'none' : 'block';
      renderChips();
    }

    window.showDetail = function(id){
      const p = allItems().find(x=>x.id===id);
      if(!p) return;
      document.getElementById('modalDetailBody').innerHTML = `
        <button class="modal-close" onclick="closeModal('modalDetail')"><i class="bi bi-x-lg"></i></button>
        ${p.image ? `<img class="detail-photo" src="${p.image}" alt="${p.name}">` : ''}
        <div class="detail-head">
          ${p.image ? '' : `<div class="detail-icon"><i class="bi ${p.icon||'bi-box-seam'}"></i></div>`}
          <div>
            <h3>${p.name}</h3>
            <div class="pc-seller"><i class="bi bi-shop-window"></i> ${p.seller||'Kamu'} ${p.rating?`· <i class="bi bi-star-fill" style="color:#E0A419;"></i> ${p.rating} (${p.reviews||0} ulasan)`:''}</div>
          </div>
        </div>
        <p>${p.desc || 'Belum ada deskripsi tambahan dari penjual.'}</p>
        ${p.specs ? `<div class="detail-specs">${p.specs.map(s=>`<span class="tag"><i class="bi bi-check2"></i> ${s}</span>`).join('')}</div>` : ''}
        <div class="detail-price-box">
          ${p.pricePerHalfDay ? `<div class="dp-item"><b>${formatRupiah(p.pricePerHalfDay)}</b><span class="hint">/ 12 jam</span></div>`:''}
          ${p.pricePerDay ? `<div class="dp-item"><b>${formatRupiah(p.pricePerDay)}</b><span class="hint">/ hari</span></div>`:''}
          ${p.priceSale ? `<div class="dp-item"><b>${formatRupiah(p.priceSale)}</b><span class="hint">harga jual</span></div>`:''}
        </div>
        <div style="display:flex;gap:10px;">
          ${p.pricePerDay ? `<a href="/booking?product=${p.id}" class="btn btn-primary btn-block"><i class="bi bi-calendar-plus"></i> Booking sewa</a>` : ''}
          ${p.priceSale ? `<button class="btn btn-secondary btn-block" onclick="closeModal('modalDetail');openBuy('${p.id}')"><i class="bi bi-cart"></i> Beli sekarang</button>` : ''}
        </div>
      `;
      openModal('modalDetail');
    };

    window.openBuy = function(id){
      const p = allItems().find(x=>x.id===id);
      if(!p) return;
      document.getElementById('buyProductId').value = id;
      document.getElementById('buySummary').innerHTML = `<b>${p.name}</b> — ${formatRupiah(p.priceSale)}`;
      openModal('modalBuy');
    };

    const buyForm = document.getElementById('buyForm');
    if(buyForm){
      buyForm.addEventListener('submit', e=>{
        e.preventDefault();
        const session = getSession();
        const id = document.getElementById('buyProductId').value;
        const p = allItems().find(x=>x.id===id);
        if(!p) return;
        const order = {
          id: genId('ORD'), productId:id, productName:p.name, type:'Pembelian',
          buyer: document.getElementById('buyName').value,
          phone: document.getElementById('buyPhone').value,
          address: document.getElementById('buyAddress').value,
          paymentMethod: document.querySelector('input[name="buyPay"]:checked').value,
          total: p.priceSale, status:'Menunggu Konfirmasi',
          created: new Date().toISOString().slice(0,10)
        };
        const bookings = getData(DB_KEYS.bookings);
        bookings.push({...order, start:order.created, end:order.created, duration:'-', paymentType:'Bayar Penuh', guarantee:'-', dp:order.total, renter:order.buyer});
        setData(DB_KEYS.bookings, bookings);
        closeModal('modalBuy');
        toast('Pembelian berhasil dibuat! Cek status di Dashboard.', 'success');
        e.target.reset();
        setTimeout(()=> location.href = session ? '/dashboard' : '/', 900);
      });
    }

    const btnJual = document.getElementById('btnJual');
    if(btnJual){
      btnJual.addEventListener('click', ()=>{
        const session = requireLogin('Masuk dulu untuk menjual barangmu.');
        if(!session) return;
        openModal('modalJual');
      });
    }

    const jualFotoInput = document.getElementById('jualFoto');
    if(jualFotoInput){
      jualFotoInput.addEventListener('change', async ()=>{
        const preview = document.getElementById('jualFotoPreview');
        if(!preview) return;
        preview.innerHTML = '';
        const files = Array.from(jualFotoInput.files).slice(0,5);
        for(const file of files){
          const url = await fileToDataUrl(file);
          const img = document.createElement('img');
          img.src = url;
          preview.appendChild(img);
        }
      });
    }

    const jualForm = document.getElementById('jualForm');
    if(jualForm){
      jualForm.addEventListener('submit', async e=>{
        e.preventDefault();
        const session = getSession();
        const hargaJual = Number(document.getElementById('jualHargaJual').value) || 0;
        const hargaSewa = Number(document.getElementById('jualHargaSewa').value) || 0;
        if(hargaJual===0 && hargaSewa===0){ toast('Isi minimal salah satu harga (jual atau sewa).','error'); return; }
        if(!jualFotoInput.files || jualFotoInput.files.length === 0){
          toast('Unggah minimal 1 foto barang sebelum menayangkan.', 'error');
          return;
        }
        const photoUrls = await Promise.all(Array.from(jualFotoInput.files).slice(0,5).map(fileToDataUrl));
        const item = {
          id: genId('usr'),
          name: document.getElementById('jualNama').value,
          category: document.getElementById('jualKategori').value,
          icon: 'bi-box-seam',
          image: photoUrls[0],
          photos: photoUrls,
          priceSale: hargaJual, pricePerDay: hargaSewa, pricePerHalfDay: hargaSewa ? Math.round(hargaSewa*0.6) : 0,
          desc: document.getElementById('jualDesk').value,
          seller: session ? session.name : 'Kamu', rating:0, reviews:0, trending:false, stock:1
        };
        const market = getData(DB_KEYS.market);
        market.unshift(item);
        setData(DB_KEYS.market, market);
        closeModal('modalJual');
        toast('Barang berhasil ditayangkan di marketplace!', 'success');
        e.target.reset();
        document.getElementById('jualFotoPreview').innerHTML = '';
        render();
      });
    }

    const searchInput = document.getElementById('searchInput');
    if(searchInput) searchInput.addEventListener('input', e=>{ state.q = e.target.value; render(); });
    if(catFilter) catFilter.addEventListener('change', e=>{ state.category = e.target.value; render(); });
    if(typeFilter) typeFilter.addEventListener('change', e=>{ state.type = e.target.value; render(); });
    const sortFilter = document.getElementById('sortFilter');
    if(sortFilter) sortFilter.addEventListener('change', e=>{ state.sort = e.target.value; render(); });

    render();
  },

  /* ---------- BOOKING ---------- */
  booking(){
    const params = new URLSearchParams(location.search);
    const products = getData(DB_KEYS.products).filter(p => p.pricePerDay > 0)
      .concat(getData(DB_KEYS.market).filter(p => p.pricePerDay > 0));

    let selectedProduct = products.find(p=>p.id === params.get('product')) || products[0];
    const calDate = new Date();
    let startDate = null, endDate = null;
    let durType = 'full';
    let payType = 'DP';
    let payMethod = 'Cash';

    const sel = document.getElementById('productSelect');
    if(!sel) return;
    sel.innerHTML = products.map(p => `<option value="${p.id}">${p.name} — ${formatRupiah(p.pricePerDay)}/hari</option>`).join('');
    if(selectedProduct) sel.value = selectedProduct.id;

    sel.addEventListener('change', ()=>{
      selectedProduct = products.find(p=>p.id===sel.value);
      renderProductPreview();
      renderSummary();
    });

    function renderProductPreview(){
      const box = document.getElementById('productPreview');
      if(!box) return;
      if(!selectedProduct){ box.innerHTML = '<div class="empty-state"><i class="bi bi-camera"></i><p>Belum ada alat tersedia.</p></div>'; return; }
      const p = selectedProduct;
      box.innerHTML = `
        ${p.image ? `<img class="bpp-photo" src="${p.image}" alt="${p.name}">` : `<div class="bpp-icon"><i class="bi ${p.icon||'bi-box-seam'}"></i></div>`}
        <div>
          <div style="font-weight:800;">${p.name}</div>
          <div class="hint">${p.category} · Stok tersedia: ${p.stock ?? '-'}</div>
        </div>
        <div class="bpp-price">
          <div>${formatRupiah(p.pricePerHalfDay || Math.round(p.pricePerDay*0.6))} <span class="hint">/12 jam</span></div>
          <div>${formatRupiah(p.pricePerDay)} <span class="hint">/hari</span></div>
        </div>`;
    }

    const monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    function dateKey(d){ return d.toISOString().slice(0,10); }
    function sameDay(a,b){ return a && b && dateKey(a)===dateKey(b); }
    function formatDateID(d){ return d.toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'}); }

    function renderCalendar(){
      const year = calDate.getFullYear(), month = calDate.getMonth();
      const label = document.getElementById('calMonthLabel');
      if(label) label.textContent = `${monthNames[month]} ${year}`;

      const firstDay = new Date(year, month, 1);
      let startOffset = firstDay.getDay() - 1; if(startOffset < 0) startOffset = 6;
      const daysInMonth = new Date(year, month+1, 0).getDate();
      const grid = document.getElementById('calGrid');
      if(!grid) return;

      let html = '';
      for(let i=0;i<startOffset;i++) html += `<div class="cal-cell blank"></div>`;
      const today = new Date(); today.setHours(0,0,0,0);
      for(let d=1; d<=daysInMonth; d++){
        const cellDate = new Date(year, month, d); cellDate.setHours(0,0,0,0);
        const disabled = cellDate < today;
        let cls = 'cal-cell';
        if(disabled) cls += ' disabled';
        if(sameDay(cellDate, today)) cls += ' today';
        if(sameDay(cellDate, startDate)) cls += ' start';
        if(sameDay(cellDate, endDate)) cls += ' end';
        if(startDate && endDate && cellDate > startDate && cellDate < endDate) cls += ' in-range';
        html += `<div class="${cls}" data-date="${dateKey(cellDate)}">${d}</div>`;
      }
      grid.innerHTML = html;
      grid.querySelectorAll('.cal-cell:not(.blank):not(.disabled)').forEach(cell=>{
        cell.addEventListener('click', ()=> pickDate(new Date(cell.dataset.date)));
      });
    }

    function pickDate(d){
      d.setHours(0,0,0,0);
      if(!startDate || (startDate && endDate)){
        startDate = d; endDate = null;
      } else if(d < startDate){
        startDate = d;
      } else {
        endDate = d;
      }
      const dc = document.getElementById('durationChoice');
      if(dc) dc.style.display = (startDate && (!endDate || sameDay(startDate,endDate))) ? 'block' : 'none';
      renderCalendar();
      renderPicked();
      renderSummary();
    }

    function renderPicked(){
      const box = document.getElementById('bkPicked');
      if(!box) return;
      if(!startDate){ box.textContent = 'Belum ada tanggal dipilih. Klik tanggal mulai, lalu tanggal selesai.'; return; }
      if(!endDate){ box.innerHTML = `<i class="bi bi-calendar-event"></i> Mulai: <b>${formatDateID(startDate)}</b> — pilih tanggal selesai (atau klik tanggal yang sama untuk 12 jam / 1 hari).`; return; }
      box.innerHTML = `<i class="bi bi-calendar-range"></i> Sewa: <b>${formatDateID(startDate)}</b> s/d <b>${formatDateID(endDate)}</b>`;
    }

    const calPrev = document.getElementById('calPrev');
    const calNext = document.getElementById('calNext');
    if(calPrev) calPrev.addEventListener('click', ()=>{ calDate.setMonth(calDate.getMonth()-1); renderCalendar(); });
    if(calNext) calNext.addEventListener('click', ()=>{ calDate.setMonth(calDate.getMonth()+1); renderCalendar(); });

    document.querySelectorAll('#durationChoice .radio-card').forEach(card=>{
      card.addEventListener('click', ()=>{
        document.querySelectorAll('#durationChoice .radio-card').forEach(c=>c.classList.remove('selected'));
        card.classList.add('selected');
        durType = card.dataset.dur;
        renderSummary();
      });
    });

    document.querySelectorAll('[data-pay]').forEach(card=>{
      card.addEventListener('click', ()=>{
        document.querySelectorAll('[data-pay]').forEach(c=>c.classList.remove('selected'));
        card.classList.add('selected');
        payType = card.dataset.pay;
        renderSummary();
      });
    });
    document.querySelectorAll('[data-method]').forEach(card=>{
      card.addEventListener('click', ()=>{
        document.querySelectorAll('[data-method]').forEach(c=>c.classList.remove('selected'));
        card.classList.add('selected');
        payMethod = card.dataset.method;
        renderPaymentInfo();
        renderSummary();
      });
    });

    function renderPaymentInfo(){
      const box = document.getElementById('paymentInfoBox');
      if(!box) return;
      if(payMethod === 'Cash'){
        box.innerHTML = `<i class="bi bi-geo-alt"></i> Bayar tunai langsung saat pengambilan alat di lokasi mitra. Bawa jaminan yang telah dipilih.`;
      } else if(payMethod === 'Transfer'){
        box.innerHTML = `
          <i class="bi bi-bank"></i> Transfer ke rekening berikut, lalu konfirmasi via CS.<br>
          <b>BCA 123 456 7890</b> a.n. CamRent Indonesia<br>
          <b>Mandiri 987 654 3210</b> a.n. CamRent Indonesia`;
      } else {
        box.innerHTML = `
          <div class="qris-box">
            <div class="qris-visual"></div>
            <div><i class="bi bi-qr-code-scan"></i> Scan kode QRIS ini dengan e-wallet / m-banking apapun untuk membayar otomatis.</div>
          </div>`;
      }
    }

    function computePrice(){
      if(!selectedProduct || !startDate) return null;
      const end = endDate || startDate;
      const isSameDay = sameDay(startDate, end);
      let duration, subtotal;
      if(isSameDay){
        if(durType === 'half'){
          duration = '12 Jam';
          subtotal = selectedProduct.pricePerHalfDay || Math.round(selectedProduct.pricePerDay*0.6);
        } else {
          duration = '1 Hari';
          subtotal = selectedProduct.pricePerDay;
        }
      } else {
        const days = Math.round((end - startDate)/(1000*60*60*24)) + 1;
        duration = days + ' Hari';
        subtotal = selectedProduct.pricePerDay * days;
      }
      const dp = Math.round(subtotal * 0.3);
      return {duration, subtotal, dp, end};
    }

    function renderSummary(){
      const body = document.getElementById('summaryBody');
      if(!body) return;
      const price = computePrice();
      if(!price){
        body.innerHTML = `<div class="sum-empty"><i class="bi bi-calendar-x" style="font-size:1.6rem;display:block;margin-bottom:6px;"></i>Pilih alat & tanggal untuk melihat ringkasan harga.</div>`;
        return;
      }
      const payNow = payType === 'DP' ? price.dp : price.subtotal;
      body.innerHTML = `
        <div class="sum-row"><span>Alat</span><span>${selectedProduct.name}</span></div>
        <div class="sum-row"><span>Durasi</span><span>${price.duration}</span></div>
        <div class="sum-row"><span>Tanggal</span><span>${formatDateID(startDate)}${!sameDay(startDate,price.end) ? ' – '+formatDateID(price.end) : ''}</span></div>
        <div class="sum-row"><span>Skema bayar</span><span>${payType}</span></div>
        <div class="sum-row"><span>Metode</span><span>${payMethod}</span></div>
        <div class="sum-row"><span>Total sewa</span><span>${formatRupiah(price.subtotal)}</span></div>
        <div class="sum-row total"><span>Bayar sekarang</span><span>${formatRupiah(payNow)}</span></div>
        ${payType==='DP' ? `<div class="hint">Sisa ${formatRupiah(price.subtotal-price.dp)} dibayar saat pengambilan alat.</div>` : ''}
      `;
    }

    const submitBtn = document.getElementById('submitBooking');
    if(submitBtn){
      submitBtn.addEventListener('click', ()=>{
        const session = getSession();
        if(!selectedProduct){ toast('Pilih alat terlebih dahulu.', 'error'); return; }
        if(!startDate){ toast('Pilih tanggal sewa terlebih dahulu.', 'error'); return; }
        const name = document.getElementById('renterName').value.trim();
        const phone = document.getElementById('renterPhone').value.trim();
        if(!name || !phone){ toast('Lengkapi nama dan no. HP penyewa.', 'error'); return; }

        const price = computePrice();
        const booking = {
          id: genId('BKG'),
          productId: selectedProduct.id,
          productName: selectedProduct.name,
          renter: name,
          phone: phone,
          city: document.getElementById('renterCity').value.trim(),
          note: document.getElementById('renterNote').value.trim(),
          start: dateKey(startDate),
          end: dateKey(price.end),
          duration: price.duration,
          paymentType: payType,
          paymentMethod: payMethod,
          guarantee: document.getElementById('guaranteeSelect').value,
          total: price.subtotal,
          dp: payType === 'DP' ? price.dp : price.subtotal,
          status: 'Menunggu Konfirmasi',
          created: new Date().toISOString().slice(0,10)
        };
        const bookings = getData(DB_KEYS.bookings);
        bookings.unshift(booking);
        setData(DB_KEYS.bookings, bookings);
        toast(`Booking ${booking.id} berhasil dibuat! Menunggu konfirmasi mitra.`, 'success');
        setTimeout(()=> location.href = session ? '/dashboard' : '/login', 1100);
      });
    }

    renderProductPreview();
    renderCalendar();
    renderPicked();
    renderPaymentInfo();
    renderSummary();
  },

  /* ---------- CS ---------- */
  cs(){
    const session = getSession();
    const params = new URLSearchParams(location.search);

    const bookingSelect = document.getElementById('csBooking');
    if(bookingSelect){
      const myBookings = getData(DB_KEYS.bookings).filter(b => !session || b.renter === session.name || b.buyer === session.name);
      myBookings.forEach(b=>{
        const opt = document.createElement('option');
        opt.value = b.id;
        opt.textContent = `${b.id} — ${b.productName}`;
        bookingSelect.appendChild(opt);
      });
      if(params.get('booking')) bookingSelect.value = params.get('booking');
    }
    if(session){
      const csName = document.getElementById('csName');
      const csContact = document.getElementById('csContact');
      if(csName) csName.value = session.name;
      if(csContact) csContact.value = session.phone || session.email;
    }

    const csForm = document.getElementById('csForm');
    if(csForm){
      csForm.addEventListener('submit', e=>{
        e.preventDefault();
        const ticket = {
          id: genId('TCK'),
          category: document.getElementById('csCategory').value,
          bookingId: bookingSelect ? (bookingSelect.value || null) : null,
          name: document.getElementById('csName').value.trim(),
          contact: document.getElementById('csContact').value.trim(),
          desc: document.getElementById('csDesc').value.trim(),
          status: 'Diproses',
          created: new Date().toISOString().slice(0,10)
        };
        const tickets = getData(DB_KEYS.tickets);
        tickets.unshift(ticket);
        setData(DB_KEYS.tickets, tickets);
        toast(`Pengaduan ${ticket.id} terkirim. Tim CS akan menghubungimu segera.`, 'success');
        e.target.reset();
        if(session){
          document.getElementById('csName').value = session.name;
          document.getElementById('csContact').value = session.phone || session.email;
        }
        renderTickets();
      });
    }

    function renderTickets(){
      const list = document.getElementById('ticketList');
      if(!list) return;
      const tickets = getData(DB_KEYS.tickets).filter(t => !session || t.name === session.name);
      list.innerHTML = tickets.length ? tickets.map(t => `
        <div class="ticket-item">
          <div class="ti-top"><span>${t.id}</span><span class="status-pill status-${slugStatus(t.status)}">${t.status}</span></div>
          <div>${t.category}${t.bookingId ? ' · '+t.bookingId : ''}</div>
          <div class="hint">${t.desc.slice(0,70)}${t.desc.length>70?'...':''}</div>
        </div>`).join('') : `<div class="empty-state" style="padding:24px 10px;"><i class="bi bi-inbox" style="font-size:1.6rem;"></i><p>Belum ada tiket pengaduan.</p></div>`;
    }
    renderTickets();

    const FAQS = [
      {q:'Bagaimana cara membatalkan atau mengubah booking?', a:'Buka menu Dashboard > Riwayat Sewa & Beli, lalu klik "Ada masalah?" pada pesanan terkait untuk mengajukan perubahan atau pembatalan lewat CS.'},
      {q:'Apa bedanya DP dan Bayar Penuh?', a:'DP berarti kamu membayar 30% di awal dan sisanya saat pengambilan alat. Bayar Penuh berarti seluruh biaya sewa dilunasi saat booking dibuat.'},
      {q:'Jaminan apa saja yang bisa digunakan?', a:'Kamu bisa menitipkan KTP, SIM, atau Paspor asli sebagai jaminan. Jaminan akan dikembalikan penuh saat alat dikembalikan dalam kondisi baik.'},
      {q:'Metode pembayaran apa saja yang tersedia?', a:'CamRent mendukung pembayaran Cash (saat pengambilan), Transfer bank, dan QRIS untuk kemudahan transaksi digital.'},
      {q:'Bagaimana jika alat yang diterima rusak?', a:'Segera laporkan lewat form pengaduan di halaman ini dengan kategori "Alat rusak / tidak sesuai" beserta nomor pesanan, tim kami akan menindaklanjuti dalam 1x24 jam.'},
      {q:'Bagaimana cara menjadi mitra penyedia alat?', a:'Kunjungi halaman "Jadi Mitra" di menu navigasi, isi formulir pendaftaran, dan tim kami akan memverifikasi dalam 1x24 jam.'},
    ];
    const faqAcc = document.getElementById('faqAccordion');
    if(faqAcc){
      faqAcc.innerHTML = FAQS.map((f,i) => `
        <div class="faq-item" id="faq-${i}">
          <div class="faq-q" onclick="toggleFaq(${i})"><span>${f.q}</span><i class="bi bi-chevron-down"></i></div>
          <div class="faq-a"><p>${f.a}</p></div>
        </div>`).join('');
    }
    window.toggleFaq = function(i){
      const el = document.getElementById('faq-'+i);
      if(el) el.classList.toggle('open');
    };

    const chatWindow = document.getElementById('chatWindow');
    function addChat(text, who){
      if(!chatWindow) return;
      const el = document.createElement('div');
      el.className = 'chat-msg ' + who;
      el.textContent = text;
      chatWindow.appendChild(el);
      chatWindow.scrollTop = chatWindow.scrollHeight;
    }
    addChat('Halo! Saya asisten CamRent. Ada yang bisa saya bantu terkait booking, pembayaran, atau alat sewa?', 'bot');

    function autoReply(msg){
      const m = msg.toLowerCase();
      if(m.includes('batal')) return 'Untuk pembatalan, silakan isi form pengaduan di sebelah kiri dengan kategori "Salah booking / tanggal" ya.';
      if(m.includes('bayar') || m.includes('qris') || m.includes('transfer')) return 'Kami mendukung pembayaran Cash, Transfer, dan QRIS. Jika transaksimu gagal, sertakan nomor pesanan di form pengaduan.';
      if(m.includes('rusak')) return 'Mohon maaf atas kendalanya. Silakan laporkan lewat form pengaduan kategori "Alat rusak / tidak sesuai" agar segera ditindaklanjuti.';
      if(m.includes('mitra')) return 'Untuk jadi mitra, kunjungi halaman "Jadi Mitra" di menu atas dan isi formulir pendaftarannya.';
      return 'Terima kasih, pesanmu sudah kami catat. Untuk respons lebih cepat, silakan lengkapi form pengaduan di sebelah kiri.';
    }

    const chatForm = document.getElementById('chatForm');
    if(chatForm){
      chatForm.addEventListener('submit', e=>{
        e.preventDefault();
        const input = document.getElementById('chatInput');
        const val = input.value.trim();
        if(!val) return;
        addChat(val, 'me');
        input.value = '';
        setTimeout(()=> addChat(autoReply(val), 'bot'), 500);
      });
    }
  },

  /* ---------- DASHBOARD ---------- */
  dashboard(){
    const session = requireLogin('Masuk untuk melihat dashboard kamu.');
    if(!session) return;

    const isMitra = session.role === 'mitra';

    document.getElementById('dashAvatar').textContent = session.name.slice(0,2).toUpperCase();
    document.getElementById('dashName').textContent = session.name;
    document.getElementById('dashRole').textContent = isMitra ? 'Akun Mitra / Penyedia Alat' : 'Akun Pengguna';
    if(isMitra){
      const st = session.status || 'Terverifikasi';
      document.getElementById('dashStatusBadge').innerHTML = `<span class="status-pill status-${slugStatus(st)}">${st}</span>`;
    }

    const tabs = [{key:'riwayat', label:'Riwayat Sewa & Beli', icon:'bi-clock-history'}];
    if(isMitra) tabs.push({key:'toko', label:'Toko Saya', icon:'bi-shop'});
    tabs.push({key:'profil', label:'Profil', icon:'bi-person'});

    document.getElementById('dashTabs').innerHTML = tabs.map((t,i)=>
      `<div class="dash-tab ${i===0?'active':''}" data-tab="${t.key}"><i class="bi ${t.icon}"></i> ${t.label}</div>`
    ).join('');

    document.querySelectorAll('.dash-tab').forEach(tab=>{
      tab.addEventListener('click', ()=>{
        document.querySelectorAll('.dash-tab').forEach(t=>t.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById('panelRiwayat').style.display = tab.dataset.tab === 'riwayat' ? 'block':'none';
        document.getElementById('panelToko').style.display    = tab.dataset.tab === 'toko'    ? 'block':'none';
        document.getElementById('panelProfil').style.display  = tab.dataset.tab === 'profil'  ? 'block':'none';
      });
    });

    function statusIcon(s){
      return {
        'Selesai':'bi-check-circle-fill','Dikonfirmasi':'bi-hourglass-split','Menunggu Konfirmasi':'bi-clock-fill',
        'Ditolak':'bi-x-circle-fill','Batal':'bi-slash-circle-fill'
      }[s] || 'bi-info-circle-fill';
    }

    function renderRiwayat(){
      const bookings = getData(DB_KEYS.bookings).filter(b => b.renter === session.name || b.buyer === session.name);
      const list = document.getElementById('riwayatList');
      if(!list) return;
      if(!bookings.length){
        list.innerHTML = `<div class="empty-state"><i class="bi bi-inbox"></i><p>Belum ada riwayat sewa atau pembelian. Yuk mulai booking!</p><a href="/booking" class="btn btn-primary" style="margin-top:10px;">Booking sekarang</a></div>`;
        return;
      }
      list.innerHTML = bookings.map(b => `
        <div class="order-item">
          <div class="order-icon"><i class="bi ${b.type==='Pembelian' ? 'bi-bag-check':'bi-camera-reels'}"></i></div>
          <div class="order-info">
            <div class="oi-title">${b.productName} <span class="hint">· ${b.id}</span></div>
            <div class="oi-meta">${b.start}${b.end && b.end!==b.start ? ' – '+b.end : ''} · ${b.duration || '-'} · ${b.paymentMethod}</div>
          </div>
          <div class="order-price">
            ${formatRupiah(b.total)}
            <div><span class="status-pill status-${slugStatus(b.status)}"><i class="bi ${statusIcon(b.status)}"></i> ${b.status}</span></div>
          </div>
          <div class="order-actions">
            <button class="btn btn-ghost btn-sm" onclick="showBookingDetail('${b.id}')"><i class="bi bi-eye"></i> Detail</button>
            <a href="/cs?booking=${b.id}" class="btn btn-secondary btn-sm"><i class="bi bi-headset"></i> Ada masalah?</a>
          </div>
        </div>`).join('');
    }

    window.showBookingDetail = function(id){
      const b = getData(DB_KEYS.bookings).find(x=>x.id===id);
      if(!b) return;
      document.getElementById('modalBookingDetailBody').innerHTML = `
        <button class="modal-close" onclick="closeModal('modalBookingDetail')"><i class="bi bi-x-lg"></i></button>
        <h3 style="margin-bottom:14px;">${b.productName}</h3>
        <div class="profil-row"><span>ID Pesanan</span><span>${b.id}</span></div>
        <div class="profil-row"><span>Nama</span><span>${b.renter || b.buyer}</span></div>
        <div class="profil-row"><span>No. HP</span><span>${b.phone||'-'}</span></div>
        <div class="profil-row"><span>Tanggal</span><span>${b.start}${b.end && b.end!==b.start ? ' – '+b.end:''}</span></div>
        <div class="profil-row"><span>Durasi</span><span>${b.duration||'-'}</span></div>
        <div class="profil-row"><span>Skema bayar</span><span>${b.paymentType||'-'}</span></div>
        <div class="profil-row"><span>Metode bayar</span><span>${b.paymentMethod}</span></div>
        <div class="profil-row"><span>Jaminan</span><span>${b.guarantee||'-'}</span></div>
        <div class="profil-row"><span>Total</span><span>${formatRupiah(b.total)}</span></div>
        <div class="profil-row"><span>Dibayar</span><span>${formatRupiah(b.dp)}</span></div>
        <div class="profil-row"><span>Status</span><span class="status-pill status-${slugStatus(b.status)}">${b.status}</span></div>
      `;
      openModal('modalBookingDetail');
    };

    function renderToko(){
      if(!isMitra) return;
      const allProducts = getData(DB_KEYS.products).concat(getData(DB_KEYS.market));
      const myProducts = allProducts.filter(p => p.seller === session.name);
      const myProductIds = new Set(myProducts.map(p=>p.id));
      const orders = getData(DB_KEYS.bookings).filter(b => myProductIds.has(b.productId));

      const orderList = document.getElementById('tokoOrderList');
      if(orderList){
        orderList.innerHTML = orders.length ? orders.map(b => `
          <div class="order-item">
            <div class="order-icon"><i class="bi bi-person-check"></i></div>
            <div class="order-info">
              <div class="oi-title">${b.productName} <span class="hint">· ${b.id}</span></div>
              <div class="oi-meta">Penyewa: ${b.renter || b.buyer} · ${b.phone||''} · ${b.start}${b.end && b.end!==b.start?' – '+b.end:''}</div>
            </div>
            <div class="order-price">
              ${formatRupiah(b.total)}
              <div><span class="status-pill status-${slugStatus(b.status)}">${b.status}</span></div>
            </div>
            <div class="order-actions">
              ${b.status === 'Menunggu Konfirmasi' ? `
                <button class="btn btn-primary btn-sm" onclick="updateOrderStatus('${b.id}','Dikonfirmasi')"><i class="bi bi-check-lg"></i> Konfirmasi</button>
                <button class="btn btn-danger btn-sm" onclick="updateOrderStatus('${b.id}','Ditolak')"><i class="bi bi-x-lg"></i> Tolak</button>` : ''}
              ${b.status === 'Dikonfirmasi' ? `<button class="btn btn-primary btn-sm" onclick="updateOrderStatus('${b.id}','Selesai')"><i class="bi bi-check2-all"></i> Tandai Selesai</button>` : ''}
            </div>
          </div>`).join('') : `<div class="empty-state"><i class="bi bi-inbox"></i><p>Belum ada pesanan masuk ke toko kamu.</p></div>`;
      }

      const productList = document.getElementById('tokoProductList');
      if(productList){
        productList.innerHTML = myProducts.length ? myProducts.map(p => `
          <div class="product-card" style="background:rgba(255,255,255,.62);border:1px solid var(--glass-border);border-radius:20px;padding:16px;">
            ${p.image ? `<img class="pc-photo" src="${p.image}" alt="${p.name}">` : `<div class="pc-icon"><i class="bi ${p.icon||'bi-box-seam'}"></i></div>`}
            <div class="pc-name">${p.name}</div>
            <div class="hint">${p.category} · Stok ${p.stock ?? '-'}</div>
            <div class="pc-price-row"><b>${p.pricePerDay?formatRupiah(p.pricePerDay)+'/hari':formatRupiah(p.priceSale)}</b></div>
          </div>`).join('') : `<div class="empty-state"><i class="bi bi-box-seam"></i><p>Belum ada produk terdaftar.</p></div>`;
      }
    }

    window.updateOrderStatus = function(id, status){
      const bookings = getData(DB_KEYS.bookings);
      const idx = bookings.findIndex(b=>b.id===id);
      if(idx>-1){
        bookings[idx].status = status;
        setData(DB_KEYS.bookings, bookings);
        toast(`Status pesanan ${id} diperbarui menjadi "${status}".`, 'success');
        renderToko();
        renderRiwayat();
      }
    };

    function renderProfil(){
      const body = document.getElementById('profilBody');
      if(!body) return;
      body.innerHTML = `
        <div class="profil-row"><span>Nama</span><span>${session.name}</span></div>
        <div class="profil-row"><span>Email</span><span>${session.email}</span></div>
        <div class="profil-row"><span>No. HP</span><span>${session.phone||'-'}</span></div>
        <div class="profil-row"><span>Peran</span><span>${isMitra?'Mitra':'User'}</span></div>
        ${isMitra ? `<div class="profil-row"><span>Status verifikasi</span><span class="status-pill status-${slugStatus(session.status||'Terverifikasi')}">${session.status||'Terverifikasi'}</span></div>`:''}
      `;
    }

    renderRiwayat();
    renderToko();
    renderProfil();
  },

  /* ---------- MITRA ---------- */
  mitra(){
    const mFotoInput = document.getElementById('mFoto');
    if(mFotoInput){
      mFotoInput.addEventListener('change', async ()=>{
        const preview = document.getElementById('mFotoPreview');
        if(!preview) return;
        preview.innerHTML = '';
        const files = Array.from(mFotoInput.files).slice(0,5);
        for(const file of files){
          const url = await fileToDataUrl(file);
          const img = document.createElement('img');
          img.src = url;
          preview.appendChild(img);
        }
      });
    }

    const mitraForm = document.getElementById('mitraForm');
    if(mitraForm){
      mitraForm.addEventListener('submit', async e=>{
        e.preventDefault();
        const nik = document.getElementById('mNik').value.trim();
        if(nik.length !== 16 || isNaN(nik)){ toast('NIK harus 16 digit angka.', 'error'); return; }
        if(!mFotoInput.files || mFotoInput.files.length === 0){
          toast('Unggah minimal 1 foto alat sebelum mendaftar sebagai mitra.', 'error');
          return;
        }

        const users = getData(DB_KEYS.users);
        const email = document.getElementById('mEmail').value.trim().toLowerCase();
        if(users.some(u=>u.email.toLowerCase() === email)){ toast('Email sudah terdaftar. Silakan masuk.', 'error'); return; }

        const photoUrls = await Promise.all(Array.from(mFotoInput.files).slice(0,5).map(fileToDataUrl));

        const newMitra = {
          id: genId('m'),
          name: document.getElementById('mNama').value.trim(),
          owner: document.getElementById('mPemilik').value.trim(),
          email,
          password: 'mitra123',
          phone: document.getElementById('mPhone').value.trim(),
          nik,
          category: document.getElementById('mKategori').value,
          city: document.getElementById('mKota').value.trim(),
          bank: document.getElementById('mRekening').value.trim(),
          role: 'mitra',
          status: 'Menunggu Verifikasi',
          sampleImage: photoUrls[0],
          sampleImages: photoUrls
        };
        users.push(newMitra);
        setData(DB_KEYS.users, users);
        setSession(newMitra);
        toast('Pendaftaran mitra berhasil! Akun sedang diverifikasi.', 'success');
        setTimeout(()=> location.href = '/dashboard', 1000);
      });
    }
  }
};

/* ===========================================================
   4. BOOTSTRAP
=========================================================== */
document.addEventListener('DOMContentLoaded', ()=>{
  initData();

  const page = document.body.dataset.page;
  renderNavbar(page);
  renderFooter();

  if(page && typeof Pages[page] === 'function'){
    Pages[page]();
  }
});