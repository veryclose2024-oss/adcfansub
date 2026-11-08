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
    S.bolum.forEach(function(b){ var n = (b === 'i' || b === 'o') ? 2 : 8; for (var i = 0; i < n; i++) yapi.push({b: b, i: i}); });
    function motif(r, yog){
      var on = [0];
      for (var s = 1; s < 32; s++) {
        var p = s % 8 === 0 ? 0.8 : s % 4 === 0 ? 0.62 : s % 2 === 0 ? 0.3 + yog * 0.12 : (yog >= 2 ? 0.1 : 0);
        if (r() < p) on.push(s);
      }
      var d = 2 + Math.floor(r() * 3), m = [], ad = [-2, -1, -1, 0, 1, 1, 2, 3, -3, 1];
      on.forEach(function(s){ d += ad[Math.floor(r() * ad.length)]; if (d < 0) d = 1 + Math.floor(r() * 2); if (d > 9) d = 7; m.push({s: s, d: d}); });
      return m;
    }
    var mA = motif(rng(S.tohum * 7 + 1), en), mA2 = motif(rng(S.tohum * 7 + 2), en),
        mB = motif(rng(S.tohum * 7 + 3), Math.min(2, en + 1)), mB2 = motif(rng(S.tohum * 7 + 4), Math.min(2, en + 1));
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
        var cift = Math.floor(y.i / 2), mot = b === 'A' ? (cift === 3 ? mA2 : mA) : (cift === 3 ? mB2 : mB), yari = (y.i % 2) * 16;
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
    var M = P.bestele(S), adim = M.adim, liste = [], seri = {kolay: 4, normal: 2, zor: (S.bpm >= 140 ? 2 : 1)}[zor];
    var serit = 1, onceD = null, sonS = -99, tutSon = {};
    function ekle(s, l, uz){
      for (var k = 0; k < 4 && tutSon[l] != null && tutSon[l] >= s; k++) l = (l + 1) % 4;
      if (tutSon[l] != null && tutSon[l] >= s) return;
      liste.push({t: s * adim, l: l, u: uz ? (s + uz) * adim : 0});
      tutSon[l] = s + (uz || 0);
    }
    M.kick.forEach(function(k){
      var y = M.yapi[Math.floor(k.s / 16)].b;
      if ((y === 'i' || y === 'o') && k.g > 0.6 && (k.s % 16 === 0 || zor !== 'kolay')) ekle(k.s, (k.s / 8) % 2 ? 2 : 1, 0);
    });
    M.lead.forEach(function(n){
      var y = M.yapi[Math.floor(n.s / 16)].b;
      if (y === 'o') return;
      if (zor === 'kolay' && n.s % 4) return;
      if (zor === 'normal' && n.s % 2) return;
      if (n.s - sonS < seri) return;
      if (onceD != null) {
        var f = n.dd - onceD;
        if (f > 0) serit += f > 2 ? 2 : 1; else if (f < 0) serit -= f < -2 ? 2 : 1;
        if (serit > 3) serit = 2; if (serit < 0) serit = 1;
      }
      onceD = n.dd; sonS = n.s;
      var uz = n.d >= (zor === 'kolay' ? 8 : 6) ? n.d - 2 : 0;
      ekle(n.s, serit, uz);
      if (zor === 'zor' && y === 'B' && n.s % 16 === 0) ekle(n.s, 3 - serit, 0);   // nakarat başında çift nota
    });
    if (zor === 'zor') M.snare.forEach(function(sn){
      if (M.yapi[Math.floor(sn.s / 16)].b !== 'A' || sn.g < 0.4) return;
      for (var i = 0; i < liste.length; i++) if (Math.abs(liste[i].t - sn.s * adim) < adim * 1.5) return;
      ekle(sn.s, sn.s % 32 === 4 ? 0 : 3, 0);
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
