(function(){
  // HK · History King motoru: kurallar, savaş, ekonomi, ikmal, diplomasi, yapay zekâ, olaylar, puan. Çizimden bağımsız.
  var HR = window.adcHKHarita;
  if (!HR || window.adcHKMotor) return;
  var W = HR.w, HH = HR.h, N = HR.il.length;
  var HUCRE = new Uint8Array(W * HH);
  (function(){ var p = 0, a = HR.alf, r = HR.rle;
    for (var i = 0; i < r.length; i += 2) { var v = a.indexOf(r[i]), n = a.indexOf(r[i + 1]) + 1; for (var j = 0; j < n; j++) HUCRE[p++] = v; } })();
  var IL = HR.il.map(function(x, k){ return { k: k, id: x[0], ad: x[1], lx: x[2], ly: x[3], arazi: x[4], gelir: x[5], ilk: x[6], kiyi: x[7], komsu: x[8] }; });
  var DENIZ = {};
  HR.deniz.forEach(function(d){ DENIZ[d[0] + '-' + d[1]] = DENIZ[d[1] + '-' + d[0]] = 1; });
  function denizMi(a, b) { return !!DENIZ[a + '-' + b]; }

  // Uluslar (sıra PHP'deki adc_hk_uluslar() ile aynı). oz = özel güçler
  var UL = [
    { id: 'osmanli',  oz: { kaleKir: 1, entegre: 1 } },
    { id: 'roma',     oz: { sav: 0.15, geri: 0.4, tehdit: 0.5 } },
    { id: 'mogol',    oz: { atli: 0.25, hizli: 1, yam: 1 }, bas: 1.5 },
    { id: 'britanya', oz: { deniz: 1, kiyi: 1 } },
    { id: 'fransa',   oz: { buyuk: 0.2, asker: 1 }, bas: 1.4 },
    { id: 'rusya',    oz: { kis: 1, toplama: 2 } },
    { id: 'cin',      oz: { kaleUcuz: 1, kaleGuc: 1, bereket: 0.2 } },
    { id: 'japonya',  oz: { sav: 0.2, deniz: 1, bushido: 1 }, bas: 1.25 },
    { id: 'ispanya',  oz: { altin: 4, sal: 0.1 } },
    { id: 'safevi',   oz: { ipek: 1, usta: 1, sal: 0.05, sav: 0.05 } }
  ];
  var U = UL.length;
  var BASKENT = [];
  IL.forEach(function(il){ if (il.ilk >= 0 && il.gelir >= 4 && BASKENT[il.ilk] === undefined) BASKENT[il.ilk] = il.k; });

  var AYAR = { tur: 30, asker: 3, kale: 10, pazar: 8, kisla: 8, toplama: 3, kislaT: 3, baskentT: 2, bakim: 4,
    tehditSinir: 15, ateskes: 8, kralOran: 0.5, uyum: 3, moral0: 70 };
  var MEVSIM = ['İlkbahar', 'Yaz', 'Sonbahar', 'Kış'];
  var ARAZI = { o: { ad: 'Ova', sav: 1 }, d: { ad: 'Dağ', sav: 1.5 }, f: { ad: 'Orman', sav: 1.25 }, c: { ad: 'Çöl', sav: 1.1 }, b: { ad: 'Bozkır', sav: 1 } };
  var ZORLUK = [
    { ad: 'Acemi',   carpan: 0.5, aiGelir: 0.75, esik: 0.8,  agr: 0.04, oyuncuGelir: 1.2 },
    { ad: 'Komutan', carpan: 1,   aiGelir: 1,    esik: 0.7,  agr: 0.09, oyuncuGelir: 1 },
    { ad: 'Dâhi',    carpan: 1.5, aiGelir: 1.3,  esik: 0.62, agr: 0.16, oyuncuGelir: 1 }
  ];
  // Ulusal odaklar: 5 ortak + 1 ulusa özel (ad PHP'den gelir). gun = kaç tur sürer
  var ODAK = [
    { id: 'hazine', ad: 'Hazine Reformu', acik: '+3 altın her tur', gun: 3, e: { gelir: 3 } },
    { id: 'ordu', ad: 'Ordu Reformu', acik: 'Saldırı +%15', gun: 3, e: { sal: 0.15 } },
    { id: 'kale', ad: 'Sınır Kaleleri', acik: 'Kale yarı fiyat, savunma +%10', gun: 3, e: { kaleUcuz: 1, sav: 0.1 } },
    { id: 'seferberlik', ad: 'Seferberlik', acik: 'Asker 1 altın ucuz, ilde +1 toplama', gun: 3, e: { asker: 1, toplama: 1 } },
    { id: 'diplomasi', ad: 'Diplomasi Okulu', acik: 'Tehdit 2 kat hızlı erir, barış kolaylaşır', gun: 3, e: { diplo: 1 } },
    { id: 'ozel', ad: '', acik: '', gun: 5, e: null }
  ];
  var OZEL_ODAK = {
    osmanli: { gelir: 2, entegre: 1, moral: 15 }, roma: { tehditSifir: 1, moral: 15, sav: 0.1 }, mogol: { moral: 20, sal: 0.1 },
    britanya: { gelir: 3, denizSal: 0.2 }, fransa: { gelir: 3, moral: 10 }, rusya: { gelir: 3, asker: 1 },
    cin: { gelir: 4 }, japonya: { moral: 20, sav: 0.1 }, ispanya: { gelir: 3, moral: 10 }, safevi: { gelir: 4 }
  };

  // Tohumlu rastgele (oyun kaydı devam edince aynı akış sürsün)
  function rnd(S) { var t = (S.r = (S.r + 0x6D2B79F5) | 0); t = Math.imul(t ^ (t >>> 15), t | 1); t ^= t + Math.imul(t ^ (t >>> 7), t | 61); return ((t ^ (t >>> 14)) >>> 0) / 4294967296; }
  function tohum(s) { var h = 2166136261; s = String(s); for (var i = 0; i < s.length; i++) { h ^= s.charCodeAt(i); h = Math.imul(h, 16777619); } return h | 0; }

  function yeniOyun(o) {
    var S = { v: 1, tur: 0, oy: o.ulus | 0, z: Math.max(0, Math.min(2, o.zorluk | 0)), seed: String(o.seed || Date.now()), gunluk: !!o.gunluk,
      hedef: o.hedef === undefined ? -1 : o.hedef, il: [], ul: [], sv: [], at: [], sk: [], log: [], bitti: 0, olay: null, teklif: null,
      ist: { zafer: 0, yenilgi: 0, deha: 0, kayip: 0, fetih: 0, baris: 0, kaleKir: 0, kisZafer: 0, cephe: 0, maxAltin: 0, baskentAldi: 0 } };
    S.r = tohum(S.seed);
    IL.forEach(function(il){
      var s = il.ilk, a, kale = 0;
      if (s >= 0) a = Math.round((BASKENT[s] === il.k ? 8 : 4) * (UL[s].bas || 1));
      else { a = il.gelir + 1 + (il.arazi === 'd' ? 0 : 1); kale = il.gelir >= 3 ? 1 : 0; }
      S.il.push({ s: s, a: a, h: a, kale: kale, b: '', u: 0 });
    });
    for (var i = 0; i < U; i++) {
      S.ul.push({ altin: i === S.oy ? 20 : 15, moral: AYAR.moral0, odak: -1, odakT: 0, bitenOdak: [], bon: {}, bk: BASKENT[i], ilkBk: BASKENT[i], olu: 0, tehdit: 0, red: -9 });
      S.sv.push([]); S.at.push([]); S.sk.push([]);
      for (var j = 0; j < U; j++) { S.sv[i].push(0); S.at[i].push(0); S.sk[i].push(0); }
    }
    yaz(S, '📜 ' + MEVSIM[0] + ', 1. yıl. Tarih yeniden yazılıyor!');
    return S;
  }

  function yaz(S, m, tur) { S.log.unshift({ t: S.tur, m: m, tur: tur || '' }); if (S.log.length > 40) S.log.length = 40; }
  function oz(S, u, k) { var a = UL[u] && UL[u].oz[k] || 0, b = S.ul[u] && S.ul[u].bon[k] || 0; return a + b; }
  function savasta(S, a, b) { if (a < 0 || b < 0) return a !== b; return !!S.sv[a][b]; }
  function iller(S, u) { var r = []; for (var k = 0; k < N; k++) if (S.il[k].s === u) r.push(k); return r; }
  function asker(S, u) { var t = 0; for (var k = 0; k < N; k++) if (S.il[k].s === u) t += S.il[k].a; return t; }
  function mevsim(S) { return S.tur % 4; }
  function moralF(S, u, sav) {
    if (u < 0) return 1;
    var m = S.ul[u].moral; if (sav && oz(S, u, 'bushido')) m = Math.max(m, 70);
    return 0.8 + 0.4 * m / 100;
  }

  // İkmal: başkentten kendi illeri (ve deniz yolları) üzerinden ulaşılan iller
  function ikmal(S, u) {
    var ok = {}, bk = S.ul[u].bk;
    if (bk < 0 || S.il[bk].s !== u) return ok;
    var q = [bk]; ok[bk] = 1;
    while (q.length) { var k = q.shift(); IL[k].komsu.forEach(function(j){ if (!ok[j] && S.il[j].s === u) { ok[j] = 1; q.push(j); } }); }
    return ok;
  }
  function ikmalVar(S, u, k, cache) { if (oz(S, u, 'yam')) return true; var c = cache || ikmal(S, u); return !!c[k]; }

  // ===== Savaş =====
  function kusatma(S, t) { // savunan il kendi illerinden tamamen kopuk mu?
    var s = S.il[t].s; if (s < 0) return false;
    for (var i = 0; i < IL[t].komsu.length; i++) if (S.il[IL[t].komsu[i]].s === s) return false;
    return iller(S, s).length > 1;
  }
  function destek(S, u, f, t) {
    var d = 0; IL[t].komsu.forEach(function(j){ if (j !== f && S.il[j].s === u && S.il[j].a > 0) d++; });
    return Math.min(3, d);
  }
  // Saldırı / savunma gücü ve açıklama satırları (ekranda gösterilir: neden-sonuç)
  function guc(S, f, t, n) {
    var u = S.il[f].s, d = S.il[t].s, il = IL[t], nd = [], A = n, D = S.il[t].a;
    var mA = moralF(S, u), mD = moralF(S, d, 1); A *= mA; D *= mD;
    if (mA > 1.05) nd.push(['Yüksek moral', 1]); else if (mA < 0.95) nd.push(['Düşük moral', -1]);
    var sal = 1 + oz(S, u, 'sal');
    if (oz(S, u, 'atli') && 'obc'.indexOf(il.arazi) >= 0) { sal += oz(S, u, 'atli'); nd.push(['Bozkır atlıları', 1]); }
    if (oz(S, u, 'buyuk') && n >= 10) { sal += oz(S, u, 'buyuk'); nd.push(['Büyük Ordu', 1]); }
    A *= sal;
    var ds = destek(S, u, f, t); if (ds) { A *= 1 + 0.1 * ds; nd.push(['Çok yönlü saldırı +' + ds * 10 + '%', 1]); }
    if (denizMi(f, t)) { if (oz(S, u, 'deniz')) { A *= 1 + oz(S, u, 'denizSal'); nd.push(['Deniz hâkimiyeti', 1]); } else { A *= 0.75; nd.push(['Denizden çıkarma -25%', -1]); } }
    if (mevsim(S) === 3 && !oz(S, u, 'kis')) {
      if (d >= 0 && oz(S, d, 'kis')) { A *= 0.7; nd.push(['General Kış -30%', -1]); } else { A *= 0.85; nd.push(['Kış seferi -15%', -1]); }
    }
    var ar = ARAZI[il.arazi].sav; D *= ar; if (ar > 1) nd.push([ARAZI[il.arazi].ad + ' savunması', -1]);
    if (S.il[t].kale) {
      var kg = oz(S, d, 'kaleGuc') ? 1.8 : 1.5; if (oz(S, u, 'kaleKir')) { kg = 1 + (kg - 1) / 2; nd.push(['Şahi topları kaleyi dövüyor', 1]); }
      D *= kg; nd.push(['Kale', -1]);
    }
    if (d >= 0) {
      D *= 1 + oz(S, d, 'sav');
      if (S.ul[d].bk === t) { D *= 1.25; nd.push(['Başkent savunması', -1]); }
      if (kusatma(S, t)) { D *= 0.8; nd.push(['Kuşatılmış, ikmalsiz', 1]); }
    }
    if (n > S.il[t].a * 1.8 && S.il[t].a > 0) nd.push(['Ezici sayı üstünlüğü', 1]);
    return { A: A, D: D, notlar: nd };
  }
  function sans(A, D) { // zar: her taraf ×0.85..1.15
    if (D <= 0) return 1; if (A <= 0) return 0;
    var w = 0, n = 0;
    for (var i = 0; i <= 12; i++) for (var j = 0; j <= 12; j++) { n++; if (A * (0.85 + 0.3 * i / 12) > D * (0.85 + 0.3 * j / 12)) w++; }
    return w / n;
  }
  function tahmin(S, f, t, n) {
    var g = guc(S, f, t, n), p = sans(g.A, g.D), kal = g.A > g.D ? Math.max(1, Math.round(n * Math.sqrt(1 - Math.pow(g.D / g.A, 2)))) : 0;
    return { p: p, kayip: g.A > g.D ? n - kal : n, notlar: g.notlar, A: g.A, D: g.D };
  }

  // Bir ilden diğerine n asker gönder (kendi iline = taşı, değilse = saldır)
  function hamle(S, f, t, n) {
    var F = S.il[f], T = S.il[t], u = F.s;
    n = Math.min(n | 0, F.h);
    if (n < 1 || IL[f].komsu.indexOf(t) < 0) return null;
    if (T.s === u) {
      F.a -= n; F.h -= n; T.a += n; if (oz(S, u, 'hizli') && !denizMi(f, t)) T.h += n;
      return { tur: 'tasi' };
    }
    if (T.s >= 0 && !savasta(S, u, T.s)) return null;
    var g = guc(S, f, t, n), sav = T.a, d = T.s, oy = S.oy;
    var A = g.A * (0.85 + 0.3 * rnd(S)), D = g.D * (0.85 + 0.3 * rnd(S));
    F.a -= n; F.h -= n;
    var R = { tur: 'savas', f: f, t: t, n: n, m: sav, u: u, d: d, notlar: g.notlar };
    if (sav === 0 || A > D) {
      var kal = sav === 0 ? n : Math.max(1, Math.round(n * Math.sqrt(1 - Math.pow(D / A, 2))));
      R.zafer = 1; R.kayip = n - kal; R.dkayip = sav;
      var kaleVardi = T.kale;
      ilDegis(S, t, u, kal);
      if (u >= 0) { S.ul[u].moral = Math.min(100, S.ul[u].moral + 2); }
      if (d >= 0) { S.ul[d].moral = Math.max(5, S.ul[d].moral - 2); S.sk[u][d] += 1.5; S.sk[d][u] -= 1.5; }
      if (u === oy) {
        S.ist.fetih++; if (sav > 0) S.ist.zafer++;
        if (sav > 0 && n < sav) { S.ist.deha++; R.deha = 1; }
        if (kaleVardi && sav > 0) S.ist.kaleKir++;
        if (mevsim(S) === 3 && d >= 0) S.ist.kisZafer++;
      }
      if (d === oy && sav > 0) S.ist.yenilgi++;
    } else {
      var kalD = Math.max(1, Math.round(sav * Math.sqrt(Math.max(0, 1 - Math.pow(A / D, 2)))));
      var geri = Math.floor(n * (oz(S, u, 'geri') || 0.2));
      R.zafer = 0; R.kayip = n - geri; R.dkayip = sav - kalD; R.geri = geri;
      T.a = kalD; T.h = Math.min(T.h, kalD); F.a += geri;
      if (u >= 0) S.ul[u].moral = Math.max(5, S.ul[u].moral - 3);
      if (d >= 0) { S.ul[d].moral = Math.min(100, S.ul[d].moral + 1); S.sk[d][u] += 0.5; S.sk[u][d] -= 0.5; }
      if (u === oy) S.ist.yenilgi++;
      if (d === oy) S.ist.zafer++;
    }
    if (u === oy) S.ist.kayip += R.kayip;
    if (d === oy) S.ist.kayip += R.dkayip;
    return R;
  }

  function ilDegis(S, t, u, a) {
    var T = S.il[t], eski = T.s;
    T.s = u; T.a = a; T.h = 0; T.u = u >= 0 ? (oz(S, u, 'entegre') ? 1 : AYAR.uyum) : 0;
    if (T.b === 'k') T.b = '';
    if (u >= 0) {
      var art = eski < 0 ? 1 : 2; if (eski >= 0 && S.ul[eski].bk === t) art = 4;
      if (oz(S, u, 'tehdit')) art = Math.ceil(art * oz(S, u, 'tehdit'));
      S.ul[u].tehdit += art;
    }
    if (eski >= 0 && S.ul[eski].bk === t) {
      S.ul[eski].moral = Math.max(5, S.ul[eski].moral - 25);
      if (u >= 0) { S.ul[u].moral = Math.min(100, S.ul[u].moral + 10); if (u === S.oy) S.ist.baskentAldi++; }
      var yeni = -1, iy = -1;
      iller(S, eski).forEach(function(k){ var v = IL[k].gelir * 10 + S.il[k].a; if (v > iy) { iy = v; yeni = k; } });
      S.ul[eski].bk = yeni;
      yaz(S, (eski === S.oy ? '💥 Başkentin ' : '🏛 ' + ulusAd(eski) + ' başkenti ') + IL[t].ad + ' düştü!' + (yeni >= 0 ? ' Yeni merkez: ' + IL[yeni].ad + '.' : ''), eski === S.oy ? 'kotu' : 'iyi');
    }
    if (eski >= 0 && !iller(S, eski).length) {
      S.ul[eski].olu = 1;
      for (var j = 0; j < U; j++) { S.sv[eski][j] = S.sv[j][eski] = 0; }
      yaz(S, '☠ ' + ulusAd(eski) + ' tarih sahnesinden çekildi.', eski === S.oy ? 'kotu' : '');
    }
  }
  // Türkçe ek: ek('Roma','e') = Roma'ya, ek('İsveç','den') = İsveç'ten, ek('Edo','i') = Edo'yu
  function ek(ad, tur) {
    var s = ad.toLocaleLowerCase('tr'), unl = 'aeıioöuüâîû', son = 'e';
    for (var i = s.length - 1; i >= 0; i--) if (unl.indexOf(s[i]) >= 0) { son = s[i]; break; }
    var kalin = 'aıouâû'.indexOf(son) >= 0, iki = kalin ? 'a' : 'e';
    var dort = 'aıâ'.indexOf(son) >= 0 ? 'ı' : 'eiî'.indexOf(son) >= 0 ? 'i' : 'ouû'.indexOf(son) >= 0 ? 'u' : 'ü';
    var sh = s[s.length - 1], unlu = unl.indexOf(sh) >= 0, sert = 'çfhkpsşt'.indexOf(sh) >= 0;
    if (tur === 'i') return ad + '\'' + (unlu ? 'y' : '') + dort;
    if (tur === 'e') return ad + '\'' + (unlu ? 'y' : '') + iki;
    if (tur === 'den') return ad + '\'' + (sert ? 't' : 'd') + iki + 'n';
    return ad + '\'' + (unlu ? 'n' : '') + dort + 'n';
  }
  function ulusAd(u) { var A = window.adcHK && window.adcHK.uluslar; return A && A[u] ? A[u].ad : UL[u].id; }

  // ===== Ekonomi =====
  function gelirHesap(S, u) {
    var c = ikmal(S, u), g = 0, ik = 0, bak = 0, kom = {};
    for (var k = 0; k < N; k++) {
      var I = S.il[k]; if (I.s !== u) continue;
      bak += I.a;
      IL[k].komsu.forEach(function(j){ var s = S.il[j].s; if (s >= 0 && s !== u) kom[s] = 1; });
      if (I.u > 0) continue;
      if (!ikmalVar(S, u, k, c)) { ik++; continue; }
      var v = IL[k].gelir * 2 + (I.b === 'p' ? 2 : 0) + (oz(S, u, 'kiyi') && IL[k].kiyi ? 1 : 0);
      g += v;
    }
    g = g * (1 + oz(S, u, 'bereket'));
    g += oz(S, u, 'altin') + oz(S, u, 'gelir');
    if (oz(S, u, 'ipek')) for (var s in kom) if (!S.sv[u][s]) g += 1;
    g *= u === S.oy ? ZORLUK[S.z].oyuncuGelir : ZORLUK[S.z].aiGelir;
    if ((S.ul[u].kitlik || 0) > S.tur) g *= 0.7;
    var bakim = Math.floor(bak / AYAR.bakim);
    return { brut: Math.round(g), bakim: bakim, net: Math.round(g) - bakim, ikmalsiz: ik };
  }
  function fiyat(S, u, ne) {
    if (ne === 'asker') return Math.max(1, AYAR.asker - oz(S, u, 'asker'));
    var f = AYAR[ne === 'kale' ? 'kale' : ne === 'pazar' ? 'pazar' : 'kisla'];
    if (ne === 'kale' && oz(S, u, 'kaleUcuz')) f = Math.ceil(f / 2);
    if (oz(S, u, 'usta')) f -= 3;
    return f;
  }
  function toplamaSiniri(S, k) {
    var I = S.il[k], u = I.s;
    return AYAR.toplama + (I.b === 'k' ? AYAR.kislaT : 0) + (S.ul[u].bk === k ? AYAR.baskentT : 0) + oz(S, u, 'toplama');
  }
  function topla(S, k, n) {
    var I = S.il[k], u = I.s; if (u < 0) return 0;
    if (I.u > 0 || !ikmalVar(S, u, k)) return 0;
    var f = fiyat(S, u, 'asker'), kalan = toplamaSiniri(S, k) - (I.t || 0);
    n = Math.min(n, kalan, Math.floor(S.ul[u].altin / f));
    if (n < 1) return 0;
    S.ul[u].altin -= n * f; I.a += n; I.t = (I.t || 0) + n;
    return n;
  }
  function insa(S, k, ne) {
    var I = S.il[k], u = I.s; if (u < 0 || I.u > 0) return false;
    if (ne === 'kale' ? I.kale : I.b) return false;
    var f = fiyat(S, u, ne); if (S.ul[u].altin < f) return false;
    S.ul[u].altin -= f;
    if (ne === 'kale') I.kale = 1; else I.b = ne === 'pazar' ? 'p' : 'k';
    return true;
  }
  function odakSec(S, u, i) {
    var L = S.ul[u]; if (L.odak >= 0 || L.bitenOdak.indexOf(i) >= 0 || !ODAK[i]) return false;
    L.odak = i; L.odakT = ODAK[i].gun; return true;
  }
  function odakBitir(S, u) {
    var L = S.ul[u], i = L.odak, e = ODAK[i].e || OZEL_ODAK[UL[u].id] || {};
    L.bitenOdak.push(i); L.odak = -1;
    for (var k in e) {
      if (k === 'moral') L.moral = Math.min(100, L.moral + e[k]);
      else if (k === 'tehditSifir') L.tehdit = 0;
      else L.bon[k] = (L.bon[k] || 0) + e[k];
    }
    if (u === S.oy) yaz(S, '🎯 Ulusal odak tamamlandı: ' + odakAd(u, i), 'iyi');
  }
  function odakAd(u, i) { if (i < 5) return ODAK[i].ad; var A = window.adcHK && window.adcHK.uluslar; return A && A[u] ? A[u].odak : 'Ulusal Odak'; }

  // ===== Diplomasi =====
  function savasIlan(S, a, b, sessiz) {
    if (a === b || a < 0 || b < 0 || S.ul[a].olu || S.ul[b].olu || S.sv[a][b] || S.at[a][b] > S.tur) return false;
    S.sv[a][b] = S.sv[b][a] = 1; S.sk[a][b] = S.sk[b][a] = 0;
    S.ul[a].tehdit += oz(S, a, 'tehdit') ? 1 : 2;
    if (!sessiz) yaz(S, '⚔ ' + ulusAd(a) + ', ' + ek(ulusAd(b), 'e') + ' savaş açtı!', b === S.oy ? 'kotu' : a === S.oy ? '' : 'dunya');
    if (a === S.oy || b === S.oy) { S.svBas = S.svBas || {}; S.svBas[a === S.oy ? b : a] = S.tur; var c = 0; for (var j = 0; j < U; j++) if (S.sv[S.oy][j]) c++; S.ist.cephe = Math.max(S.ist.cephe, c); }
    return true;
  }
  function baris(S, a, b) {
    S.sv[a][b] = S.sv[b][a] = 0; S.at[a][b] = S.at[b][a] = S.tur + AYAR.ateskes;
  }
  // Oyuncunun barış teklifi: savaş skoru iyiyse ya da karşı taraf çok zayıfsa kabul
  function barisTeklif(S, b) {
    var a = S.oy; if (!S.sv[a][b]) return { ok: false, neden: 'Zaten savaşta değilsiniz.' };
    if (S.ul[a].red === S.tur) return { ok: false, neden: 'Bu tur zaten bir elçi gönderdin.' };
    S.ul[a].red = S.tur;
    var sk = S.sk[a][b], esik = oz(S, a, 'diplo') ? 0.5 : 2;
    var zayif = asker(S, b) < asker(S, a) * 0.5;
    var kayip = S.sk[b][a] > 3 && asker(S, b) > asker(S, a) * 0.8, sure = S.tur - (S.svBas && S.svBas[b] || 0);
    if (!kayip && (sk >= esik || zayif || (sk > -2 && sure >= 4 && rnd(S) < 0.6))) {
      baris(S, a, b);
      var kazandi = sk >= 3;
      if (kazandi) { S.ist.baris++; S.ul[a].moral = Math.min(100, S.ul[a].moral + 10); }
      yaz(S, '🕊 ' + ulusAd(b) + ' barışı kabul etti.' + (kazandi ? ' Savaşı kazandın! Moral +10.' : '') + ' ' + AYAR.ateskes + ' tur ateşkes.', 'iyi');
      return { ok: true, kazandi: kazandi };
    }
    return { ok: false, neden: kayip ? ulusAd(b) + ' kazandığını düşünüyor, barışa yanaşmıyor.' : ulusAd(b) + ' henüz pes etmedi. Birkaç zafer daha kazan ya da ordusunu eritmeye devam et.' };
  }

  // ===== Yapay zekâ =====
  function tehditAltinda(S, k) {
    var u = S.il[k].s, t = 0;
    IL[k].komsu.forEach(function(j){ var s = S.il[j].s; if (s >= 0 && s !== u && savasta(S, u, s)) t = Math.max(t, S.il[j].a); });
    return t;
  }
  function sinirMi(S, k) {
    var u = S.il[k].s; for (var i = 0; i < IL[k].komsu.length; i++) if (S.il[IL[k].komsu[i]].s !== u) return true; return false;
  }
  function deger(S, u, t) {
    var T = S.il[t], v = IL[t].gelir * 2 + (T.kale ? 1 : 0);
    if (T.s >= 0 && S.ul[T.s].bk === t) v += 8;
    if (T.s >= 0 && T.s === S.oy && S.z === 2) v += 2;
    if (T.s < 0) v += 1;
    return v;
  }
  function komsuUlus(S, a, b) {
    for (var k = 0; k < N; k++) if (S.il[k].s === a) for (var i = 0; i < IL[k].komsu.length; i++) if (S.il[IL[k].komsu[i]].s === b) return true;
    return false;
  }
  function sinirGucu(S, a, b) { // a'nın b'ye komşu illerindeki askerleri
    var t = 0; for (var k = 0; k < N; k++) if (S.il[k].s === a) for (var i = 0; i < IL[k].komsu.length; i++) if (S.il[IL[k].komsu[i]].s === b) { t += S.il[k].a; break; }
    return t;
  }
  function mesafeSinir(S, u) {
    var d = {}, q = [];
    for (var k = 0; k < N; k++) if (S.il[k].s === u) { if (sinirMi(S, k)) { d[k] = 0; q.push(k); } else d[k] = 99; }
    while (q.length) { var k2 = q.shift(); IL[k2].komsu.forEach(function(j){ if (S.il[j].s === u && d[j] > d[k2] + 1) { d[j] = d[k2] + 1; q.push(j); } }); }
    return d;
  }

  // ===== Tur sonu =====
  function turBitir(S) {
    if (S.bitti) return;
    var sira = []; for (var u = 0; u < U; u++) if (u !== S.oy && !S.ul[u].olu) sira.push(u);
    for (var i = sira.length - 1; i > 0; i--) { var j = Math.floor(rnd(S) * (i + 1)), x = sira[i]; sira[i] = sira[j]; sira[j] = x; }
    sira.forEach(function(u){ window.adcHKMotor.aiTur(S, u); });
    var ozet = { gelir: 0, eriyen: 0 };
    for (u = 0; u < U; u++) {
      var L = S.ul[u]; if (L.olu) continue;
      var G = gelirHesap(S, u);
      L.altin += G.net;
      if (u === S.oy) ozet.gelir = G.net;
      if (L.altin < 0) { // iflas: askerler dağılır, moral düşer
        L.moral = Math.max(5, L.moral - 8);
        iller(S, u).forEach(function(k){ var x = Math.ceil(S.il[k].a * 0.1); S.il[k].a -= x; if (u === S.oy) { S.ist.kayip += x; ozet.eriyen += x; } });
        if (u === S.oy) yaz(S, '💸 Hazine boş! Maaş alamayan askerler dağılıyor, moral düşüyor.', 'kotu');
        L.altin = 0;
      }
      // ikmalsiz iller erir (Moğol hariç)
      if (!oz(S, u, 'yam')) {
        var c = ikmal(S, u);
        iller(S, u).forEach(function(k){ if (!c[k] && S.il[k].a > 0) { var x = Math.ceil(S.il[k].a * 0.1); S.il[k].a -= x; if (u === S.oy) { S.ist.kayip += x; ozet.eriyen += x; } } });
      }
      // Rus kışı: kışın Rusya'ya komşu düşman illerindeki ordular erir
      if (mevsim(S) === 3 && oz(S, u, 'kis')) {
        iller(S, u).forEach(function(k){ IL[k].komsu.forEach(function(j){ var s = S.il[j].s; if (s >= 0 && s !== u && S.sv[u][s] && S.il[j].a > 2 && !oz(S, s, 'kis')) { var x = Math.ceil(S.il[j].a * 0.08); S.il[j].a -= x; if (s === S.oy) { S.ist.kayip += x; ozet.eriyen += x; } } }); });
      }
      if (L.odak >= 0 && --L.odakT <= 0) odakBitir(S, u);
      L.tehdit = Math.max(0, L.tehdit - (oz(S, u, 'diplo') ? 2 : 1));
      if (L.moral < 60) L.moral++; else if (L.moral > 75) L.moral--;
      L.maxAltin = Math.max(L.maxAltin || 0, L.altin);
    }
    S.ist.maxAltin = Math.max(S.ist.maxAltin, S.ul[S.oy].altin);
    for (var k = 0; k < N; k++) { var I = S.il[k]; if (I.u > 0) I.u--; I.t = 0; }
    // koalisyon
    var P = S.ul[S.oy];
    if (P.tehdit >= AYAR.tehditSinir) {
      var kat = [];
      for (u = 0; u < U; u++) if (u !== S.oy && !S.ul[u].olu && !S.sv[u][S.oy] && S.at[u][S.oy] <= S.tur && komsuUlus(S, u, S.oy)) { savasIlan(S, u, S.oy, 1); kat.push(ulusAd(u)); }
      if (kat.length) yaz(S, '⚠ KOALİSYON! Fetihlerinden korkan ' + kat.join(', ') + ' sana karşı birleşti!', 'kotu');
      P.tehdit = 8;
    } else if (P.tehdit >= AYAR.tehditSinir - 4 && !S.uyari) { S.uyari = 1; yaz(S, '👀 Komşular fetihlerinden tedirgin. Tehdit ' + AYAR.tehditSinir + '\'e ulaşırsa sana karşı birleşecekler.', 'kotu'); }
    if (P.tehdit < 6) S.uyari = 0;
    // yapay zekâlar arası barış + yenilen yapay zekâ oyuncuya barış teklif eder
    for (u = 0; u < U; u++) for (var j2 = u + 1; j2 < U; j2++) {
      if (!S.sv[u][j2] || u === S.oy || j2 === S.oy) continue;
      if (rnd(S) < 0.07) { baris(S, u, j2); }
    }
    for (u = 0; u < U; u++) if (u !== S.oy && S.sv[u][S.oy] && !S.teklif && S.sk[u][S.oy] <= -3.5 && rnd(S) < 0.35) S.teklif = u;
    S.tur++;
    if (P.olu || !iller(S, S.oy).length) { S.bitti = 'yenildi'; return ozet; }
    if (iller(S, S.oy).length >= Math.ceil(N * AYAR.kralOran)) { S.bitti = 'kral'; return ozet; }
    if (S.tur >= AYAR.tur) { S.bitti = 'sure'; return ozet; }
    if (S.tur >= 2 && rnd(S) < 0.3) S.olay = window.adcHKMotor.olaySec(S);
    for (k = 0; k < N; k++) S.il[k].h = S.il[k].a;
    yaz(S, '⏳ ' + MEVSIM[mevsim(S)] + ', ' + (Math.floor(S.tur / 4) + 1) + '. yıl · gelir ' + (ozet.gelir >= 0 ? '+' : '') + ozet.gelir + ' altın' + (ozet.eriyen ? ' · ' + ozet.eriyen + ' asker eridi' : ''));
    return ozet;
  }
  function teklifCevap(S, kabul) {
    var u = S.teklif; S.teklif = null; if (u === null || u === undefined) return;
    if (kabul) { var kaz = S.sk[S.oy][u] >= 3; baris(S, S.oy, u); if (kaz) { S.ist.baris++; S.ul[S.oy].moral = Math.min(100, S.ul[S.oy].moral + 10); } yaz(S, '🕊 ' + ulusAd(u) + ' ile barış imzalandı.' + (kaz ? ' Savaşı kazandın!' : ''), 'iyi'); }
    else yaz(S, '⚔ ' + ek(ulusAd(u), 'in') + ' barış teklifini reddettin. Savaş sürüyor.');
  }

  // ===== Puan (sunucu da aynısını hesaplar) =====
  function sonuc(S) {
    var p = S.oy, il = iller(S, p).length, bk = 0;
    for (var u = 0; u < U; u++) if (u !== p && S.il[BASKENT[u]].s === p) bk++;
    var r = { il: il, baskent: bk, zafer: S.ist.zafer, deha: S.ist.deha, kayip: S.ist.kayip, baris: S.ist.baris,
      altin: Math.max(0, Math.floor(S.ul[p].altin)), kendi: S.il[BASKENT[p]].s === p ? 1 : 0, kral: S.bitti === 'kral' ? 1 : 0,
      tur: S.tur, hedef: S.hedef >= 0 && S.il[S.hedef].s === p ? 1 : 0, zorluk: S.z, ulus: p,
      kaleKir: S.ist.kaleKir, kisZafer: S.ist.kisZafer, cephe: S.ist.cephe, maxAltin: S.ist.maxAltin, yenilgi: S.ist.yenilgi };
    r.dokum = puanDokum(r); r.puan = r.dokum.toplam;
    return r;
  }
  function puanDokum(r) {
    var d = [
      ['Toprak: ' + r.il + ' il × 10', r.il * 10],
      ['Ele geçen başkentler: ' + r.baskent + ' × 40', r.baskent * 40],
      ['Kazanılan muharebeler: ' + Math.min(r.zafer, 150) + ' × 2', Math.min(r.zafer, 150) * 2],
      ['Dâhiyane zaferler (az askerle): ' + Math.min(r.deha, 60) + ' × 8', Math.min(r.deha, 60) * 8],
      ['Kazanılan barışlar: ' + r.baris + ' × 20', Math.min(r.baris, 9) * 20],
      ['Hazine: ' + Math.min(r.altin, 300) + ' altın ÷ 10', Math.floor(Math.min(r.altin, 300) / 10)],
      ['Kayıplar: ' + r.kayip + ' asker ÷ 4', -Math.floor(r.kayip / 4)]
    ];
    if (!r.kendi) d.push(['Kendi başkentini kaybettin', -100]);
    if (r.kral) d.push(['TARİHİN KRALI! Kalan ' + (30 - r.tur) + ' tur × 15', (30 - r.tur) * 15 + 100]);
    if (r.hedef) d.push(['Günün hedefi tamam', 100]);
    var ham = 0; d.forEach(function(x){ ham += x[1]; });
    ham = Math.max(0, ham);
    var c = ZORLUK[r.zorluk].carpan, top = Math.round(ham * c);
    return { satir: d, ham: ham, carpan: c, toplam: top };
  }

  window.adcHKMotor = {
    W: W, H: HH, N: N, U: U, HUCRE: HUCRE, IL: IL, UL: UL, BASKENT: BASKENT, AYAR: AYAR, MEVSIM: MEVSIM, ARAZI: ARAZI, ZORLUK: ZORLUK, ODAK: ODAK,
    yeniOyun: yeniOyun, hamle: hamle, tahmin: tahmin, topla: topla, insa: insa, fiyat: fiyat, toplamaSiniri: toplamaSiniri, odakSec: odakSec, odakAd: odakAd,
    savasIlan: savasIlan, barisTeklif: barisTeklif, teklifCevap: teklifCevap, turBitir: turBitir, sonuc: sonuc, puanDokum: puanDokum,
    gelirHesap: gelirHesap, ikmal: ikmal, iller: iller, asker: asker, savasta: savasta, komsuUlus: komsuUlus, 
    mevsim: mevsim, ek: ek, denizMi: denizMi, kusatma: kusatma, tehditAltinda: tehditAltinda, yaz: yaz, tohum: tohum,
    _: { oz: oz, rnd: rnd, ulusAd: ulusAd, deger: deger, sinirMi: sinirMi, mesafeSinir: mesafeSinir, sinirGucu: sinirGucu }
  };
})();
