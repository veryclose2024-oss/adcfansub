<?php

/**
 * HK - zekâ (yeni, 2026-10-23)
 * Yapay zekâ devletlerin turu (asker, inşa, saldırı, savaş ilanı), tarihî olaylar (iki seçenekli kararlar, devlete özel olanlar
 * dahil) ve 💡 danışman ipuçları. Gerekenler: HK, HK - harita, HK - motor.
 */
add_action( 'wp_footer', function() {
    if ( ! function_exists( 'adc_hk_sayfa' ) || ! adc_hk_sayfa() ) { return; }
    ?>
<script>
(function(){
var M = window.adcHKMotor;
if (!M || M.aiTur) return;
var _ = M._, IL = M.IL, N = M.N, U = M.U, AYAR = M.AYAR, ODAK = M.ODAK, ZORLUK = M.ZORLUK, UL = M.UL;
var oz = _.oz, rnd = _.rnd, ulusAd = _.ulusAd, deger = _.deger, sinirMi = _.sinirMi, mesafeSinir = _.mesafeSinir, sinirGucu = _.sinirGucu;
var yaz = M.yaz, iller = M.iller, asker = M.asker, tahmin = M.tahmin, hamle = M.hamle, topla = M.topla, insa = M.insa, fiyat = M.fiyat,
odakSec = M.odakSec, savasIlan = M.savasIlan, komsuUlus = M.komsuUlus, tehditAltinda = M.tehditAltinda, gelirHesap = M.gelirHesap;
function aiTur(S, u) {
var L = S.ul[u], z = ZORLUK[S.z];
if (L.olu) return;
if (L.odak < 0) { var ss = [0, 1, 5, 2, 3, 4].filter(function(i){ return L.bitenOdak.indexOf(i) < 0; }); if (ss.length) odakSec(S, u, ss[Math.floor(rnd(S) * Math.min(2, ss.length))]); }
var benim = iller(S, u);
if (L.altin > 28 && L.bk >= 0 && !S.il[L.bk].kale && tehditAltinda(S, L.bk) > 0) insa(S, L.bk, 'kale');
else if (L.altin > 34) {
var en = -1, ev = 0; benim.forEach(function(k){ if (!S.il[k].b && S.il[k].u === 0 && IL[k].gelir > ev) { ev = IL[k].gelir; en = k; } });
if (en >= 0) insa(S, en, 'pazar');
}
var sira = benim.map(function(k){ return { k: k, v: (sinirMi(S, k) ? 4 : 0) + tehditAltinda(S, k) * 1.2 - S.il[k].a + (S.ul[u].bk === k ? 3 : 0) + rnd(S) }; });
sira.sort(function(a, b){ return b.v - a.v; });
for (var g = 0; g < 4 && L.altin >= fiyat(S, u, 'asker') + 3; g++) {
var al = 0; sira.forEach(function(o){ if (L.altin >= fiyat(S, u, 'asker') + 3) al += topla(S, o.k, 2); });
if (!al) break;
}
for (var tur = 0; tur < 8; tur++) {
var best = null;
iller(S, u).forEach(function(f){
var F = S.il[f]; if (F.h < 1) return;
var tutulacak = Math.min(F.h, Math.ceil(tehditAltinda(S, f) * 0.6));
IL[f].komsu.forEach(function(t){
var T = S.il[t]; if (T.s === u || (T.s >= 0 && !S.sv[u][T.s])) return;
var n = T.s >= 0 && tehditAltinda(S, f) && tehditAltinda(S, f) >= F.h ? F.h : F.h - tutulacak;
if (n < 1) return;
var tm = tahmin(S, f, t, n);
if (tm.p < z.esik + (T.s < 0 ? 0.05 : 0)) return;
var v = deger(S, u, t) * tm.p - tm.kayip * 0.25;
if (!best || v > best.v) best = { f: f, t: t, n: n, v: v };
});
});
if (!best) break;
var R = hamle(S, best.f, best.t, best.n);
if (R && R.d === S.oy) { S.ist.saldiri = (S.ist.saldiri || 0) + 1; yaz(S, (R.zafer ? '🔥 ' + ulusAd(u) + ' ' + M.ek(IL[best.t].ad, 'i') + ' aldı!' : '🛡 ' + IL[best.t].ad + ' ' + ulusAd(u) + ' saldırısını püskürttü.'), R.zafer ? 'kotu' : 'iyi'); }
else if (R && R.zafer && R.d >= 0 && S.ul[R.d].bk !== best.t && IL[best.t].gelir >= 4) yaz(S, '🏳 ' + ulusAd(u) + ', ' + M.ek(IL[best.t].ad, 'i') + ' ' + M.ek(ulusAd(R.d), 'den') + ' aldı.', 'dunya');
}
var uzak = mesafeSinir(S, u);
iller(S, u).forEach(function(f){
var F = S.il[f]; if (F.h < 1 || uzak[f] === 0) return;
var hedef = -1, md = uzak[f];
IL[f].komsu.forEach(function(t){ if (S.il[t].s === u && uzak[t] < md) { md = uzak[t]; hedef = t; } });
var kal = f === L.bk ? Math.min(F.h, 3) : 0;
if (hedef >= 0 && F.h - kal > 0) hamle(S, f, hedef, F.h - kal);
});
for (var j = 0; j < U; j++) {
if (j === u || S.ul[j].olu || S.sv[u][j] || S.at[u][j] > S.tur || !komsuUlus(S, u, j)) continue;
var oran = asker(S, u) / Math.max(1, asker(S, j)), yerel = sinirGucu(S, u, j) / Math.max(1, sinirGucu(S, j, u)), sans2 = j === S.oy ? z.agr : 0.05;
if (S.tur < 3) sans2 = 0;
if (j === S.oy) { var mesgul = 0; for (var x = 0; x < U; x++) if (S.sv[x][S.oy] || (x !== j && S.sv[u][x])) mesgul++; if (mesgul) sans2 *= S.z === 2 ? 0.35 : 0.1; }
if (oran > 1.1 && yerel > 1.5 && rnd(S) < sans2 * Math.min(2, yerel - 0.5)) savasIlan(S, u, j);
}
}
var OLAY = [
{ id: 'veba', b: 'Salgın Hastalık', m: 'Limanlardan bir salgın yayılıyor. Hekimler karantina istiyor.',
a: ['Karantina (-8 altın)', function(S){ S.ul[S.oy].altin -= 8; }], c: ['Görmezden gel (en kalabalık ordu -%30, moral -5)', function(S){ var k = enKalabalik(S); if (k >= 0) { var x = Math.ceil(S.il[k].a * 0.3); S.il[k].a -= x; S.il[k].h = Math.min(S.il[k].h, S.il[k].a); S.ist.kayip += x; } S.ul[S.oy].moral -= 5; }] },
{ id: 'hasat', b: 'Bereketli Hasat', m: 'Ambarlar taşıyor. Fazlayı ne yapalım?',
a: ['Hazineye (+12 altın)', function(S){ S.ul[S.oy].altin += 12; }], c: ['Halka dağıt (moral +10)', function(S){ S.ul[S.oy].moral = Math.min(100, S.ul[S.oy].moral + 10); }] },
{ id: 'mimar', b: 'Usta Mimar', m: 'Ünlü bir mimar sarayın kapısında. Ne inşa etsin?',
a: ['Başkente kale (bedava)', function(S){ var k = S.ul[S.oy].bk; if (k >= 0) S.il[k].kale = 1; }], c: ['En zengin ile pazar (bedava)', function(S){ var k = enZengin(S); if (k >= 0) S.il[k].b = 'p'; }] },
{ id: 'barut', b: 'Barut Devrimi', m: 'Yeni toplar ve tüfekler savaşın kurallarını değiştiriyor.',
a: ['Orduyu yenile (-15 altın, saldırı +%10)', function(S){ S.ul[S.oy].altin -= 15; bon(S, 'sal', 0.1); }], c: ['Eski usul devam (bir şey olmaz)', function(){}] },
{ id: 'matbaa', b: 'Matbaa', m: 'Kitaplar artık hızla basılabiliyor. Bilgi yayılınca devlet de güçlenir.',
a: ['Destekle (-10 altın, her tur +2 altın)', function(S){ S.ul[S.oy].altin -= 10; bon(S, 'gelir', 2); }], c: ['Sonra bakarız', function(){}] },
{ id: 'kurak', b: 'Kuraklık', m: 'Yağmur yağmadı, tarlalar kurudu.',
a: ['Tahıl satın al (-10 altın)', function(S){ S.ul[S.oy].altin -= 10; }], c: ['Kemerleri sık (3 tur gelir -%30)', function(S){ S.ul[S.oy].kitlik = S.tur + 3; }] },
{ id: 'komutan', b: 'Genç Bir Komutan', m: 'Ordudan parlak bir subay çıktı. Askerler onu çok seviyor.',
a: ['Orduya ata (saldırı +%8)', function(S){ bon(S, 'sal', 0.08); }], c: ['Sınır valisi yap (savunma +%8)', function(S){ bon(S, 'sav', 0.08); }] },
{ id: 'kervan', b: 'İpek Yolu Kervanı', m: 'Zengin bir kervan topraklarından geçmek istiyor.',
a: ['Gümrük al (+10 altın)', function(S){ S.ul[S.oy].altin += 10; }], c: ['Ticareti teşvik et (her tur +1 altın)', function(S){ bon(S, 'gelir', 1); }] },
{ id: 'gerginlik', b: 'Sınır Gerginliği', m: 'Komşu elçiler sert bir mektup gönderdi: "Fetihlerin bizi korkutuyor."',
kosul: function(S){ return S.ul[S.oy].tehdit >= 7; },
a: ['Geri adım at (tehdit -4, moral -5)', function(S){ S.ul[S.oy].tehdit = Math.max(0, S.ul[S.oy].tehdit - 4); S.ul[S.oy].moral -= 5; }], c: ['Diren (moral +5, tehdit +2)', function(S){ S.ul[S.oy].moral += 5; S.ul[S.oy].tehdit += 2; }] },
{ id: 'isyan', b: 'Askerler Huzursuz', m: 'Maaşlar gecikti, kışlalarda söylenmeler başladı.',
kosul: function(S){ return S.ul[S.oy].moral < 45; },
a: ['Bahşiş dağıt (-12 altın, moral +15)', function(S){ S.ul[S.oy].altin -= 12; S.ul[S.oy].moral += 15; }], c: ['Disiplin (başkentte 3 asker ayrılır)', function(S){ var k = S.ul[S.oy].bk; if (k >= 0) { var x = Math.min(3, S.il[k].a); S.il[k].a -= x; S.il[k].h = Math.min(S.il[k].h, S.il[k].a); } }] },
{ id: 'gonullu', b: 'Gönüllüler', m: 'Halk zaferlerini duydu, gençler orduya katılmak istiyor.',
kosul: function(S){ return S.ist.zafer >= 3; },
a: ['Kabul et (başkente +4 asker)', function(S){ var k = S.ul[S.oy].bk; if (k >= 0) S.il[k].a += 4; }], c: ['Tarlalara dönsünler (+8 altın)', function(S){ S.ul[S.oy].altin += 8; }] },
{ id: 'yeniceri', u: 'osmanli', b: 'Yeniçeri Ocağı', m: 'Yeniçeriler cülus bahşişi bekliyor.',
a: ['Bahşişi ver (-10 altın, moral +12)', function(S){ S.ul[S.oy].altin -= 10; S.ul[S.oy].moral += 12; }], c: ['Ocağı ıslah et (saldırı +%5, moral -8)', function(S){ bon(S, 'sal', 0.05); S.ul[S.oy].moral -= 8; }] },
{ id: 'sinan', u: 'osmanli', b: 'Mimar Sinan', m: '"Çıraklık eserim" dediği caminin ardından şimdi saray için çalışmak istiyor.',
a: ['Külliye yaptır (moral +10, her tur +1 altın)', function(S){ S.ul[S.oy].moral += 10; bon(S, 'gelir', 1); }], c: ['Kale yaptır (başkent + sınırdaki bir il)', function(S){ var k = S.ul[S.oy].bk; if (k >= 0) S.il[k].kale = 1; var s = iller(S, S.oy).filter(function(i){ return sinirMi(S, i) && !S.il[i].kale; })[0]; if (s !== undefined) S.il[s].kale = 1; }] },
{ id: 'senato', u: 'roma', b: 'Senato Toplandı', m: 'Senatörler savaş bütçesini tartışıyor.',
a: ['Lejyonlara para (-10 altın, başkente +5 asker)', function(S){ S.ul[S.oy].altin -= 10; var k = S.ul[S.oy].bk; if (k >= 0) S.il[k].a += 5; }], c: ['Yollara para (her tur +2 altın)', function(S){ bon(S, 'gelir', 2); }] },
{ id: 'kurultay', u: 'mogol', b: 'Kurultay', m: 'Boy beyleri büyük meclise toplandı.',
a: ['Birlik yemini (moral +15)', function(S){ S.ul[S.oy].moral += 15; }], c: ['Yağız atlar (saldırı +%8)', function(S){ bon(S, 'sal', 0.08); }] },
{ id: 'sanayi', u: 'britanya', b: 'Buhar Makinesi', m: 'Fabrikalar dumanı tütmeye başladı.',
a: ['Yatırım yap (-15 altın, her tur +3 altın)', function(S){ S.ul[S.oy].altin -= 15; bon(S, 'gelir', 3); }], c: ['Donanmaya ver (deniz saldırısı +%15)', function(S){ bon(S, 'denizSal', 0.15); }] },
{ id: 'ansiklopedi', u: 'fransa', b: 'Ansiklopedi', m: 'Düşünürler bilginin herkese açık olmasını istiyor.',
a: ['Destekle (moral +10, tehdit -3)', function(S){ S.ul[S.oy].moral += 10; S.ul[S.oy].tehdit = Math.max(0, S.ul[S.oy].tehdit - 3); }], c: ['Sansürle (+6 altın, moral -5)', function(S){ S.ul[S.oy].altin += 6; S.ul[S.oy].moral -= 5; }] },
{ id: 'petro', u: 'rusya', b: 'Avrupa Gezisi', m: 'Çar kılık değiştirip tersanelerde çalışmış, yeni fikirlerle döndü.',
a: ['Reform (-12 altın, saldırı ve savunma +%6)', function(S){ S.ul[S.oy].altin -= 12; bon(S, 'sal', 0.06); bon(S, 'sav', 0.06); }], c: ['Gelenek (moral +8)', function(S){ S.ul[S.oy].moral += 8; }] },
{ id: 'filo', u: 'cin', b: 'Hazine Filosu', m: 'Dev gemiler uzak limanlara gitmeye hazır.',
a: ['Sefere gönder (-12 altın, her tur +3 altın)', function(S){ S.ul[S.oy].altin -= 12; bon(S, 'gelir', 3); }], c: ['Limanda kalsın (+10 altın)', function(S){ S.ul[S.oy].altin += 10; }] },
{ id: 'sakoku', u: 'japonya', b: 'Kapılar Kapansın mı?', m: 'Danışmanlar dış dünyaya kapanmayı öneriyor.',
a: ['Kapan (tehdit 0, her tur -1 altın)', function(S){ S.ul[S.oy].tehdit = 0; bon(S, 'gelir', -1); }], c: ['Açık kal (moral +6)', function(S){ S.ul[S.oy].moral += 6; }] },
{ id: 'kafile', u: 'ispanya', b: 'Gümüş Kafilesi', m: 'Okyanusun ötesinden gemiler geldi.',
a: ['Hazineye (+18 altın)', function(S){ S.ul[S.oy].altin += 18; }], c: ['Tersaneye (kıyı illeri +1 altın/tur)', function(S){ bon(S, 'kiyi', 1); }] },
{ id: 'nisf', u: 'safevi', b: 'Nısf-ı Cihan', m: '"İsfahan dünyanın yarısıdır." Meydan için büyük bir proje var.',
a: ['Meydanı yap (-12 altın, her tur +3 altın)', function(S){ S.ul[S.oy].altin -= 12; bon(S, 'gelir', 3); }], c: ['Orduya harca (başkente +5 asker)', function(S){ var k = S.ul[S.oy].bk; if (k >= 0) S.il[k].a += 5; }] }
];
function bon(S, k, v) { var b = S.ul[S.oy].bon; b[k] = (b[k] || 0) + v; }
function enKalabalik(S) { var e = -1, m = -1; iller(S, S.oy).forEach(function(k){ if (S.il[k].a > m) { m = S.il[k].a; e = k; } }); return e; }
function enZengin(S) { var e = -1, m = -1; iller(S, S.oy).forEach(function(k){ if (!S.il[k].b && IL[k].gelir > m) { m = IL[k].gelir; e = k; } }); return e; }
function olaySec(S) {
var o = S.goruldu || (S.goruldu = []), uid = UL[S.oy].id;
var aday = OLAY.filter(function(e){ return o.indexOf(e.id) < 0 && (!e.u || e.u === uid) && (!e.kosul || e.kosul(S)); });
if (!aday.length) return null;
var oz2 = aday.filter(function(e){ return e.u; });
var e = oz2.length && rnd(S) < 0.5 ? oz2[0] : aday[Math.floor(rnd(S) * aday.length)];
o.push(e.id);
return e.id;
}
function olayCevap(S, sec) {
var e = null; OLAY.forEach(function(x){ if (x.id === S.olay) e = x; });
S.olay = null; if (!e) return;
(sec ? e.c : e.a)[1](S);
var L = S.ul[S.oy]; L.moral = Math.max(5, Math.min(100, L.moral));
yaz(S, '📜 ' + e.b + ': ' + (sec ? e.c : e.a)[0].replace(/ \(.*\)$/, ''));
}
function danisman(S) {
var p = S.oy, out = [], best = null, L = S.ul[p];
iller(S, p).forEach(function(f){
if (S.il[f].h < 1) return;
IL[f].komsu.forEach(function(t){
var T = S.il[t]; if (T.s === p || (T.s >= 0 && !S.sv[p][T.s])) return;
var tm = tahmin(S, f, t, S.il[f].h);
if (tm.p < 0.72) return;
var v = deger(S, p, t) * tm.p - tm.kayip * 0.4;
if (!best || v > best.v) best = { f: f, t: t, n: S.il[f].h, p: tm.p, v: v };
});
});
var bk = L.bk, tb = bk >= 0 ? tehditAltinda(S, bk) : 0;
if (bk >= 0 && tb > S.il[bk].a) out.push({ m: '🛡 Başkentin ' + IL[bk].ad + ' tehlikede: sınırda ' + tb + ' düşman, sende ' + S.il[bk].a + ' asker. Asker topla ya da kale yap.', k: bk });
if (best) out.push({ m: '⚔ ' + IL[best.f].ad + ' → ' + IL[best.t].ad + ': ' + best.n + ' askerle zafer şansı %' + Math.round(best.p * 100) + '.', f: best.f, t: best.t });
else out.push({ m: '🧭 Şu an iyi bir saldırı yok. Askerleri tek ilde topla: kalabalık ordu daha az kayıp verir.' });
var ac = []; iller(S, p).forEach(function(k){ var t = tehditAltinda(S, k); if (t > S.il[k].a + 2 && k !== bk) ac.push(IL[k].ad); });
if (ac.length) out.push({ m: '⚠ Zayıf sınır: ' + ac.slice(0, 3).join(', ') + '. Düşman orada daha kalabalık.' });
if (L.tehdit >= AYAR.tehditSinir - 5) out.push({ m: '👀 Tehdit ' + L.tehdit + '/' + AYAR.tehditSinir + '. Biraz yavaşla ya da barış yap, yoksa koalisyon kurulur.' });
if (L.altin >= 30) out.push({ m: '💰 Hazinede ' + L.altin + ' altın var. Boşta duran altın savaş kazanmaz: asker topla ya da pazar kur.' });
if (L.odak < 0 && L.bitenOdak.length < ODAK.length) out.push({ m: '🎯 Ulusal odak seçmedin. 🎯 düğmesinden bir hedef seç.' });
var c = gelirHesap(S, p); if (c.ikmalsiz) out.push({ m: '📦 ' + c.ikmalsiz + ' ilin başkentle bağı kopuk: gelir vermiyor, askerleri eriyor.' });
var svs = 0; for (var j = 0; j < U; j++) if (S.sv[p][j]) svs++;
if (svs >= 2) out.push({ m: '🕊 ' + svs + ' cephede savaşıyorsun. İki cepheli savaş tehlikelidir, birinde barış iste.' });
return out;
}
function gununSeferi(tarih) {
var h = M.tohum('hk' + tarih), ulus = ((h % U) + U) % U, S = M.yeniOyun({ ulus: ulus, zorluk: 1, seed: 'gun' + tarih });
var bk = M.BASKENT[ulus], d = {}, q = [bk]; d[bk] = 0;
while (q.length) { var k = q.shift(); IL[k].komsu.forEach(function(j){ if (d[j] === undefined) { d[j] = d[k] + 1; q.push(j); } }); }
var aday = [];
for (var u = 0; u < U; u++) if (u !== ulus && d[M.BASKENT[u]] !== undefined && d[M.BASKENT[u]] <= 4) aday.push(M.BASKENT[u]);
if (!aday.length) for (var k2 = 0; k2 < N; k2++) if (d[k2] === 3 && IL[k2].gelir >= 3) aday.push(k2);
if (!aday.length) aday.push(M.BASKENT[(ulus + 1) % U]);
return { ulus: ulus, hedef: aday[((h >>> 3) % aday.length + aday.length) % aday.length], seed: 'gun' + tarih };
}
M.gununSeferi = gununSeferi;
M.aiTur = aiTur; M.OLAY = OLAY; M.olaySec = olaySec; M.olayCevap = olayCevap; M.danisman = danisman;
})();
</script>
<?php
}, 23 );
