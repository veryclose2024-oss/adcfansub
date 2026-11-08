<?php

/**
 * PixPulse - müzik (yeni 2026-11-08)
 * Şarkıları tarayıcıda besteler (tohumdan, herkese aynı, telif yok), nota haritasını müzikten çıkarır,
 * sesi takılmasın diye önceden arabelleğe çizer, dokunuşu çalan sesin zamanına çevirir, 3x5 piksel yazı.
 * "PixPulse" çekirdek snippet'i açık olmalı. Sıra: müzik (20) → oyun (21) → ekran (22).
 */
add_action( 'wp_footer', function() {
    if ( ! function_exists( 'adc_pp_ayar' ) || ! function_exists( 'bp_is_activity_directory' ) || ! bp_is_activity_directory() ) { return; }
    ?>
<script>
(function(){
  // PixPulse: şarkı besteleyici (tohumdan, her seferinde aynı), nota haritası, ses üretimi, ses saati, piksel yazı
  var P = window.adcPP = window.adcPP || {};
  function rng(s){ return function(){ s = s + 0x6D2B79F5 | 0; var t = Math.imul(s ^ s >>> 15, 1 | s); t = t + Math.imul(t ^ t >>> 7, 61 | t) ^ t; return ((t ^ t >>> 14) >>> 0) / 4294967296; }; }
  var OLC = {maj: [0, 2, 4, 5, 7, 9, 11], min: [0, 2, 3, 5, 7, 8, 10]};
  P.rng = rng;

  // ===== Beste: her ölçü 16 adım (16'lık nota). Bölümler: i giriş (kalp atışı), A, B nakarat, o çıkış =====
  P.bestele = function(S){
    if (S._m) return S._m;
    var olc = OLC[S.mod], adim = 60 / S.bpm / 4, en = S.enerji;
    function ton(d, okt){ var o = Math.floor(d / 7); return S.kok + olc[((d % 7) + 7) % 7] + 12 * (o + (okt || 0)); }
    var yapi = [];
    var kez = {};
    S.bolum.forEach(function(b){ var n = (b === 'i' || b === 'o') ? 2 : 8, k = kez[b] = (kez[b] == null ? 0 : kez[b] + 1); for (var i = 0; i < n; i++) yapi.push({b: b, i: i, k: k}); });
    // Ritim hücreleri (bir vuruş = 4 adım): düz, ikili, noktalı, dörtlü, senkop, es... [adımlar, en az yoğunluk]
    var HUCRE = [[[0], 0], [[0, 2], 0], [[], 0], [[0], 0], [[0, 3], 1], [[0, 2, 3], 1], [[0, 1, 2], 1], [[2], 1], [[1, 3], 2], [[0, 1, 2, 3], 3], [[0, 1, 3], 2], [[3], 2]];
    function ritim(r, yog){
      var on = [];
      for (var v = 0; v < 8; v++) {
        var c;
        if (v === 0 || v === 4) c = [[0], [0, 2], [0, 3], [0, 2, 3]][Math.floor(r() * Math.min(4, 1 + yog))];
        else { var hv = HUCRE.filter(function(h){ return h[1] <= yog; }); c = hv[Math.floor(r() * hv.length)][0]; }
        if (v === 7 && r() < 0.5) c = [0];   // cümle sonunda nefes
        c.forEach(function(x){ on.push(v * 4 + x); });
      }
      return on;
    }
    function ezgi(r, on){
      var d = 2 + Math.floor(r() * 3), m = [], ad = [-2, -1, -1, 0, 1, 1, 2, 3, -3, 1, -1, 2];
      on.forEach(function(s){ d += ad[Math.floor(r() * ad.length)]; if (d < 0) d = 1 + Math.floor(r() * 2); if (d > 9) d = 7; m.push({s: s, d: d}); });
      return m;
    }
    // Her bölüm tekrarında 4 ikişer ölçülük cümle: 1. motif, aynı ritim yeni ezgi, yeni motif, kapanış. İkinci kez gelen bölüm daha yoğun.
    var cumle = {};
    function cumleler(b, k){
      var ad = b + k; if (cumle[ad]) return cumle[ad];
      var yog = Math.min(3, en + (b === 'B' ? 1 : 0) + k), t = S.tohum * 7 + (b === 'B' ? 50 : 10) + k * 100;
      var r1 = ritim(rng(t + 1), yog), r3 = ritim(rng(t + 3), yog), r4 = ritim(rng(t + 4), Math.max(0, yog - 1));
      return (cumle[ad] = [ezgi(rng(t + 5), r1), ezgi(rng(t + 6), r1), ezgi(rng(t + 7), r3), ezgi(rng(t + 8), r4)]);
    }
    var M = {lead: [], bass: [], arp: [], kick: [], snare: [], hat: [], adim: adim, olcu: yapi.length, yapi: yapi};
    function akor(o){ var c = S.prog[o % S.prog.length]; return [c, c + 2, c + 4]; }
    function yakin(d, ak){ var en_ = d, f = 99; ak.forEach(function(a){ for (var k = -1; k <= 1; k++) { var x = a + 7 * (Math.floor(d / 7) + k); if (Math.abs(x - d) < f) { f = Math.abs(x - d); en_ = x; } } }); return en_; }
    yapi.forEach(function(y, o){
      var t0 = o * 16, ak = akor(o), b = y.b;
      // davul
      if (b === 'i' || (b === 'o' && y.i === 0)) { [0, 3, 8, 11].forEach(function(s, k){ M.kick.push({s: t0 + s, g: k % 2 ? 0.55 : 0.9}); }); }
      else if (b === 'o') { M.kick.push({s: t0, g: 0.9}); M.snare.push({s: t0, g: 0.5}); }
      else {
        var kk = b === 'B' ? [0, 6, 8].concat(en >= 2 ? [14] : []) : [0, 8].concat(en >= 1 ? [10] : []);
        kk.forEach(function(s){ M.kick.push({s: t0 + s, g: 0.85}); });
        [4, 12].forEach(function(s){ M.snare.push({s: t0 + s, g: 0.5}); });
        if (b === 'B' && y.i === 7) [13, 14, 15].forEach(function(s){ M.snare.push({s: t0 + s, g: 0.3}); });
        for (var h = 0; h < 16; h += (b === 'B' && en >= 1 ? 1 : 2)) M.hat.push({s: t0 + h, g: h % 4 === 0 ? 0.14 : h % 2 ? 0.05 : 0.09});
      }
      // bas
      if (b === 'A' || b === 'B') {
        var bp = en === 0 ? [0, 8] : en === 1 ? [0, 6, 8, 14] : [0, 2, 4, 6, 8, 10, 12, 14];
        bp.forEach(function(s, k){ M.bass.push({s: t0 + s, d: en === 0 ? 7 : en === 1 ? (k % 2 ? 2 : 5) : 2, n: ton(ak[0], -2) + (en === 2 && k % 2 ? 12 : 0)}); });
      } else if (b === 'o' && y.i === 1) M.bass.push({s: t0, d: 14, n: ton(S.prog[0], -2)});
      // arpej (nakaratta)
      if (b === 'B') for (var a = 0; a < 16; a += 2) M.arp.push({s: t0 + a, d: 2, n: ton(ak[(a / 2) % 3], 1)});
      // melodi: iki ölçülük motif, akora oturtulur
      if (b === 'A' || b === 'B') {
        var mot = cumleler(b, y.k)[Math.floor(y.i / 2)], yari = (y.i % 2) * 16;
        mot.forEach(function(n, k){
          if (n.s < yari || n.s >= yari + 16) return;
          var son = k + 1 < mot.length ? mot[k + 1].s : 32, d = n.d + (b === 'B' ? 2 : 0);
          if (n.s % 8 === 0) d = yakin(d, ak);
          M.lead.push({s: t0 + n.s - yari, d: Math.min(8, Math.min(son, yari + 16) - n.s), n: ton(d, 0), dd: d});
        });
      } else if (b === 'o' && y.i === 1) M.lead.push({s: t0, d: 12, n: ton(S.prog[0] + 7, 0), dd: S.prog[0] + 7});
    });
    M.sure = (yapi.length * 16 + 4) * adim + 1.2;
    S._m = M;
    return M;
  };

  // ===== Nota haritası: müzikten çıkar (melodi yükselince sağa, inince sola) =====
  P.nota = function(S, zor){
    var M = P.bestele(S), adim = M.adim, aday = [], liste = [], hizli = S.bpm >= 140;
    var seri = {kolay: 4, normal: 2, zor: hizli ? 2 : 1}[zor], izgara = {kolay: 2, normal: hizli ? 2 : 1, zor: 1}[zor];
    var onceD = null, onceL = 1, sonS = -99, tutSon = {}, sonL = -1, tekrar = 0, pencere = [];
    // Her 2 ölçülük cümlede melodinin en alçak / en yüksek notası bulunur: alçak nota solda (pembe), yüksek sağda (mavi)
    var ar = {};
    M.lead.forEach(function(n){ var c = Math.floor(n.s / 32), x = ar[c] || (ar[c] = [99, -99]); x[0] = Math.min(x[0], n.dd); x[1] = Math.max(x[1], n.dd); });
    M.kick.forEach(function(k){
      var y = M.yapi[Math.floor(k.s / 16)].b;
      if ((y === 'i' || y === 'o') && k.g > 0.6 && (k.s % 16 === 0 || zor !== 'kolay')) aday.push({s: k.s, l: (k.s / 8) % 2 ? 2 : 1, uz: 0});
    });
    M.lead.forEach(function(n){
      var y = M.yapi[Math.floor(n.s / 16)].b;
      if (y === 'o' || n.s % izgara || n.s - sonS < seri) return;
      var x = ar[Math.floor(n.s / 32)], l = x[1] > x[0] ? Math.round((n.dd - x[0]) / (x[1] - x[0]) * 3) : onceL;
      if (onceD != null) {   // melodinin yönüne uy: aynı nota aynı renk, çıkınca sağa, inince sola
        if (n.dd === onceD) l = onceL;
        else if (n.dd > onceD && l <= onceL) l = Math.min(3, onceL + 1);
        else if (n.dd < onceD && l >= onceL) l = Math.max(0, onceL - 1);
      }
      onceD = n.dd; onceL = l; sonS = n.s;
      aday.push({s: n.s, l: l, uz: n.d >= (zor === 'kolay' ? 8 : 6) ? n.d - 2 : 0});
      if (zor === 'zor' && y === 'B' && n.s % 16 === 0) aday.push({s: n.s, l: -1, uz: 0, cift: 1});   // nakarat başında çift nota
    });
    if (zor === 'zor') M.snare.forEach(function(sn){
      if (M.yapi[Math.floor(sn.s / 16)].b !== 'A' || sn.g < 0.4) return;
      for (var i = 0; i < aday.length; i++) if (Math.abs(aday[i].s - sn.s) < 1.5) return;
      aday.push({s: sn.s, l: sn.s % 32 === 4 ? 0 : 3, uz: 0});
    });
    if (zor === 'zor') M.kick.forEach(function(k, i){   // Zor: melodi boşluklarında bas davul da nota olur
      var y = M.yapi[Math.floor(k.s / 16)].b;
      if (y !== 'A' && y !== 'B') return;
      for (var j = 0; j < aday.length; j++) if (Math.abs(aday[j].s - k.s) < 1.5) return;
      aday.push({s: k.s, l: i % 2 ? 0 : 3, uz: 0});
    });
    if (zor === 'zor') M.lead.forEach(function(n){   // Zor: A bölümünde her ölçü başında da çift nota
      if (M.yapi[Math.floor(n.s / 16)].b === 'A' && n.s % 32 === 0) aday.push({s: n.s, l: -1, uz: 0, cift: 1});
    });
    aday.sort(function(a, b){ return a.s - b.s || (a.cift ? 1 : 0) - (b.cift ? 1 : 0); });
    aday.forEach(function(a){
      var l = a.l < 0 ? 3 - Math.max(0, sonL) : a.l;
      if (!a.cift) {
        // aynı renk en fazla 3 kez üst üste; son 8 notada bir renk 4'ü geçmesin (en az kullanılan komşuya kayar)
        if (l === sonL) { tekrar++; if (tekrar >= 3) { l = l <= 1 ? l + 1 : l - 1; tekrar = 0; } } else tekrar = 0;
        var say = [0, 0, 0, 0]; pencere.forEach(function(x){ say[x]++; });
        if (say[l] >= 4) {
          var en = -1;
          [l - 1, l + 1, l - 2, l + 2, l - 3, l + 3].forEach(function(x){ if (x >= 0 && x <= 3 && x !== sonL && (en < 0 || say[x] < say[en])) en = x; });
          if (en >= 0) l = en;
        }
      }
      for (var k = 0; k < 4 && tutSon[l] != null && tutSon[l] >= a.s; k++) l = (l + 1) % 4;
      if (tutSon[l] != null && tutSon[l] >= a.s) return;
      liste.push({t: a.s * adim, l: l, u: a.uz ? (a.s + a.uz) * adim : 0});
      tutSon[l] = a.s + a.uz;
      if (!a.cift) { sonL = l; pencere.push(l); if (pencere.length > 8) pencere.shift(); }
    });
    liste.sort(function(a, b){ return a.t - b.t || a.l - b.l; });
    return liste;
  };

  // ===== Ses: şarkı tek seferde, elle (örnek örnek) arabelleğe çizilir: hızlı ve takılmasız =====
  var onbellek = {};
  P.SES_BAS = 0.15;
  P.sesHazirla = function(S, cb){
    if (onbellek[S.id]) return cb(onbellek[S.id]);
    var ctx = P.ctx; if (!ctx) return cb(null);
    setTimeout(function(){
      var M = P.bestele(S), sr = 22050, a = M.adim, L = Math.ceil(M.sure * sr), B = P.SES_BAS;
      var out = new Float32Array(L), lead = new Float32Array(L), r = rng(S.tohum * 31 + 5);
      function hz(n){ return 440 * Math.pow(2, (n - 69) / 12); }
      function ton(hedef, tip, n, t, d, g, son){
        var i0 = Math.floor(t * sr), n_ = Math.floor(d * sr), f = hz(n) / sr, ph = 0, at = 0.006 * sr, bt = 0.02 * sr;
        for (var i = 0; i < n_ && i0 + i < L; i++) {
          var e = i < at ? i / at : 1 - (1 - (son || 0.6)) * (i / n_);
          if (i > n_ - bt) e *= (n_ - i) / bt;
          ph += f; if (ph >= 1) ph -= 1;
          var v = tip === 't' ? 4 * Math.abs(ph - 0.5) - 1 : ph < (tip === 'p' ? 0.25 : 0.5) ? 1 : -1;
          hedef[i0 + i] += v * e * g;
        }
      }
      function gur(t, d, g, yuksek){
        var i0 = Math.floor(t * sr), n_ = Math.floor(d * sr), onc = 0, lp = 0, k = Math.log(1000) / n_;
        for (var i = 0; i < n_ && i0 + i < L; i++) {
          var x = r() * 2 - 1, v;
          if (yuksek) { v = x - onc; onc = x; } else { lp += (x - lp) * 0.35; v = lp * 1.6; }
          out[i0 + i] += v * g * Math.exp(-k * i);
        }
      }
      M.kick.forEach(function(k){
        var i0 = Math.floor((B + k.s * a) * sr), n_ = Math.floor(0.3 * sr), ph = 0;
        for (var i = 0; i < n_ && i0 + i < L; i++) {
          var tt = i / sr, f = tt < 0.13 ? 150 * Math.pow(42 / 150, tt / 0.13) : 42;
          ph += f / sr; out[i0 + i] += Math.sin(ph * 6.2832) * k.g * Math.exp(-tt * 16) * 1.1;
        }
      });
      M.snare.forEach(function(s){ var t = B + s.s * a; gur(t, 0.16, s.g * 0.8, false); ton(out, 't', 55, t, 0.06, s.g * 0.5, 0.2); });
      M.hat.forEach(function(h){ gur(B + h.s * a, 0.045, h.g * 1.2, true); });
      M.bass.forEach(function(b){ ton(out, 't', b.n, B + b.s * a, b.d * a * 0.95, 0.42, 0.8); });
      M.arp.forEach(function(q){ ton(out, 's', q.n, B + q.s * a, q.d * a * 0.8, 0.04, 0.3); });
      M.lead.forEach(function(l){ ton(lead, 'p', l.n, B + l.s * a, l.d * a * 0.92, 0.16, 0.55); });
      var dl = Math.round(a * 3 * sr);
      for (var i = 0; i < L; i++) {
        if (i >= dl) lead[i] += lead[i - dl] * 0.25;
        var x = (out[i] + lead[i]) * 0.55;
        out[i] = x > 1 ? 1 : x < -1 ? -1 : x * (1.5 - 0.5 * x * x);
      }
      var buf;
      try { buf = ctx.createBuffer(1, L, sr); buf.getChannelData(0).set(out); } catch (e) { return cb(null); }
      onbellek[S.id] = buf; cb(buf);
    }, 30);
  };

  // ===== Ses saati: dokunuş anını çalan sesin zamanına çevirir (cihaz gecikmesi dahil) =====
  P.ctx = null;
  P.ac = function(){
    if (!P.ctx) { var AC = window.AudioContext || window.webkitAudioContext; if (!AC) return null; P.ctx = new AC({latencyHint: 'interactive'}); }
    if (P.ctx.state !== 'running') P.ctx.resume();
    return P.ctx;
  };
  P.sesZamani = function(perf){
    var c = P.ctx, kaba = c.currentTime - (c.outputLatency || c.baseLatency || 0) + (perf - performance.now()) / 1000;
    if (c.getOutputTimestamp && c.state === 'running') {
      var z = c.getOutputTimestamp();
      if (z && z.contextTime > 0 && z.performanceTime > 0) {
        var t = z.contextTime + (perf - z.performanceTime) / 1000;
        if (Math.abs(t - kaba) < 0.25) return t;
      }
    }
    return kaba;
  };
  P.tik = function(t, f, g){ var c = P.ctx, x = c.createOscillator(), e = c.createGain(); x.type = 'square'; x.frequency.value = f || 1200; e.gain.setValueAtTime(g || 0.18, t); e.gain.exponentialRampToValueAtTime(0.001, t + 0.05); x.connect(e); e.connect(c.destination); x.start(t); x.stop(t + 0.06); };

  // ===== Puan (sunucu da aynısını hesaplar) =====
  P.puan = function(s, n, carpan){
    if (!n) return 0;
    var isabet = (s.p + s.g * 0.7 + s.o * 0.35) / n;
    return Math.round((isabet * 90000 + (s.k / n) * 10000) * carpan);
  };
  P.isabet = function(s, n){ return n ? (s.p + s.g * 0.7 + s.o * 0.35) / n : 0; };
  P.harf = function(s, n){ var i = P.isabet(s, n); return s.p === n ? 'S+' : i >= 0.95 ? 'S' : i >= 0.9 ? 'A' : i >= 0.8 ? 'B' : i >= 0.7 ? 'C' : 'D'; };

  // ===== 3x5 piksel yazı =====
  var F = {}, FK = 'A75755B65656C74447D65556E74647F74644G74557H55755I72227J11157K55655L44447M57555N75555O75557P75744R75655S74717T72222U55557V55552Y55222Z71247+02720-00700.00002!22202%51245:02020x05250/11244?71302';
  for (var q = 0; q < FK.length; q += 6) F[FK.charAt(q)] = FK.substr(q + 1, 5);
  '0123456789'.split('').forEach(function(d, i){ F[d] = ['75557', '26227', '71747', '71317', '55711', '74717', '74757', '71222', '75757', '75717'][i]; });
  P.yaz = function(c, t, x, y, b, renk, golge, hiza){
    t = String(t).toUpperCase(); var w = t.length * 4 * b - b, sx = Math.round(hiza === 'sol' ? x : hiza === 'sag' ? x - w : x - w / 2);
    for (var gec = golge ? 0 : 1; gec < 2; gec++) for (var k = 0; k < t.length; k++) {
      var ch = t.charAt(k), cc = ch === 'Ç' ? 'C' : ch, r = F[cc]; if (!r) continue;
      c.fillStyle = gec ? renk : golge;
      for (var i = 0; i < 5; i++) { var sat = +r.charAt(i); for (var j = 0; j < 3; j++) if (sat & (4 >> j)) c.fillRect(sx + (k * 4 + j) * b + (gec ? 0 : b), y + i * b + (gec ? 0 : b), b, b); }
      if (ch === 'Ç') c.fillRect(sx + (k * 4 + 1) * b, y + 5 * b, b, b);
    }
    return w;
  };
})();
</script>
<?php
}, 20 );
