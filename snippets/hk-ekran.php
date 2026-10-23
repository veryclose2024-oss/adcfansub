<?php

/**
 * HK - ekran (yeni, 2026-10-23)
 * Pencere ve menüler: HK logosu, unvan, Devam et / Yeni Sefer / Günün Seferi, devlet seçimi + zorluk, sefer sonu (puan dökümü,
 * sunucuya kayıt, rekor, XP, madalya), En iyiler (podyum), madalyalar, nasıl oynanır, ilk sefer tanıtımı.
 * Mini Games şeridindeki History King'e ya da akıştaki "Sen de oyna"ya basınca açılır. Gerekenler: HK, HK - harita, HK - motor, HK - zekâ, HK - oyun.
 */
add_action( 'wp_footer', function() {
    if ( ! function_exists( 'adc_hk_sayfa' ) || ! adc_hk_sayfa() ) { return; }
    ?>
<script>
(function(){
var V = window.adcHK, M = window.adcHKMotor;
if (!V || !M || window.adcHKE) return;
function el(t, c, h) { var e = document.createElement(t); if (c) e.className = c; if (h !== undefined) e.innerHTML = h; return e; }
function esc(s) { return String(s).replace(/[&<>"]/g, function(c){ return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
function ajax(eylem, veri, cb) {
var fd = new FormData(); fd.append('action', eylem); fd.append('n', V.nonce);
for (var k in veri) fd.append(k, veri[k]);
fetch(V.ajax, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function(r){ return r.json(); })
.then(function(j){ cb(j && j.success ? j.data : null); }).catch(function(){ cb(null); });
}
var KAYIT = 'adc_hk_kayit';
function oku() { try { var s = JSON.parse(localStorage.getItem(KAYIT) || 'null'); return s && s.v === 1 && !s.bitti ? s : null; } catch (e) { return null; } }
function unvan(p) { var u = V.unvanlar[0]; V.unvanlar.forEach(function(x){ if (p >= x.esik) u = x; }); return u; }
var kutu, panel, govde, bas;
var E = window.adcHKE = { V: V, el: el };
function kur() {
if (kutu) return;
kutu = el('div', 'adc-hk'); panel = el('div', 'adc-hk-panel');
bas = el('div', 'adc-hk-bas', '<span class="adc-hk-baslik">' + V.logo_kucuk + ' HISTORY KING</span><span><button type="button" class="adc-hk-x" data-m title="Menü">☰</button><button type="button" class="adc-hk-x" data-k aria-label="Kapat">✕</button></span>');
govde = el('div', 'adc-hk-govde');
panel.appendChild(bas); panel.appendChild(govde); kutu.appendChild(panel); document.body.appendChild(kutu);
E.panel = panel; E.govde = govde;
bas.addEventListener('click', function(e){
if (e.target.closest('[data-k]')) kapat();
else if (e.target.closest('[data-m]')) menu();
});
kutu.addEventListener('click', function(e){ if (e.target === kutu && !panel.classList.contains('adc-hk-tam')) kapat(); });
document.addEventListener('keydown', function(e){ if (e.key === 'Escape' && kutu.style.display === 'flex') kapat(); });
}
function ac() { kur(); kutu.style.display = 'flex'; document.documentElement.style.overflow = 'hidden'; menu(); }
function kapat() { if (kutu) kutu.style.display = 'none'; document.documentElement.style.overflow = ''; }
function temizle() { govde.onclick = null; panel.classList.remove('adc-hk-tam'); var s = panel.querySelector('.adc-hk-sayfa'); if (s) s.remove(); govde.innerHTML = ''; govde.scrollTop = 0; }
E.kaydet = function(S){ try { localStorage.setItem(KAYIT, JSON.stringify(S)); } catch (e) {} };
function menu() {
temizle();
var k = oku(), rekor = V.giris ? V.rekor : yerelRekor(), u = unvan(rekor), sonraki = null;
V.unvanlar.forEach(function(x){ if (!sonraki && x.esik > rekor) sonraki = x; });
var g = M.gununSeferi(V.bugun), gu = V.uluslar[g.ulus];
var h = '<div class="adc-hk-vitrin">' + V.logo + '<p>Tarihin büyük devletleri aynı haritada. Hangisini zafere taşıyacaksın?</p></div>'
+ '<div class="adc-hk-unvan"><b>' + u.ad + '</b><small>' + (rekor ? 'Rekorun ' + rekor + ' puan' : 'Henüz sefer yok') + (sonraki ? ' · ' + sonraki.ad + ' için ' + sonraki.esik : '') + '</small>'
+ (sonraki ? '<i><em style="width:' + Math.min(100, Math.round((rekor - u.esik) / (sonraki.esik - u.esik) * 100)) + '%"></em></i>' : '') + '</div>';
if (k) h += '<button type="button" class="adc-hk-btn adc-hk-buyuk" data-a="devam">▶ Devam et <small>' + esc(V.uluslar[k.oy].ad) + ' · ' + (k.tur + 1) + '. tur' + (k.gunluk ? ' · günün seferi' : '') + '</small></button>';
h += '<button type="button" class="adc-hk-btn adc-hk-buyuk ' + (k ? 'adc-hk-ikinci' : '') + '" data-a="yeni">⚔ Yeni Sefer</button>'
+ '<button type="button" class="adc-hk-gunluk' + (V.gunluk_bitti ? ' adc-hk-tamam2' : '') + '" data-a="gun"><span class="adc-hk-renk" style="background:' + gu.renk + '"></span><span><b>📅 Günün Seferi' + (V.gunluk_bitti ? ' ✓' : '') + '</b>'
+ '<small>' + esc(gu.ad) + ' ile 30 turda <b>' + esc(M.ek(M.IL[g.hedef].ad, 'i')) + '</b> al · herkes aynı haritada · +100 puan' + (V.xp ? ', +' + V.xp + ' XP' : '') + '</small></span></button>'
+ '<div class="adc-hk-sekmeler"><button type="button" class="adc-hk-btn" data-a="sira">🏆 En iyiler</button><button type="button" class="adc-hk-btn" data-a="madalya">🎖 Madalyalar</button><button type="button" class="adc-hk-btn" data-a="nasil">❓ Nasıl?</button></div>';
if (!V.giris) h += '<p class="adc-hk-ipucu">Misafir olarak oynayabilirsin. Puanın sıralamaya girsin istersen <a href="' + V.giris_link + '">giriş yap</a>.</p>';
govde.innerHTML = h;
govde.onclick = function(e){
var b = e.target.closest('[data-a]'); if (!b) return;
var a = b.getAttribute('data-a');
if (a === 'devam') oyna(oku());
else if (a === 'yeni') { if (oku() && !confirm('Kayıtlı seferin silinecek. Yeni sefer başlasın mı?')) return; ulusSec(); }
else if (a === 'gun') { if (oku() && !confirm('Kayıtlı seferin silinecek. Günün seferi başlasın mı?')) return; baslat(g.ulus, 1, g); }
else if (a === 'sira') siralama('genel');
else if (a === 'madalya') madalyalar();
else if (a === 'nasil') nasil();
};
}
function yerelRekor() { try { return +localStorage.getItem('adc_hk_rekor') || 0; } catch (e) { return 0; } }
function ulusSec() {
temizle();
var z = 1, secili = -1;
try { z = +(localStorage.getItem('adc_hk_zorluk') || 1); } catch (e) {}
var h = '<h3 class="adc-hk-h">Devletini seç</h3><div class="adc-hk-ulusgrid">';
V.uluslar.forEach(function(u, i){
h += '<button type="button" class="adc-hk-ulkart" data-u="' + i + '" style="--u:' + u.renk + '"><b>' + esc(u.ad) + '</b><em>' + esc(u.lider) + '</em>'
+ '<small>' + esc(u.g1) + '</small><small>' + esc(u.g2) + '</small><i class="adc-hk-z' + u.zorluk + '">' + ['Kolay', 'Orta', 'Zor'][u.zorluk] + ' başlangıç</i></button>';
});
h += '</div><h3 class="adc-hk-h">Zorluk</h3><div class="adc-hk-zorluk">';
M.ZORLUK.forEach(function(x, i){ h += '<button type="button" class="adc-hk-btn" data-z="' + i + '"><b>' + x.ad + '</b><small>puan ×' + x.carpan + '</small></button>'; });
h += '</div><p class="adc-hk-ipucu" data-zacik></p><button type="button" class="adc-hk-btn adc-hk-buyuk" data-basla disabled>Önce bir devlet seç</button>';
govde.innerHTML = h;
var acik = ['Yapay zekâ yavaş, sana savaş açmayı pek sevmez. Yeni başlayanlar için.', 'Dengeli dünya. Hata yaparsan komşular fırsatı kaçırmaz.', 'Yapay zekâ zengin, cesur ve hırslı. Gerçek stratejistler için.'];
function yenile() {
govde.querySelectorAll('[data-z]').forEach(function(b){ b.classList.toggle('adc-hk-secili', +b.getAttribute('data-z') === z); });
govde.querySelectorAll('[data-u]').forEach(function(b){ b.classList.toggle('adc-hk-secili', +b.getAttribute('data-u') === secili); });
govde.querySelector('[data-zacik]').textContent = acik[z];
var bb = govde.querySelector('[data-basla]'); bb.disabled = secili < 0;
bb.textContent = secili < 0 ? 'Önce bir devlet seç' : '⚔ ' + V.uluslar[secili].ad + ' ile başla';
}
govde.onclick = function(e){
var u = e.target.closest('[data-u]'), zz = e.target.closest('[data-z]'), b = e.target.closest('[data-basla]');
if (u) secili = +u.getAttribute('data-u');
if (zz) { z = +zz.getAttribute('data-z'); try { localStorage.setItem('adc_hk_zorluk', z); } catch (x) {} }
if (b && secili >= 0) { baslat(secili, z, null); return; }
yenile();
};
yenile();
}
function baslat(ulus, z, gun) {
function kur2(seed) {
var S = M.yeniOyun({ ulus: ulus, zorluk: z, seed: seed, gunluk: !!gun, hedef: gun ? gun.hedef : -1 });
E.kaydet(S); oyna(S);
}
if (!V.giris) { kur2(gun ? gun.seed : 'm' + Date.now() + Math.random()); return; }
govde.innerHTML = '<p class="adc-hk-bos">⏳ Harita hazırlanıyor…</p>';
ajax('adc_hk_basla', { ulus: ulus, zorluk: z, gunluk: gun ? 1 : 0 }, function(d){
if (!d) { govde.innerHTML = '<p class="adc-hk-bos">Bağlantı sorunu. Biraz sonra tekrar dene.</p>'; return; }
kur2(d.seed);
});
}
function oyna(S) { if (!S) { menu(); return; } temizle(); E.S = S; window.adcHKOyun(E, S); }
E.bitir = function(S) {
try { localStorage.removeItem(KAYIT); } catch (e) {}
var r = M.sonuc(S), ul = V.uluslar[S.oy];
temizle();
var bas2 = S.bitti === 'kral' ? '👑 TARİHİN KRALI!' : S.bitti === 'yenildi' ? '☠ DEVLET YIKILDI' : '📜 SEFER TAMAMLANDI';
var h = '<div class="adc-hk-son"><div class="adc-hk-buyukyazi">' + bas2 + '</div><p>' + esc(ul.ad) + ' · ' + esc(ul.lider) + ' · ' + M.ZORLUK[S.z].ad + '</p>'
+ '<div class="adc-hk-puan">' + r.puan + '</div><div class="adc-hk-dokum">';
r.dokum.satir.forEach(function(x){ h += '<div><span>' + x[0] + '</span><b class="' + (x[1] < 0 ? 'adc-hk-kirmizi' : '') + '">' + (x[1] > 0 ? '+' : '') + x[1] + '</b></div>'; });
if (r.dokum.carpan !== 1) h += '<div><span>Zorluk çarpanı</span><b>×' + r.dokum.carpan + '</b></div>';
h += '<div><span>Toplam</span><b>' + r.puan + '</b></div></div><div class="adc-hk-odul" data-odul>' + (V.giris ? '⏳ Puanın kaydediliyor…' : '') + '</div>'
+ '<p class="adc-hk-ipucu">' + yorum(r, S) + '</p>'
+ '<div class="adc-hk-eylem"><button type="button" class="adc-hk-btn adc-hk-buyuk" data-a="yeni">⚔ Yeni sefer</button><button type="button" class="adc-hk-btn" data-a="sira">🏆 En iyiler</button></div></div>';
govde.innerHTML = h;
govde.onclick = function(e){ var b = e.target.closest('[data-a]'); if (!b) return; if (b.getAttribute('data-a') === 'yeni') ulusSec(); else siralama('genel'); };
if (!V.giris) {
if (r.puan > yerelRekor()) { try { localStorage.setItem('adc_hk_rekor', r.puan); } catch (e) {} }
govde.querySelector('[data-odul]').innerHTML = 'Misafir olarak oynadın. <a href="' + V.giris_link + '">Giriş yap</a>, sıralamaya gir!';
return;
}
var veri = { seed: S.seed };
['il', 'baskent', 'zafer', 'deha', 'kayip', 'baris', 'altin', 'kendi', 'kral', 'tur', 'hedef', 'kaleKir', 'kisZafer', 'cephe', 'maxAltin', 'yenilgi'].forEach(function(k){ veri[k] = r[k]; });
ajax('adc_hk_bitti', veri, function(d){
var o = govde.querySelector('[data-odul]'); if (!o) return;
if (!d) { o.textContent = 'Bağlantı sorunu, puan kaydedilemedi.'; return; }
if (!d.sayildi) { o.textContent = 'Bu sefer sıralamaya sayılmadı (' + (d.neden || 'kontrol') + ').'; return; }
V.tablo = d.tablo; V.rekor = d.rekor_deger; V.benim = d.benim; if (d.gunluk_bitti) V.gunluk_bitti = true;
var p = [];
if (d.rekor) p.push('<span class="adc-hk-yeni">YENİ REKOR!</span>');
if (d.unvan_atladi) p.push('🎖 Yeni unvan: <b>' + esc(d.unvan) + '</b>');
if (d.xp) p.push('+' + d.xp + ' XP');
(d.yeni || []).forEach(function(id){ var m = V.madalyalar[id]; if (m) p.push(m.ikon + ' ' + esc(m.ad)); });
o.innerHTML = p.length ? p.join(' · ') : 'Puanın kaydedildi.';
});
};
function yorum(r, S) {
if (S.bitti === 'yenildi') return 'Her büyük komutan bir gün kaybeder. Bir dahakine başkentini boş bırakma ve aynı anda iki cephede savaşma.';
if (r.kral) return 'Haritanın yarısı senin! Tarih seni "History King" olarak yazacak.';
if (r.deha >= 5) return 'Az askerle çok zafer: gerçek bir strateji dehası!';
if (r.kayip > r.zafer * 6) return 'Zaferler pahalıya patladı. Saldırmadan önce askerleri tek ilde toplamak kayıpları çok azaltır.';
if (r.il < 5) return 'Topraklar dar kaldı. Bağımsız iller savaş ilanı gerektirmez, erken yıllarda onlarla büyü.';
return 'Güzel sefer! Daha zor bir dünyada denemeye ne dersin?';
}
function siralama(sekme) {
temizle();
var T = V.tablo || { genel: [], bugun: [] }, L = sekme === 'genel' ? T.genel : T.bugun;
var h = '<div class="adc-hk-sekmeler"><button type="button" class="adc-hk-btn' + (sekme === 'bugun' ? ' adc-hk-secili' : '') + '" data-s="bugun">Bugün</button><button type="button" class="adc-hk-btn' + (sekme === 'genel' ? ' adc-hk-secili' : '') + '" data-s="genel">Tüm zamanlar</button></div>';
if (!L || !L.length) h += '<p class="adc-hk-bos">Henüz kimse yok. İlk tarihi sen yaz!</p>';
else {
h += '<div class="adc-hk-podyum">';
[1, 0, 2].forEach(function(i){
var k = L[i]; if (!k) { h += '<div class="adc-hk-basamak"></div>'; return; }
h += '<div class="adc-hk-basamak adc-hk-b' + (i + 1) + '"><img class="adc-hk-av adc-hk-av' + i + '" src="' + esc(k.resim) + '" alt="" width="' + (i ? 40 : 52) + '" height="' + (i ? 40 : 52) + '"><div class="adc-hk-isim">' + esc(k.ad) + '</div>'
+ '<div class="adc-hk-deger"><span class="adc-hk-renk" style="background:' + (V.uluslar[k.ulus] ? V.uluslar[k.ulus].renk : '#ccc') + '"></span>' + k.deger + '</div><small class="adc-hk-unv">' + esc(k.unvan) + '</small><div class="adc-hk-blok">' + (i + 1) + '</div></div>';
});
h += '</div><div class="adc-hk-liste">';
L.slice(3).forEach(function(k, i){
h += '<div class="adc-hk-sira' + (k.ben ? ' adc-hk-ben' : '') + '"><span class="adc-hk-no">' + (i + 4) + '</span><img class="adc-hk-av" src="' + esc(k.resim) + '" alt="" width="28" height="28">'
+ '<span class="adc-hk-isim2">' + esc(k.ad) + ' <small>' + esc(k.unvan) + '</small></span><span class="adc-hk-renk" style="background:' + (V.uluslar[k.ulus] ? V.uluslar[k.ulus].renk : '#ccc') + '"></span><b>' + k.deger + '</b></div>';
});
h += '</div>';
}
var b = T.ben;
if (!V.giris) h += '<div class="adc-hk-benim">Sen de listeye girmek ister misin? <a href="' + V.giris_link + '">Giriş yap</a></div>';
else if (sekme === 'genel') h += '<div class="adc-hk-benim">' + (b && b.sira ? 'Sen: ' + b.sira + '. sıra · rekorun ' + b.rekor : 'Bir sefer bitir, listeye gir!') + '</div>';
else h += '<div class="adc-hk-benim">' + (b && b.gun_sira ? 'Bugün ' + b.gun_sira + '. sıradasın · ' + b.gun_skor + ' puan' : 'Bugün bir sefer bitir, listeye gir!') + '</div>';
h += '<p class="adc-hk-ipucu">Tüm zamanlarda ilk 3 taçlı <b>HK</b> rozeti kazanır. Unvanlar: ' + V.unvanlar.map(function(x){ return x.ad + ' ' + x.esik; }).join(' · ') + '. <b>Bugün</b> her gece sıfırlanır.</p>'
+ '<button type="button" class="adc-hk-btn" data-geri>← Menü</button>';
govde.innerHTML = h;
govde.onclick = function(e){ var s = e.target.closest('[data-s]'); if (s) siralama(s.getAttribute('data-s')); if (e.target.closest('[data-geri]')) menu(); };
}
function madalyalar() {
temizle();
var benim = V.benim || [], h = '<h3 class="adc-hk-h">🎖 Madalyalar · ' + benim.length + '/' + Object.keys(V.madalyalar).length + '</h3><div class="adc-hk-madalyalar">';
for (var id in V.madalyalar) {
var m = V.madalyalar[id], var2 = benim.indexOf(id) >= 0;
h += '<div class="adc-hk-mdl' + (var2 ? '' : ' adc-hk-yok') + '"><span>' + m.ikon + '</span><b>' + esc(m.ad) + '</b>' + esc(m.acik) + '</div>';
}
govde.innerHTML = h + '</div>' + (V.giris ? '' : '<p class="adc-hk-ipucu">Madalyalar üyelere verilir.</p>') + '<button type="button" class="adc-hk-btn" data-geri>← Menü</button>';
govde.onclick = function(e){ if (e.target.closest('[data-geri]')) menu(); };
}
var NASIL = '<h3 class="adc-hk-h">❓ Nasıl oynanır?</h3><ol class="adc-hk-nasil">'
+ '<li><b>Devletini seç.</b> Her devletin iki tarihî gücü var (Osmanlı\'nın Şahi topları, Rusya\'nın General Kış\'ı…).</li>'
+ '<li><b>İline dokun.</b> Hazır askerin varsa komşu illerde <b>zafer şansı</b> yazar. Bir komşuya dokun, asker sayısını seç, saldır.</li>'
+ '<li><b>Kalabalık ordu az kayıp verir.</b> 10 askerle 5\'e saldırırsan 1-2 kaybın olur; 6 ile 5\'e saldırırsan neredeyse hepsi gider. Askerleri tek ilde topla!</li>'
+ '<li><b>Arazi ve mevsim önemli.</b> Dağ, orman, kale ve başkent savunanı güçlendirir. Kışın saldırmak zordur (Rusya\'ya hiç saldırma).</li>'
+ '<li><b>Çok yönlü saldırı:</b> hedefe komşu her ilin +%10 destek verir. Düşman ilini kendi topraklarından kopar: kuşatılan il zayıflar.</li>'
+ '<li><b>Ekonomi:</b> iller altın verir, askerler maaş ister (her 4 asker 1 altın). Hazine boşalırsa ordu dağılır. Pazar gelir, kışla asker, kale savunma verir.</li>'
+ '<li><b>İkmal:</b> başkentle bağı kopan il gelir vermez, askerleri erir. Yeni fethedilen il birkaç tur uyum sağlar.</li>'
+ '<li><b>Diplomasi:</b> bağımsız illere savaş ilanı gerekmez. Devletlere önce savaş ilan edilir. Her fetih <b>tehdit</b> yaratır; 15\'te komşular koalisyon kurar. Kazanınca barış iste!</li>'
+ '<li><b>Ulusal odak, olaylar ve 💡 danışman</b> sana yol gösterir. 30 tur (7,5 yıl) sonunda puanın hesaplanır. Haritanın yarısını alırsan erken biter: <b>Tarihin Kralı</b>!</li></ol>'
+ '<p class="adc-hk-ipucu">Puan: il ×10, ele geçen başkent ×40, zafer ×2, sayıca azken kazanılan zafer ×8, kazanılan barış ×20, hazine ÷10, kayıp ÷4 eksi. Kendi başkentini kaybedersen -100. Zorluk çarpanı: Acemi ×0,5 · Komutan ×1 · Dâhi ×1,5.</p>'
+ '<p class="adc-hk-ipucu">Bu oyun tarihi bir "ya olsaydı?" oyunudur: farklı çağların devletleri aynı haritada buluşur. Gerçek olayları ya da halkları küçümsemez.</p>';
function nasil() { temizle(); govde.innerHTML = NASIL + '<button type="button" class="adc-hk-btn" data-geri>← Menü</button>'; govde.onclick = function(e){ if (e.target.closest('[data-geri]')) menu(); }; }
E.ogretici = function(ac2, kapat2) {
try { if (localStorage.getItem('adc_hk_ogretici')) return; localStorage.setItem('adc_hk_ogretici', '1'); } catch (e) { return; }
var adim = [
['👑 Hoş geldin, hükümdar!', 'Haritada <b>mavi çerçeveli sayılar</b> senin ordularındır. ★ başkentindir. Bej iller bağımsızdır, savaş ilanı gerekmez.'],
['⚔ Saldırı', 'Bir iline dokun: komşularda <b>zafer şansı</b> çıkar. Kalabalık orduyla saldır, az kayıp ver. Dağ, kale ve kış savunanı korur.'],
['▶ Tur', 'İşin bitince <b>Turu bitir</b>. Diğer devletler de hamle yapar. 💡 Danışman takıldığında yardım eder. Bol şans!']
], i = 0;
function goster() {
var k = ac2('<div class="adc-hk-olay"><small>' + (i + 1) + '/3</small><h3>' + adim[i][0] + '</h3><p>' + adim[i][1] + '</p></div><button type="button" class="adc-hk-btn adc-hk-tamam" data-x>' + (i < 2 ? 'İleri ▶' : 'Başla!') + '</button>', false);
k.querySelector('[data-x]').addEventListener('click', function(){ i++; if (i < 3) goster(); else kapat2(); });
}
goster();
};
document.addEventListener('click', function(e){
var a = e.target.closest && e.target.closest('[data-adc-oyun="hk"],a[href$="#hk"]');
if (!a) return;
e.preventDefault(); ac();
});
if (location.hash === '#hk') setTimeout(ac, 0);
E.ac = ac;
})();
</script>
<?php
}, 25 );
