const fs = require('fs');
const path = require('path');

const css = `/* =====================================================
   StayFinder PH -- Cozy & Homely Design System
   Warm Earth Tones: Cream, Terracotta, Sage, Amber
   ===================================================== */

:root {
  --cream:         #FDFBF7;
  --cream-deep:    #F4EFE4;
  --cream-border:  #E8DFD0;
  --warm-brown:    #7C5C3E;
  --terracotta:    #C8714A;
  --terracotta-lt: #F2A882;
  --sage:          #6B8F71;
  --sage-lt:       #98BEA0;
  --amber:         #D4852A;
  --amber-lt:      #F5C97A;
  --mocha:         #4A3728;
  --charcoal:      #2C2016;
  --bg-body:       #FDFBF7;
  --bg-surface:    #F4EFE4;
  --bg-card:       #FFFFFF;
  --border:        #E8DFD0;
  --text-primary:  #4A3728;
  --text-secondary:#7A6654;
  --text-muted:    #A3917E;
  --shadow-sm:     0 2px 8px rgba(76,55,40,0.08);
  --shadow-md:     0 6px 24px rgba(76,55,40,0.12);
  --shadow-lg:     0 16px 48px rgba(76,55,40,0.18);
  --r-sm: 12px;
  --r-md: 18px;
  --r-lg: 24px;
  --r-xl: 32px;
}

*,*::before,*::after{box-sizing:border-box}

html,body{
  height:100%;margin:0;padding:0;
  background:var(--cream);color:var(--text-primary);
  font-family:'Plus Jakarta Sans',system-ui,sans-serif;
  overflow-x:hidden;
}

@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Lora:ital,wght@0,600;1,400&display=swap');

[v-cloak]{display:none!important}

#app{
  height:100vh;min-height:100vh;
  display:flex;flex-direction:column;
  overflow:hidden;background:var(--cream);
}
@media(max-width:991.98px){#app{height:auto;overflow:visible}}

/* Bootstrap overrides */
.text-white{color:var(--text-primary)!important}
.text-light{color:var(--text-secondary)!important}
.text-white-50{color:var(--text-muted)!important}
.text-secondary{color:var(--text-secondary)!important}
.text-info{color:var(--sage)!important}
.text-primary{color:var(--terracotta)!important}
.text-warning{color:var(--amber)!important}
.text-success{color:var(--sage)!important}
.text-danger{color:#D95F5F!important}
.bg-dark{background:var(--bg-surface)!important}
.bg-primary{background:var(--terracotta)!important}
.bg-success{background:var(--sage)!important}
.border-secondary,.border-opacity-25,.border-opacity-30,.border-opacity-50{border-color:var(--border)!important}
.badge.bg-secondary{background:var(--cream-border)!important;color:var(--text-secondary)!important}

.btn-primary{
  background:var(--terracotta)!important;border-color:var(--terracotta)!important;
  color:#fff!important;font-weight:600;border-radius:var(--r-sm)!important;
  box-shadow:0 3px 12px rgba(200,113,74,0.35)!important;
  transition:transform .18s,box-shadow .18s;
}
.btn-primary:hover{background:#B5603E!important;transform:translateY(-1px);box-shadow:0 6px 20px rgba(200,113,74,0.45)!important}
.btn-outline-info{border-color:var(--sage)!important;color:var(--sage)!important;border-radius:var(--r-sm)!important}
.btn-outline-info:hover{background:var(--sage)!important;color:#fff!important}
.btn-outline-secondary{border-color:var(--cream-border)!important;color:var(--text-secondary)!important;border-radius:var(--r-sm)!important}
.btn-outline-warning{border-color:var(--amber)!important;color:var(--amber)!important;border-radius:var(--r-sm)!important}

.dropdown-menu,.dropdown-menu-dark{
  background:#fff!important;border:1px solid var(--border)!important;
  border-radius:var(--r-md)!important;box-shadow:var(--shadow-md)!important;
  color:var(--text-primary)!important;
}
.dropdown-item{color:var(--text-primary)!important}
.dropdown-item:hover{background:var(--cream-deep)!important}
.dropdown-divider{border-color:var(--border)!important}

.form-control,.form-select{
  background:#fff!important;border-color:var(--border)!important;
  color:var(--text-primary)!important;border-radius:var(--r-sm)!important;
}
.form-control:focus,.form-select:focus{
  border-color:var(--terracotta)!important;
  box-shadow:0 0 0 3px rgba(200,113,74,0.18)!important;outline:none!important;
}
.form-control::placeholder{color:var(--text-muted)!important}
.input-group-text{background:var(--cream-deep)!important;border-color:var(--border)!important;color:var(--text-secondary)!important}

/* Header */
.app-header{
  background:#fff;border-bottom:1px solid var(--border);
  padding:14px 0;position:sticky;top:0;z-index:1050;
  box-shadow:0 2px 12px rgba(76,55,40,0.07);
}
.brand-logo-badge{
  width:42px;height:42px;border-radius:14px;
  background:linear-gradient(135deg,var(--terracotta),var(--amber));
  display:flex;align-items:center;justify-content:center;
  color:#fff;font-size:1.3rem;box-shadow:0 4px 14px rgba(200,113,74,0.4);
}
.btn-nav-pill{
  display:inline-flex;align-items:center;gap:6px;
  padding:8px 16px;border-radius:50px;font-size:0.82rem;font-weight:600;
  cursor:pointer;border:1.5px solid var(--border);background:#fff;
  color:var(--text-secondary);transition:all .18s ease;text-decoration:none;
}
.btn-nav-pill:hover,.btn-nav-pill.active{
  background:var(--terracotta);border-color:var(--terracotta);
  color:#fff;box-shadow:0 3px 12px rgba(200,113,74,0.3);transform:translateY(-1px);
}

/* Filter bar */
.filter-bar{
  background:#fff;border-bottom:1px solid var(--border);
  padding:12px 0;position:sticky;top:71px;z-index:1040;
  box-shadow:0 2px 8px rgba(76,55,40,0.05);
}
.search-input-box{
  padding:9px 12px 9px 38px!important;border-radius:50px!important;
  border:1.5px solid var(--border)!important;background:var(--cream-deep)!important;
  color:var(--text-primary)!important;font-size:0.85rem;transition:all .2s;
}
.search-input-box:focus{
  background:#fff!important;border-color:var(--terracotta)!important;
  box-shadow:0 0 0 3px rgba(200,113,74,0.15)!important;
}
.pill-filter-btn{
  display:inline-flex;align-items:center;gap:5px;padding:7px 14px;
  border-radius:50px;font-size:0.8rem;font-weight:600;
  border:1.5px solid var(--border);background:var(--cream-deep);
  color:var(--text-secondary);cursor:pointer;transition:all .2s ease;white-space:nowrap;
}
.pill-filter-btn:hover{border-color:var(--terracotta);color:var(--terracotta);background:rgba(200,113,74,0.06)}
.pill-filter-btn.active{background:var(--terracotta);border-color:var(--terracotta);color:#fff!important;box-shadow:0 3px 12px rgba(200,113,74,0.3)}
.pill-filter-btn.active .bi{color:#fff!important}
.pill-filter-btn.active .badge{background:rgba(255,255,255,0.25)!important}
.filter-bar .form-select{background:var(--cream-deep)!important;border:1.5px solid var(--border)!important;color:var(--text-primary)!important;border-radius:var(--r-sm)!important;font-size:0.82rem}

/* Layout */
.main-workspace{flex:1;overflow:hidden;min-height:0}
.cards-pane{
  height:100%;overflow-y:auto;padding:18px 16px 0 16px;
  background:var(--cream-deep);scrollbar-width:thin;
  scrollbar-color:var(--cream-border) transparent;
}
.cards-pane::-webkit-scrollbar{width:5px}
.cards-pane::-webkit-scrollbar-thumb{background:var(--cream-border);border-radius:4px}
@media(max-width:991.98px){.cards-pane{height:auto;max-height:55vh;overflow-y:auto}}

/* Stay cards */
.stay-card{
  background:#fff;border:1.5px solid var(--border);border-radius:var(--r-lg);
  overflow:hidden;cursor:pointer;
  transition:all .25s cubic-bezier(.25,.46,.45,.94);box-shadow:var(--shadow-sm);
}
.stay-card:hover{transform:translateY(-3px);box-shadow:var(--shadow-md);border-color:var(--terracotta-lt)}
.stay-card.active-selected{border-color:var(--terracotta)!important;box-shadow:0 0 0 3px rgba(200,113,74,0.2),var(--shadow-md)}
.stay-card-img-wrapper{position:relative;overflow:hidden;background:var(--cream-deep)}
.stay-card-img{width:100%;height:100%;object-fit:cover;transition:transform .4s ease}
.stay-card:hover .stay-card-img{transform:scale(1.04)}

/* Badges */
.badge-category-pill{
  position:absolute;top:10px;left:10px;padding:4px 10px;border-radius:50px;
  font-size:0.7rem;font-weight:700;letter-spacing:0.4px;text-transform:uppercase;
  backdrop-filter:blur(8px);
}
.badge-motel{background:rgba(200,113,74,0.92);color:#fff}
.badge-airbnb{background:rgba(107,143,113,0.92);color:#fff}
.badge-rental{background:rgba(212,133,42,0.92);color:#fff}
.badge-rating-pill{
  position:absolute;top:10px;right:10px;background:rgba(255,255,255,0.93);
  backdrop-filter:blur(8px);color:var(--mocha);font-size:0.75rem;font-weight:700;
  padding:3px 9px;border-radius:50px;box-shadow:0 2px 8px rgba(0,0,0,0.12);
}
.badge-availability{
  position:absolute;bottom:10px;left:10px;font-size:0.68rem;font-weight:600;
  padding:3px 10px;border-radius:50px;backdrop-filter:blur(8px);
}
.status-walkin{background:rgba(107,143,113,0.88);color:#fff}
.status-booking{background:rgba(200,113,74,0.88);color:#fff}
.status-closed{background:rgba(150,100,80,0.8);color:#fff}
.badge-instant{
  position:absolute;bottom:10px;right:10px;font-size:0.65rem;font-weight:700;
  padding:3px 9px;border-radius:50px;background:rgba(212,133,42,0.9);color:#fff;
}
.stay-tag{
  display:inline-block;padding:3px 10px;background:var(--cream-deep);
  color:var(--text-secondary);border:1px solid var(--border);
  border-radius:50px;font-size:0.68rem;font-weight:500;white-space:nowrap;
}

/* Map */
.map-pane-container{position:relative;height:100%;flex:1}
@media(max-width:991.98px){.map-pane-container{height:45vh;min-height:320px}}
#leafletMap{width:100%;height:100%}
.map-overlay-layers{
  position:absolute;top:14px;right:14px;z-index:800;
  display:flex;align-items:center;gap:6px;
  background:rgba(255,255,255,0.96);backdrop-filter:blur(10px);
  border:1px solid var(--border);border-radius:var(--r-md);
  padding:8px 12px;box-shadow:var(--shadow-sm);
}
.map-layer-btn{
  display:inline-flex;align-items:center;gap:4px;padding:5px 12px;
  border-radius:50px;font-size:0.76rem;font-weight:600;
  background:transparent;border:1.5px solid transparent;
  color:var(--text-secondary);cursor:pointer;transition:all .18s;
}
.map-layer-btn:hover{background:var(--cream-deep);color:var(--terracotta)}
.map-layer-btn.active{background:var(--terracotta);color:#fff;border-color:var(--terracotta)}
.map-layer-btn.btn-locating{color:var(--terracotta);animation:pulse 1.2s infinite}

@keyframes pulse{0%,100%{opacity:1}50%{opacity:0.5}}
@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
@keyframes slideInRight{from{transform:translateX(40px);opacity:0}to{transform:translateX(0);opacity:1}}
@keyframes hotspot-pulse{
  0%{box-shadow:0 0 0 0 rgba(200,113,74,0.6)}
  50%{box-shadow:0 0 0 12px rgba(200,113,74,0)}
  100%{box-shadow:0 0 0 0 rgba(200,113,74,0)}
}
.spin-icon{animation:spin 1s linear infinite}

/* Leaflet overrides */
.leaflet-popup-content-wrapper{
  background:#fff!important;border:1px solid var(--border)!important;
  border-radius:var(--r-md)!important;box-shadow:var(--shadow-md)!important;
  color:var(--text-primary)!important;font-family:'Plus Jakarta Sans',sans-serif!important;
}
.leaflet-popup-tip{background:#fff!important}

/* Directions card */
.directions-info-card{
  position:absolute;bottom:60px;left:14px;right:14px;z-index:800;
  background:rgba(255,255,255,0.97);backdrop-filter:blur(12px);
  border:1px solid var(--border);border-radius:var(--r-md);
  padding:16px;box-shadow:var(--shadow-md);color:var(--text-primary)!important;
}
.map-legend-bar{
  position:absolute;bottom:14px;left:50%;transform:translateX(-50%);z-index:800;
  display:flex;align-items:center;gap:12px;
  background:rgba(255,255,255,0.96);backdrop-filter:blur(10px);
  border:1px solid var(--border);border-radius:50px;
  padding:7px 16px;font-size:0.75rem;box-shadow:var(--shadow-sm);
  color:var(--text-secondary);white-space:nowrap;
}
.dest-marker-badge,.origin-pin-badge{
  background:var(--terracotta);color:#fff;border-radius:50px;
  padding:4px 10px;font-size:0.7rem;font-weight:700;white-space:nowrap;
  box-shadow:0 3px 10px rgba(200,113,74,0.4);
}
.origin-pin-badge{background:var(--sage)}

/* Toast */
.action-toast-wrapper{
  position:fixed;bottom:24px;right:24px;z-index:9999;
  max-width:340px;animation:slideInRight .35s ease;
}
.action-toast{
  background:#fff;border:1.5px solid var(--border);
  border-left:4px solid var(--terracotta);border-radius:var(--r-md);
  padding:16px 20px;box-shadow:var(--shadow-lg);color:var(--text-primary)!important;
}

/* Modals */
.modal-content,.modal-content-dark{
  background:#fff!important;border:1px solid var(--border)!important;
  border-radius:var(--r-xl)!important;box-shadow:var(--shadow-lg)!important;
  color:var(--text-primary)!important;overflow:hidden;
}
.modal-header{background:var(--cream-deep);border-bottom:1px solid var(--border)!important;padding:20px 24px}
.modal-body{padding:24px}
.modal-footer{background:var(--cream-deep);border-top:1px solid var(--border)!important;padding:16px 24px}
.modal-title{color:var(--mocha)!important;font-weight:700}
.modal-input{
  background:var(--cream-deep)!important;border-color:var(--border)!important;
  color:var(--text-primary)!important;border-radius:var(--r-sm)!important;
}
.modal-input:focus{
  background:#fff!important;border-color:var(--terracotta)!important;
  box-shadow:0 0 0 3px rgba(200,113,74,0.18)!important;
}
.btn-close-white{
  filter:none!important;
  background:var(--cream-border) center/12px no-repeat!important;
  background-image:url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath d='M.293.293a1 1 0 0 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 0 1-1.414-1.414L6.586 8 .293 1.707a1 1 0 0 1 0-1.414z'/%3E%3C/svg%3E")!important;
  border-radius:50%!important;width:32px;height:32px;opacity:1!important;
}
.btn-close-white:hover{background-color:var(--border)!important}

/* Room detail cards */
.room-detail-card{
  background:#fff;border:1.5px solid var(--border);border-radius:var(--r-lg);
  overflow:hidden;transition:box-shadow .2s;
}
.room-detail-card:hover{box-shadow:var(--shadow-md)}
.room-detail-img-wrapper{position:relative;height:200px;overflow:hidden;background:var(--cream-deep)}
.room-detail-img{width:100%;height:100%;object-fit:cover;transition:transform .35s ease}
.room-detail-card:hover .room-detail-img{transform:scale(1.04)}
.room-rate-label{font-size:0.62rem;font-weight:800;text-transform:uppercase;letter-spacing:0.6px;color:var(--text-muted)}

/* 360 Panorama Viewer */
.showroom-viewer-wrapper{
  position:relative;width:100%;height:420px;border-radius:var(--r-lg);
  overflow:hidden;background:#1a1008;border:2px solid var(--border);user-select:none;
}
@media(max-width:576px){.showroom-viewer-wrapper{height:280px}}
.showroom-pano-canvas{
  width:100%;height:100%;cursor:grab;display:block;
  object-fit:cover;transition:opacity .4s ease;background:#1a1008;
}
.showroom-pano-canvas.loading{opacity:0.3}
.showroom-pano-canvas:active{cursor:grabbing}

/* Hotspots */
.pano-hotspot{
  position:absolute;transform:translate(-50%,-50%);cursor:pointer;z-index:10;
  display:flex;flex-direction:column;align-items:center;gap:4px;transition:transform .2s;
}
.pano-hotspot:hover{transform:translate(-50%,-50%) scale(1.15)}
.pano-hotspot-icon{
  width:40px;height:40px;border-radius:50%;background:rgba(200,113,74,0.92);
  border:2.5px solid #fff;display:flex;align-items:center;justify-content:center;
  color:#fff;font-size:1rem;box-shadow:0 4px 16px rgba(200,113,74,0.5);
  animation:hotspot-pulse 2.5s infinite;
}
.pano-hotspot-label{
  background:rgba(255,255,255,0.96);color:var(--mocha);font-size:0.7rem;font-weight:700;
  padding:3px 10px;border-radius:50px;box-shadow:0 2px 8px rgba(0,0,0,0.14);
  white-space:nowrap;max-width:130px;text-align:center;
}
.pano-controls{
  position:absolute;top:12px;left:50%;transform:translateX(-50%);z-index:20;
  display:flex;gap:6px;flex-wrap:wrap;justify-content:center;
}
.pano-room-btn{
  padding:6px 14px;border-radius:50px;font-size:0.72rem;font-weight:700;
  border:none;cursor:pointer;background:rgba(255,255,255,0.93);
  color:var(--mocha);box-shadow:var(--shadow-sm);transition:all .18s;backdrop-filter:blur(6px);
}
.pano-room-btn:hover,.pano-room-btn.active{background:var(--terracotta);color:#fff;box-shadow:0 4px 12px rgba(200,113,74,0.4)}
.pano-hint{
  position:absolute;bottom:42px;left:50%;transform:translateX(-50%);
  background:rgba(0,0,0,0.52);color:#fff;font-size:0.68rem;
  padding:4px 14px;border-radius:50px;z-index:20;pointer-events:none;
  opacity:1;transition:opacity 1.2s;white-space:nowrap;
}
.pano-hint.hidden{opacity:0}
.pano-loading{
  position:absolute;inset:0;background:rgba(26,16,8,0.82);
  display:flex;align-items:center;justify-content:center;
  z-index:30;transition:opacity .3s;
}
.pano-loading.done{opacity:0;pointer-events:none}
.pano-spinner{
  width:42px;height:42px;border:4px solid rgba(200,113,74,0.3);
  border-top-color:var(--terracotta);border-radius:50%;animation:spin 0.9s linear infinite;
}
.pano-room-label{
  position:absolute;bottom:14px;right:14px;background:rgba(255,255,255,0.94);
  color:var(--mocha);font-size:0.75rem;font-weight:700;padding:5px 14px;
  border-radius:50px;z-index:20;box-shadow:var(--shadow-sm);backdrop-filter:blur(6px);
}

/* Booking */
.booking-summary-box{background:var(--cream-deep);border:1.5px solid var(--border);border-radius:var(--r-md);padding:16px 20px}
.booking-review-box{background:var(--cream-deep);border:1.5px solid var(--terracotta-lt);border-radius:var(--r-md);padding:16px 20px}
.booking-item-card{background:#fff;border:1.5px solid var(--border);border-radius:var(--r-md);padding:16px;transition:box-shadow .2s}
.booking-item-card:hover{box-shadow:var(--shadow-sm)}
.booking-status-confirmed{border-left:4px solid var(--sage)}
.booking-status-cancelled{border-left:4px solid #D95F5F;opacity:0.7}
.booking-status-pending{border-left:4px solid var(--amber)}

/* Verify */
.verify-code-display{
  background:var(--mocha);color:var(--amber-lt);font-family:'Courier New',monospace;
  border-radius:var(--r-md);padding:16px;letter-spacing:3px;
  font-weight:800;font-size:1.5rem;text-align:center;
}
.letter-spacing-2{letter-spacing:0.15em}
.letter-spacing-3{letter-spacing:0.25em}

/* Index page cards */
.infographic-card{
  background:#fff;border:1.5px solid var(--border);border-radius:var(--r-xl);
  overflow:hidden;transition:all .3s ease;box-shadow:var(--shadow-sm);height:100%;
}
.infographic-card:hover{transform:translateY(-4px);box-shadow:var(--shadow-lg)}
.infographic-img-container{position:relative;height:220px;overflow:hidden}
.infographic-img{width:100%;height:100%;object-fit:cover;transition:transform .4s ease}
.infographic-card:hover .infographic-img{transform:scale(1.06)}
.infographic-badge-floating{
  position:absolute;top:14px;left:14px;background:rgba(255,255,255,0.95);
  color:var(--mocha);padding:5px 12px;border-radius:50px;font-size:0.72rem;
  font-weight:700;box-shadow:var(--shadow-sm);
}
.infographic-promo-badge{
  position:absolute;top:14px;right:14px;background:var(--terracotta);
  color:#fff;padding:5px 12px;border-radius:50px;font-size:0.72rem;
  font-weight:700;box-shadow:0 3px 10px rgba(200,113,74,0.4);
}
.infographic-body{padding:20px;display:flex;flex-direction:column;height:calc(100% - 220px)}

/* Utilities */
.btn-xs{padding:4px 10px;font-size:0.75rem}
.tracking-tight{letter-spacing:-0.025em}
.fw-extrabold{font-weight:800}
.text-mocha{color:var(--mocha)!important}
.text-terracotta{color:var(--terracotta)!important}
.text-sage{color:var(--sage)!important}
.text-amber{color:var(--amber)!important}

::-webkit-scrollbar{width:6px;height:6px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:var(--cream-border);border-radius:4px}
::-webkit-scrollbar-thumb:hover{background:var(--text-muted)}

@media(max-width:576px){
  .app-header{padding:12px 0}
  .modal-body{padding:16px}
  .showroom-viewer-wrapper{height:260px}
}
`;

const dest = path.join(__dirname, '..', 'styles.css');
fs.writeFileSync(dest, css, 'utf8');
console.log('Wrote', css.length, 'bytes to', dest);
