<?php

/**
 * HK - oyun (yeni, 2026-10-23)
 * Oyun ekranı: piksel harita (yakınlaştır, kaydır, dokun), üst çubuk, il bilgisi ve düğmeleri, hamle penceresi (zafer şansı,
 * neden-sonuç listesi), diplomasi, odak, danışman, günlük, tur raporu, olaylar. Gerekenler: HK, HK - harita, HK - motor, HK - zekâ.
 */
add_action( 'wp_footer', function() {
    if ( ! function_exists( 'adc_hk_sayfa' ) || ! adc_hk_sayfa() ) { return; }
    ?>
<script>
(function(){
if (window.adcHKOyun) return;
var M = window.adcHKMotor;
function el(t, c, h) { var e = document.createElement(t); if (c) e.className = c; if (h !== undefined) e.innerHTML = h; return e; }
function yuzde(p) { return '%' + Math.round(p * 100); }
function renkSans(p) { return p >= 0.7 ? '#12b76a' : p >= 0.45 ? '#ff8a00' : '#e8323c'; }
function hex(c) { c = c.replace('#', ''); return [parseInt(c.substr(0, 2), 16), parseInt(c.substr(2, 2), 16), parseInt(c.substr(4, 2), 16)]; }
function karis(c, o, a) { return [c[0] + (o[0] - c[0]) * a, c[1] + (o[1] - c[1]) * a, c[2] + (o[2] - c[2]) * a]; }
var BEYAZ = [255, 255, 255], KOYU = [26, 26, 46], DENIZ = [91, 124, 250], DALGA = [142, 166, 255];
window.adcHKOyun = function(E, S) {
var V = E.V, UL = V.uluslar, W = M.W, H = M.H, IL = M.IL, kok = E.govde;
var sec = -1, hedef = {}, zoom = 0, flas = {}, flasT = 0, cs = 4;
E.panel.classList.add('adc-hk-tam');
kok.innerHTML = '';
var hud = el('div', 'adc-hk-hud'), sar = el('div', 'adc-hk-harita'), cv = el('canvas', 'adc-hk-tuval'), zm = el('div', 'adc-hk-zoom');
var bilgi = el('div', 'adc-hk-bilgi'), alt = el('div', 'adc-hk-alt'), sayfa = el('div', 'adc-hk-sayfa');
sar.appendChild(cv); kok.appendChild(hud);
var hw = el('div', 'adc-hk-hsar'); hw.appendChild(sar); hw.appendChild(zm); kok.appendChild(hw);
kok.appendChild(bilgi); kok.appendChild(alt); E.panel.appendChild(sayfa);
zm.innerHTML = '<button type="button" data-z="1">＋</button><button type="button" data-z="-1">－</button>';
alt.innerHTML = '<button type="button" class="adc-hk-btn" data-a="diplo">🕊 Diplomasi</button><button type="button" class="adc-hk-btn" data-a="odak">🎯 Odak</button>'
+ '<button type="button" class="adc-hk-btn" data-a="danis">💡 Danışman</button><button type="button" class="adc-hk-btn" data-a="log">📜</button>'
+ '<button type="button" class="adc-hk-btn adc-hk-bitir" data-a="tur">Turu bitir ▶</button>';
var taban = document.createElement('canvas'); taban.width = W; taban.height = H;
var tctx = taban.getContext('2d'), ctx = cv.getContext('2d');
function ilRenk(k) {
var s = S.il[k].s;
if (s >= 0) return hex(UL[s].renk);
return k % 3 === 0 ? [214, 204, 176] : k % 3 === 1 ? [200, 190, 160] : [226, 216, 190];
}
function tabanCiz() {
var img = tctx.createImageData(W, H), d = img.data, ik = M.ikmal(S, S.oy), yan = flasT && (Date.now() / 250 | 0) % 2;
for (var y = 0; y < H; y++) for (var x = 0; x < W; x++) {
var i = y * W + x, v = M.HUCRE[i], c;
if (!v) c = ((x * 7 + y * 13) % 29 === 0 || (x * 3 + y * 5) % 41 === 0) ? DALGA : DENIZ;
else {
var k = v - 1, s = S.il[k].s; c = ilRenk(k);
var sag = x < W - 1 ? M.HUCRE[i + 1] : v, alt2 = y < H - 1 ? M.HUCRE[i + W] : v, sol = x > 0 ? M.HUCRE[i - 1] : v, ust = y > 0 ? M.HUCRE[i - W] : v;
var kenar = (sag && sag !== v) || (alt2 && alt2 !== v), diger = function(w){ return w && S.il[w - 1].s !== s; };
if (k === sec) c = karis(c, BEYAZ, 0.45);
else if (hedef[k] && (x + y) % 2 === 0) c = karis(c, hedef[k].tur === 'saldir' ? [255, 43, 214] : BEYAZ, 0.55);
if (s === S.oy && !ik[k] && !M.UL[s].oz.yam && (x + y) % 3 === 0) c = karis(c, KOYU, 0.4);
if (flas[k] && yan) c = karis(c, flas[k] === 'kotu' ? [232, 50, 60] : [255, 255, 255], 0.7);
if (diger(sag) || diger(alt2) || diger(sol) || diger(ust)) c = karis(c, KOYU, 0.55);
else if (kenar) c = karis(c, KOYU, 0.18);
}
d[i * 4] = c[0]; d[i * 4 + 1] = c[1]; d[i * 4 + 2] = c[2]; d[i * 4 + 3] = 255;
}
tctx.putImageData(img, 0, 0);
}
function boyut() {
var gen = sar.clientWidth || 340, yuk = sar.clientHeight || 300, sig = gen / W, dol = Math.max(sig * 1.6, yuk / H);
cs = [sig, dol, dol * 1.6][zoom];
var dpr = window.devicePixelRatio || 1;
cv.style.width = Math.round(W * cs) + 'px'; cv.style.height = Math.round(H * cs) + 'px';
cv.width = Math.round(W * cs * dpr); cv.height = Math.round(H * cs * dpr);
ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
}
function rozet(x, y, yazi, arka, cerceve, renk, fs) {
ctx.font = 'bold ' + fs + 'px monospace';
var w = ctx.measureText(yazi).width + 6, h = fs + 4;
ctx.fillStyle = cerceve; ctx.fillRect(Math.round(x - w / 2) - 1, Math.round(y - h / 2) - 1, Math.round(w) + 2, h + 2);
ctx.fillStyle = arka; ctx.fillRect(Math.round(x - w / 2), Math.round(y - h / 2), Math.round(w), h);
ctx.fillStyle = renk; ctx.textAlign = 'center'; ctx.textBaseline = 'middle'; ctx.fillText(yazi, x, y + 1);
}
function ciz() {
tabanCiz();
ctx.imageSmoothingEnabled = false;
ctx.clearRect(0, 0, W * cs, H * cs);
ctx.drawImage(taban, 0, 0, W * cs, H * cs);
var fs = Math.max(9, Math.min(13, Math.round(cs * 2.4))), yakin = zoom > 0;
for (var k = 0; k < M.N; k++) {
var I = S.il[k], x = (IL[k].lx + 0.5) * cs, y = (IL[k].ly + 0.5) * cs, s = I.s;
if (hedef[k]) {
var hd = hedef[k];
if (hd.tur === 'saldir') rozet(x, y, yuzde(hd.p), renkSans(hd.p), '#1a1a2e', '#fff', fs);
else if (hd.tur === 'tasi') rozet(x, y, '➜' + I.a, '#fff', '#0000ee', '#0000ee', fs);
else rozet(x, y, '🕊' + I.a, '#e8e8f0', '#1a1a2e', '#1a1a2e', fs);
continue;
}
if (!yakin && s < 0 && k !== sec) continue;
var ben = s === S.oy;
rozet(x, y, String(I.a), ben ? (I.h > 0 ? '#fff' : '#efeaff') : '#fff', ben ? '#0000ee' : '#1a1a2e', ben ? '#0000ee' : '#1a1a2e', fs);
if (s >= 0 && S.ul[s].bk === k) { ctx.fillStyle = '#ffcc00'; ctx.strokeStyle = '#1a1a2e'; ctx.font = (fs + 2) + 'px monospace'; ctx.fillText('★', x, y - fs - 1); }
if (I.kale && yakin) { ctx.font = fs + 'px monospace'; ctx.fillStyle = '#1a1a2e'; ctx.fillText('♜', x - fs * 1.4, y + 1); }
if (zoom === 2 || k === sec) { ctx.font = 'bold ' + (fs - 1) + 'px monospace'; ctx.lineWidth = 3; ctx.strokeStyle = 'rgba(255,255,255,.85)'; ctx.strokeText(IL[k].ad, x, y + fs + 3); ctx.fillStyle = '#1a1a2e'; ctx.fillText(IL[k].ad, x, y + fs + 3); }
}
}
function ortala(k) {
var x = (IL[k].lx + 0.5) * cs, y = (IL[k].ly + 0.5) * cs;
sar.scrollLeft = x - sar.clientWidth / 2; sar.scrollTop = y - sar.clientHeight / 2;
}
function yakinlas(d) {
var mx = (sar.scrollLeft + sar.clientWidth / 2) / cs, my = (sar.scrollTop + sar.clientHeight / 2) / cs;
zoom = Math.max(0, Math.min(2, zoom + d)); boyut(); ciz();
sar.scrollLeft = mx * cs - sar.clientWidth / 2; sar.scrollTop = my * cs - sar.clientHeight / 2;
try { localStorage.setItem('adc_hk_zoom', zoom); } catch (e) {}
}
function hedefleriBul() {
hedef = {};
if (sec < 0 || S.il[sec].s !== S.oy || S.il[sec].h < 1) return;
IL[sec].komsu.forEach(function(t){
var T = S.il[t];
if (T.s === S.oy) hedef[t] = { tur: 'tasi' };
else if (T.s < 0 || M.savasta(S, S.oy, T.s)) hedef[t] = { tur: 'saldir', p: M.tahmin(S, sec, t, S.il[sec].h).p };
else hedef[t] = { tur: 'baris' };
});
}
function sec2(k) { sec = k; hedefleriBul(); ciz(); bilgiCiz(); }
cv.addEventListener('click', function(e){
var r = cv.getBoundingClientRect(), x = Math.floor((e.clientX - r.left) / cs), y = Math.floor((e.clientY - r.top) / cs);
if (x < 0 || y < 0 || x >= W || y >= H) return;
var k = M.HUCRE[y * W + x] - 1;
if (k < 0) { sec2(-1); return; }
if (sec >= 0 && hedef[k]) { hamleAc(sec, k); return; }
sec2(k);
});
zm.addEventListener('click', function(e){ var b = e.target.closest('button'); if (b) yakinlas(+b.getAttribute('data-z')); });
function hudCiz() {
var L = S.ul[S.oy], G = M.gelirHesap(S, S.oy), u = UL[S.oy];
hud.innerHTML = '<span class="adc-hk-bayrak" style="--u:' + u.renk + '">' + u.ad + '</span>'
+ '<span title="Hazine (bu tur gelir)">💰 ' + Math.floor(L.altin) + ' <small>' + (G.net >= 0 ? '+' : '') + G.net + '</small></span>'
+ '<span title="Moral: savaş gücünü etkiler">⚑ ' + Math.round(L.moral) + '</span>'
+ '<span title="Tehdit: 15 olunca komşular birleşir" class="' + (L.tehdit >= 11 ? 'adc-hk-acil' : '') + '">⚠ ' + L.tehdit + '/' + M.AYAR.tehditSinir + '</span>'
+ '<span>📅 ' + M.MEVSIM[M.mevsim(S)] + ' · ' + (S.tur + 1) + '/' + M.AYAR.tur + '</span>';
}
function iliski(s) {
if (s < 0) return '<b>Bağımsız</b> · savaş ilanı gerekmez';
if (s === S.oy) return '<b>Senin ilin</b>';
if (M.savasta(S, S.oy, s)) return '<b class="adc-hk-kirmizi">⚔ Savaştasınız</b>';
if (S.at[S.oy][s] > S.tur) return '🕊 Ateşkes · ' + (S.at[S.oy][s] - S.tur) + ' tur';
return '🕊 Barış';
}
function bilgiCiz() {
hudCiz();
if (sec < 0) {
bilgi.innerHTML = '<p class="adc-hk-ipucu">👆 Kendi iline (mavi çerçeveli sayılar) dokun. Hazır askerin varsa komşu illerde <b>zafer şansı</b> çıkar; birine dokununca hamle yaparsın. Sonra <b>Turu bitir</b>.</p>';
return;
}
var k = sec, I = S.il[k], s = I.s, il = IL[k], L = S.ul[S.oy];
var bas = '<div class="adc-hk-ilad"><b>' + il.ad + '</b>' + (s >= 0 && S.ul[s].bk === k ? ' ★' : '')
+ ' <span class="adc-hk-bayrak" style="--u:' + (s >= 0 ? UL[s].renk : '#cfc6a8') + '">' + (s >= 0 ? UL[s].ad : 'Bağımsız') + '</span></div>';
var ozl = [M.ARAZI[il.arazi].ad, 'gelir ' + il.gelir * 2, '⚔ ' + I.a + (s === S.oy ? ' (hazır ' + I.h + ')' : '')];
if (I.kale) ozl.push('♜ kale'); if (I.b === 'p') ozl.push('🏛 pazar'); if (I.b === 'k') ozl.push('⛺ kışla');
if (s === S.oy && I.u > 0) ozl.push('⏳ uyum ' + I.u + ' tur');
if (s === S.oy && !M.ikmal(S, S.oy)[k] && !M.UL[s].oz.yam) ozl.push('📦 ikmal yok');
var h = bas + '<div class="adc-hk-oz">' + ozl.join(' · ') + '</div><div class="adc-hk-oz">' + iliski(s) + '</div>';
if (s === S.oy) {
var f = M.fiyat(S, s, 'asker'), kalan = M.toplamaSiniri(S, k) - (I.t || 0), ok = I.u === 0 && M.ikmal(S, s)[k] || M.UL[s].oz.yam && I.u === 0;
h += '<div class="adc-hk-eylem">'
+ '<button type="button" class="adc-hk-btn" data-e="a1"' + (!ok || kalan < 1 || L.altin < f ? ' disabled' : '') + '>＋1 asker <small>' + f + '💰</small></button>'
+ '<button type="button" class="adc-hk-btn" data-e="am"' + (!ok || kalan < 2 || L.altin < f * 2 ? ' disabled' : '') + '>＋' + Math.max(2, Math.min(kalan, Math.floor(L.altin / f))) + '</button>'
+ '<button type="button" class="adc-hk-btn" data-e="kale"' + (I.kale || I.u || L.altin < M.fiyat(S, s, 'kale') ? ' disabled' : '') + '>♜ Kale <small>' + M.fiyat(S, s, 'kale') + '</small></button>'
+ '<button type="button" class="adc-hk-btn" data-e="pazar"' + (I.b || I.u || L.altin < M.fiyat(S, s, 'pazar') ? ' disabled' : '') + '>🏛 Pazar <small>' + M.fiyat(S, s, 'pazar') + '</small></button>'
+ '<button type="button" class="adc-hk-btn" data-e="kisla"' + (I.b || I.u || L.altin < M.fiyat(S, s, 'kisla') ? ' disabled' : '') + '>⛺ Kışla <small>' + M.fiyat(S, s, 'kisla') + '</small></button>'
+ '</div><p class="adc-hk-ipucu">' + (I.u ? 'Yeni fethedildi: ' + I.u + ' tur gelir ve asker vermez.' : 'Bu tur ' + Math.max(0, kalan) + ' asker daha toplanabilir.')
+ (I.h ? ' Saldırmak için parlayan komşuya dokun.' : '') + '</p>';
} else if (s >= 0) {
var komsu = M.komsuUlus(S, S.oy, s);
h += '<div class="adc-hk-eylem">'
+ (M.savasta(S, S.oy, s) ? '<button type="button" class="adc-hk-btn" data-e="baris" data-u="' + s + '">🕊 Barış teklif et</button>'
: komsu && S.at[S.oy][s] <= S.tur ? '<button type="button" class="adc-hk-btn" data-e="savas" data-u="' + s + '">⚔ Savaş ilan et</button>' : '')
+ '</div><p class="adc-hk-ipucu">' + UL[s].lider + ' · ' + M.iller(S, s).length + ' il · ' + M.asker(S, s) + ' asker</p>';
}
bilgi.innerHTML = h;
}
bilgi.addEventListener('click', function(e){
var b = e.target.closest('button'); if (!b || b.disabled) return;
var a = b.getAttribute('data-e'), k = sec;
if (a === 'a1') M.topla(S, k, 1);
else if (a === 'am') M.topla(S, k, 99);
else if (a === 'kale' || a === 'pazar' || a === 'kisla') { if (M.insa(S, k, a)) E.ses && E.ses('insa'); }
else if (a === 'savas') { savasSor(+b.getAttribute('data-u'), function(){ sec2(sec); }); return; }
else if (a === 'baris') { barisSor(+b.getAttribute('data-u')); return; }
kaydet(); hedefleriBul(); ciz(); bilgiCiz();
});
function sayfaAc(html, kapanabilir) {
sayfa.innerHTML = '<div class="adc-hk-kart">' + (kapanabilir !== false ? '<button type="button" class="adc-hk-kapat" aria-label="Kapat">✕</button>' : '') + html + '</div>';
sayfa.style.display = 'flex';
return sayfa.firstChild;
}
function sayfaKapat() { sayfa.style.display = 'none'; sayfa.innerHTML = ''; }
sayfa.addEventListener('click', function(e){ if (e.target === sayfa || e.target.closest('.adc-hk-kapat')) sayfaKapat(); });
function savasSor(u, sonra) {
var k = sayfaAc('<h3>⚔ ' + M.ek(UL[u].ad, 'e') + ' savaş?</h3><p>' + UL[u].lider + ' · ' + M.iller(S, u).length + ' il · ' + M.asker(S, u) + ' asker (sende ' + M.asker(S, S.oy) + ')</p>'
+ '<p class="adc-hk-ipucu">Savaş ilanı tehdidi +2 artırır. Tehdit ' + M.AYAR.tehditSinir + ' olunca komşular sana karşı birleşir. Şu an: ' + S.ul[S.oy].tehdit + '.</p>'
+ '<div class="adc-hk-eylem"><button type="button" class="adc-hk-btn" data-x="0">Vazgeç</button><button type="button" class="adc-hk-btn adc-hk-kirmizib" data-x="1">⚔ Savaş ilan et</button></div>');
k.addEventListener('click', function(e){
var b = e.target.closest('[data-x]'); if (!b) return;
if (b.getAttribute('data-x') === '1') { M.savasIlan(S, S.oy, u); kaydet(); }
sayfaKapat(); if (sonra) sonra();
});
}
function barisSor(u) {
var r = M.barisTeklif(S, u); kaydet();
sayfaAc('<h3>' + (r.ok ? '🕊 Barış!' : '✉ Elçi geri döndü') + '</h3><p>' + (r.ok ? UL[u].ad + ' barışı kabul etti.' + (r.kazandi ? ' <b>Savaşı kazandın!</b> Moral +10.' : '') + ' ' + M.AYAR.ateskes + ' tur ateşkes.' : r.neden) + '</p>');
sec2(sec);
}
function hamleAc(f, t) {
var T = S.il[t], F = S.il[f], hd = hedef[t];
if (hd.tur === 'baris') { savasSor(T.s, function(){ sec2(f); }); return; }
var n = F.h, tasi = hd.tur === 'tasi';
var k = sayfaAc('<h3>' + (tasi ? '➜ ' : '⚔ ') + IL[f].ad + ' → ' + IL[t].ad + '</h3>'
+ '<label class="adc-hk-miktar">Asker: <b data-n></b> / ' + F.h + '<input type="range" min="1" max="' + F.h + '" value="' + n + '"></label>'
+ '<div data-t></div><div class="adc-hk-eylem"><button type="button" class="adc-hk-btn" data-x="0">Vazgeç</button>'
+ '<button type="button" class="adc-hk-btn ' + (tasi ? '' : 'adc-hk-kirmizib') + '" data-x="1">' + (tasi ? '➜ Gönder' : '⚔ SALDIR') + '</button></div>');
var inp = k.querySelector('input'), nb = k.querySelector('[data-n]'), tb = k.querySelector('[data-t]');
function yenile() {
n = +inp.value; nb.textContent = n;
if (tasi) { tb.innerHTML = '<p class="adc-hk-ipucu">Askerler bu tur yeniden yürüyemez' + (M.UL[S.oy].oz.hizli ? ' (Moğol atlıları kendi topraklarında yürüyebilir)' : '') + '.</p>'; return; }
var tm = M.tahmin(S, f, t, n);
tb.innerHTML = '<div class="adc-hk-sans" style="--p:' + Math.round(tm.p * 100) + '%;--r:' + renkSans(tm.p) + '"><b>Zafer şansı ' + yuzde(tm.p) + '</b><i></i></div>'
+ '<p class="adc-hk-oz">Savunan: ' + T.a + ' asker · ' + M.ARAZI[IL[t].arazi].ad + (T.kale ? ' · ♜ kale' : '') + ' · Tahmini kaybın: <b>' + tm.kayip + '</b></p>'
+ '<ul class="adc-hk-notlar">' + tm.notlar.map(function(x){ return '<li class="' + (x[1] > 0 ? 'adc-hk-arti' : 'adc-hk-eksi') + '">' + (x[1] > 0 ? '▲ ' : '▼ ') + x[0] + '</li>'; }).join('') + '</ul>'
+ (n < T.a ? '<p class="adc-hk-ipucu">🧠 Sayıca azsın: kazanırsan <b>dâhiyane zafer</b> (+8 puan).</p>' : '');
}
inp.addEventListener('input', yenile); yenile();
k.addEventListener('click', function(e){
var b = e.target.closest('[data-x]'); if (!b) return;
if (b.getAttribute('data-x') === '0') { sayfaKapat(); return; }
var R = M.hamle(S, f, t, n); kaydet();
if (!R) { sayfaKapat(); return; }
if (R.tur === 'tasi') { sayfaKapat(); sec2(t); return; }
sonucGoster(R);
});
}
function sonucGoster(R) {
var bk = R.zafer && R.d >= 0 && M.BASKENT.indexOf(R.t) >= 0;
flas = {}; flas[R.t] = R.zafer ? 'iyi' : 'kotu'; flasBasla();
if (E.ses) E.ses(R.zafer ? 'zafer' : 'yenilgi');
sayfaAc('<div class="adc-hk-sonuc ' + (R.zafer ? 'adc-hk-zafer' : 'adc-hk-yenilgi') + '">'
+ '<div class="adc-hk-buyukyazi">' + (R.zafer ? (R.m ? 'ZAFER!' : 'İL ALINDI') : 'GERİ PÜSKÜRTÜLDÜN') + '</div>'
+ '<p>' + IL[R.t].ad + (R.zafer ? ' artık senin.' : ' direndi.') + '</p>'
+ '<p class="adc-hk-oz">Kaybın: <b>' + R.kayip + '</b> · Düşman kaybı: <b>' + R.dkayip + '</b>' + (R.geri ? ' · ' + R.geri + ' asker geri çekildi' : '') + '</p>'
+ (R.deha ? '<p class="adc-hk-deha">🧠 DÂHİYANE ZAFER! Sayıca azken kazandın.</p>' : '')
+ (bk ? '<p class="adc-hk-deha">🏛 Bir başkent düştü! Moral +10</p>' : '')
+ (!R.zafer ? '<p class="adc-hk-ipucu">İpucu: askerleri tek ilde topla, komşu illerden destek al (çok yönlü saldırı) ve kışın, dağa ya da kaleye zor saldır.</p>' : '')
+ '<button type="button" class="adc-hk-btn adc-hk-tamam" data-x="1">Tamam</button></div>');
sayfa.querySelector('[data-x]').addEventListener('click', function(){ sayfaKapat(); });
sec2(R.zafer ? R.t : R.f);
}
function flasBasla() {
flasT = Date.now();
(function dongu(){ if (Date.now() - flasT > 1600) { flasT = 0; flas = {}; ciz(); return; } ciz(); setTimeout(dongu, 250); })();
}
function diplomasi() {
var L = S.ul[S.oy], h = '<h3>🕊 Diplomasi</h3><div class="adc-hk-tehdit"><b>⚠ Tehdit ' + L.tehdit + '/' + M.AYAR.tehditSinir + '</b><i style="width:' + Math.min(100, L.tehdit / M.AYAR.tehditSinir * 100) + '%"></i></div>'
+ '<p class="adc-hk-ipucu">Her fetih tehdidi artırır (bağımsız il +1, ülke ili +2, başkent +4), her tur 1 azalır. ' + M.AYAR.tehditSinir + '\'e ulaşırsa komşular <b>koalisyon</b> kurar.</p><div class="adc-hk-uluslar">';
for (var u = 0; u < M.U; u++) {
if (u === S.oy) continue;
var o = S.ul[u], d = UL[u], durum;
if (o.olu) durum = '☠ yıkıldı';
else if (M.savasta(S, S.oy, u)) durum = '<b class="adc-hk-kirmizi">⚔ savaş</b>';
else if (S.at[S.oy][u] > S.tur) durum = '🕊 ateşkes ' + (S.at[S.oy][u] - S.tur);
else durum = M.komsuUlus(S, S.oy, u) ? '🕊 barış · komşu' : '🕊 barış';
h += '<div class="adc-hk-ulus' + (o.olu ? ' adc-hk-olu' : '') + '"><span class="adc-hk-renk" style="background:' + d.renk + '"></span><div><b>' + d.ad + '</b><small>' + M.iller(S, u).length + ' il · ' + M.asker(S, u) + ' asker · ' + durum + '</small></div>'
+ (o.olu ? '' : M.savasta(S, S.oy, u) ? '<button type="button" class="adc-hk-btn" data-b="' + u + '">🕊 Barış</button>'
: M.komsuUlus(S, S.oy, u) && S.at[S.oy][u] <= S.tur ? '<button type="button" class="adc-hk-btn" data-s="' + u + '">⚔</button>' : '') + '</div>';
}
var k = sayfaAc(h + '</div>');
k.addEventListener('click', function(e){
var b = e.target.closest('[data-b]'), s = e.target.closest('[data-s]');
if (b) barisSor(+b.getAttribute('data-b'));
if (s) savasSor(+s.getAttribute('data-s'), function(){ bilgiCiz(); ciz(); });
});
}
function odak() {
var L = S.ul[S.oy], h = '<h3>🎯 Ulusal Odak</h3><p class="adc-hk-ipucu">Bir hedef seç, birkaç tur sonra kalıcı güç kazanırsın. Aynı anda bir odak.</p><div class="adc-hk-odaklar">';
M.ODAK.forEach(function(o, i){
var bitti = L.bitenOdak.indexOf(i) >= 0, simdi = L.odak === i, ad = M.odakAd(S.oy, i), acik = i < 5 ? o.acik : UL[S.oy].odak_acik;
h += '<button type="button" class="adc-hk-odak' + (bitti ? ' adc-hk-bitti' : '') + (simdi ? ' adc-hk-simdi' : '') + (i === 5 ? ' adc-hk-ozel' : '') + '" data-o="' + i + '"' + (bitti || L.odak >= 0 ? ' disabled' : '') + '>'
+ '<b>' + (bitti ? '✓ ' : simdi ? '⏳ ' : '') + ad + '</b><small>' + acik + '</small><i>' + (simdi ? L.odakT + ' tur kaldı' : o.gun + ' tur') + '</i></button>';
});
var k = sayfaAc(h + '</div>');
k.addEventListener('click', function(e){ var b = e.target.closest('[data-o]'); if (!b || b.disabled) return; M.odakSec(S, S.oy, +b.getAttribute('data-o')); kaydet(); odak(); });
}
function danisman() {
var L = M.danisman(S), h = '<h3>💡 Danışman</h3><ul class="adc-hk-danis">';
L.forEach(function(x, i){ h += '<li>' + x.m + (x.f !== undefined ? ' <button type="button" class="adc-hk-btn" data-d="' + i + '">Göster</button>' : x.k !== undefined ? ' <button type="button" class="adc-hk-btn" data-d="' + i + '">Göster</button>' : '') + '</li>'; });
var k = sayfaAc(h + '</ul>');
k.addEventListener('click', function(e){
var b = e.target.closest('[data-d]'); if (!b) return; var x = L[+b.getAttribute('data-d')];
sayfaKapat(); sec2(x.f !== undefined ? x.f : x.k); ortala(sec);
});
}
function gunluk() {
sayfaAc('<h3>📜 Vakanüvis</h3><ul class="adc-hk-log">' + S.log.map(function(l){ return '<li class="adc-hk-' + (l.tur || 'n') + '">' + l.m + '</li>'; }).join('') + '</ul>');
}
function turBitir() {
var once = S.il.map(function(I){ return I.s; });
S.log.forEach(function(l){ l.e = 1; });
var bekle = sayfaAc('<div class="adc-hk-bekle">⏳ Dünya hamlesini yapıyor…</div>', false);
setTimeout(function(){
M.turBitir(S); kaydet();
flas = {};
S.il.forEach(function(I, k){ if (once[k] !== I.s) { if (once[k] === S.oy) flas[k] = 'kotu'; else if (I.s === S.oy) flas[k] = 'iyi'; } });
sec = -1; hedef = {};
if (S.bitti) { sayfaKapat(); E.bitir(S); return; }
var yeni = S.log.filter(function(l){ return !l.e; }).reverse(), kayip = 0;
once.forEach(function(s, k){ if (s === S.oy && S.il[k].s !== S.oy) kayip++; });
var sirali = yeni.filter(function(l){ return l.tur === 'kotu'; }).concat(yeni.filter(function(l){ return l.tur === 'iyi'; }), yeni.filter(function(l){ return l.tur !== 'kotu' && l.tur !== 'iyi'; }).slice(-6));
var k = sayfaAc('<h3>📅 ' + M.MEVSIM[M.mevsim(S)] + ', ' + (Math.floor(S.tur / 4) + 1) + '. yıl</h3>' + (kayip ? '<p class="adc-hk-kirmizi"><b>' + kayip + ' il kaybettin!</b> Haritada kırmızı yanıp sönüyor.</p>' : '')
+ '<ul class="adc-hk-log">' + sirali.map(function(l){ return '<li class="adc-hk-' + (l.tur || 'n') + '">' + l.m + '</li>'; }).join('') + '</ul>'
+ '<button type="button" class="adc-hk-btn adc-hk-tamam" data-x="1">Devam ▶</button>', false);
k.querySelector('[data-x]').addEventListener('click', function(){ sayfaKapat(); sirada(); });
flasBasla(); bilgiCiz();
var ilk = Object.keys(flas)[0]; if (ilk !== undefined && flas[ilk] === 'kotu') ortala(+ilk);
}, 350);
}
function sirada() {
if (S.teklif !== null && S.teklif !== undefined) {
var u = S.teklif, k = sayfaAc('<h3>✉ ' + UL[u].ad + ' barış istiyor</h3><p>' + UL[u].lider + ' elçi gönderdi. Kabul edersen ' + M.AYAR.ateskes + ' tur ateşkes olur, aldığın topraklar sende kalır.</p>'
+ '<div class="adc-hk-eylem"><button type="button" class="adc-hk-btn" data-x="0">Reddet, savaş sürsün</button><button type="button" class="adc-hk-btn adc-hk-yesilb" data-x="1">🕊 Kabul et</button></div>', false);
k.addEventListener('click', function(e){ var b = e.target.closest('[data-x]'); if (!b) return; M.teklifCevap(S, b.getAttribute('data-x') === '1'); kaydet(); sayfaKapat(); bilgiCiz(); ciz(); sirada(); });
return;
}
if (S.olay) {
var o = null; M.OLAY.forEach(function(x){ if (x.id === S.olay) o = x; });
if (!o) { S.olay = null; return; }
var k2 = sayfaAc('<div class="adc-hk-olay"><small>OLAY</small><h3>' + o.b + '</h3><p>' + o.m.replace('{lider}', UL[S.oy].lider) + '</p></div>'
+ '<div class="adc-hk-secim"><button type="button" class="adc-hk-btn" data-x="0">' + o.a[0] + '</button><button type="button" class="adc-hk-btn" data-x="1">' + o.c[0] + '</button></div>', false);
k2.addEventListener('click', function(e){ var b = e.target.closest('[data-x]'); if (!b) return; M.olayCevap(S, b.getAttribute('data-x') === '1'); kaydet(); sayfaKapat(); bilgiCiz(); ciz(); });
}
}
alt.addEventListener('click', function(e){
var b = e.target.closest('[data-a]'); if (!b) return;
var a = b.getAttribute('data-a');
if (a === 'tur') turBitir(); else if (a === 'diplo') diplomasi(); else if (a === 'odak') odak(); else if (a === 'danis') danisman(); else gunluk();
});
function kaydet() { E.kaydet(S); }
try { zoom = Math.max(0, Math.min(2, +(localStorage.getItem('adc_hk_zoom') || 1))); } catch (e) { zoom = 1; }
boyut(); ciz(); bilgiCiz();
var bk = S.ul[S.oy].bk; if (bk >= 0) setTimeout(function(){ ortala(bk); }, 0);
var rz; window.addEventListener('resize', function(){ clearTimeout(rz); rz = setTimeout(function(){ if (!document.body.contains(cv)) return; boyut(); ciz(); }, 200); });
if (S.olay || S.teklif !== null && S.teklif !== undefined) sirada();
if (E.ogretici) E.ogretici(sayfaAc, sayfaKapat);
};
})();
</script>
<?php
}, 24 );
