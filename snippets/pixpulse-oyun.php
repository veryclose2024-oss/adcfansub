<?php

/**
 * PixPulse - oyun (yeni 2026-11-08)
 * Oyun alanı: 4 şerit, kalp notaları, uzun notalar, EKG çizgisi, PERFECT / GREAT / GOOD / MISS, kombo, PULSE modu,
 * duraklatma, gecikme ayarı. "PixPulse" ve "PixPulse - müzik" açık olmalı.
 */
add_action( 'wp_footer', function() {
    if ( ! function_exists( 'adc_pp_ayar' ) || ! function_exists( 'bp_is_activity_directory' ) || ! bp_is_activity_directory() ) { return; }
    ?>
<script>
(function(){
  // PixPulse: oyun alanı (4 şerit, kalp notaları, EKG çizgisi) + gecikme ayarı
  var P = window.adcPP; if (!P) return;
  var EN = 144, BOY = 256, HIT = 220, SX = 8, SW = 32;
  var RENK = ['#ff2bd6', '#ffcc00', '#2bd97a', '#4fc3ff'], KOYU = ['#7a1568', '#7a6200', '#0f6b3a', '#1c5f85'];
  var PEN = {kolay: 1.3, normal: 1, zor: 0.9}, W = {p: 0.05, g: 0.095, o: 0.14};
  var YAK = [1.8, 1.45, 1.15, 0.92, 0.74];
  var TUS = {d: 0, a: 0, arrowleft: 0, f: 1, s: 1, arrowdown: 1, j: 2, arrowup: 2, k: 3, l: 3, arrowright: 3};
  var KALP = ['.xx...xx.', 'xxxx.xxxx', 'xxxxxxxxx', 'xxxxxxxxx', '.xxxxxxx.', '..xxxxx..', '...xxx...', '....x....'];
  var YAZI = {p: ['PERFECT', '#ff9af0'], g: ['GREAT', '#ffe066'], o: ['GOOD', '#8af5b4'], m: ['MISS', '#ff5a5a']};

  function el(t, c, h){ var e = document.createElement(t); if (c) e.className = c; if (h != null) e.innerHTML = h; return e; }

  P.oyna = function(o){
    var S = o.S, zor = o.zor, pen = PEN[zor] || 1, ctx = P.ac(), M = P.bestele(S);
    var notalar = P.nota(S, zor).map(function(n){ return {t: n.t, l: n.l, u: n.u, j: null}; });
    var N = notalar.length, son = notalar[N - 1], bitis = Math.max(son.t, son.u) + 1.0;
    var yak = YAK[(o.hiz || 3) - 1], pps = (HIT + 12) / yak, gec = (o.gecikme || 0) / 1000;
    var vurus = 60 / S.bpm, s = {p: 0, g: 0, o: 0, m: 0, k: 0}, kombo = 0, durum = 'yukle', ilk = 0;
    var basli = [0, 0, 0, 0], tut = [null, null, null, null], tepe = [0, 0, 0, 0], kirmizi = [0, 0, 0, 0], parca = [], yazi = null, src = null, bas = 0, dongu = 0, sonT = -9;

    var sahne = el('div', 'adc-pp-sahne'), tuval = el('canvas'), ortu = el('div', 'adc-pp-ortu'), dur = el('button', 'adc-pp-dur', '⏸');
    dur.type = 'button'; dur.setAttribute('aria-label', 'Duraklat');
    tuval.setAttribute('aria-label', 'Oyun alanı: 4 şerit. Telefonda şeride dokun, bilgisayarda D F J K ya da ok tuşları.');
    sahne.appendChild(tuval); sahne.appendChild(ortu); sahne.appendChild(dur);
    o.alan.innerHTML = ''; o.alan.appendChild(sahne);
    var c, olc = 1;
    function boyutla(){
      var gW = Math.min(window.innerWidth - 46, 420), gH = window.innerHeight - 20 - 6 - 34 - 20;
      var w = Math.max(160, Math.min(gW, Math.floor(gH * EN / BOY))), h = Math.round(w * BOY / EN);
      tuval.style.width = w + 'px'; tuval.style.height = h + 'px';
      olc = Math.max(1, Math.ceil(w * (window.devicePixelRatio || 1) / EN));
      tuval.width = EN * olc; tuval.height = BOY * olc;
      c = tuval.getContext('2d'); c.setTransform(olc, 0, 0, olc, 0, 0); c.imageSmoothingEnabled = false;
    }
    boyutla();
    function ortuGoster(h){ ortu.innerHTML = h || ''; ortu.style.display = h ? 'flex' : 'none'; }
    ortuGoster('<div class="adc-pp-kutu"><strong>♥ HAZIRLANIYOR…</strong><small>Şarkı çalınmaya hazırlanıyor</small></div>');

    function zaman(perf){ return P.sesZamani(perf) - bas - gec; }

    P.sesHazirla(S, function(buf){
      if (durum === 'kapandi') return;
      if (!buf || !ctx) { ortuGoster('<div class="adc-pp-kutu"><strong>SES AÇILAMADI</strong><small>Bu tarayıcı ses çalamıyor. Başka bir tarayıcı dene.</small></div>'); return; }
      src = ctx.createBufferSource(); src.buffer = buf; src.connect(ctx.destination);
      var t0 = ctx.currentTime + 2.0; src.start(t0); bas = t0 + P.SES_BAS;
      durum = 'oyun'; ortuGoster('');
      if (o.basladi) o.basladi();
      P.son = {zaman: zaman, notalar: notalar};
      dongu = requestAnimationFrame(kare);
    });

    // ===== Girdi =====
    function vur(l, perf){
      if (durum !== 'oyun') return;
      basli[l] = 1;
      var t = zaman(perf), en = null, ef = 9;
      for (var i = ilk; i < N; i++) {
        var n = notalar[i]; if (n.t - t > W.o * pen) break;
        if (n.l !== l || n.j) continue;
        var d = Math.abs(n.t - t); if (d <= W.o * pen && d < ef) { ef = d; en = n; }
      }
      if (!en) return;
      var dt = t - en.t, a = Math.abs(dt), j = a <= W.p * pen ? 'p' : a <= W.g * pen ? 'g' : 'o';
      if (en.u) { en.j = 'tut'; en.jh = j; en.dt = dt; tut[l] = en; tepe[l] = 1; } else sonuc(en, j, dt);
    }
    function birak(l, perf){
      basli[l] = 0;
      var n = tut[l]; if (!n || durum !== 'oyun') return;
      tut[l] = null;
      if (zaman(perf) < n.u - 0.12) sonuc(n, 'o', 0, 'ERKEN BIRAKTIN'); else sonuc(n, n.jh, n.dt);
    }
    function sonuc(n, j, dt, not){
      n.j = j; s[j]++;
      if (j === 'm') { kombo = 0; kirmizi[n.l] = 1; }
      else {
        kombo++; if (kombo > s.k) s.k = kombo; tepe[n.l] = 1;
        for (var i = 0; i < (j === 'p' ? 7 : 4); i++) parca.push({x: SX + n.l * SW + SW / 2, y: HIT, vx: (Math.random() - 0.5) * 70, vy: -30 - Math.random() * 60, t: 0, r: RENK[n.l]});
      }
      var ek = not || (j !== 'm' && j !== 'p' ? (dt < 0 ? 'ERKEN' : 'GEÇ') : '');
      yazi = {j: j, ek: ek, t: performance.now()};
    }
    var isaret = {};
    function pd(e){
      e.preventDefault();
      var r = tuval.getBoundingClientRect(), x = (e.clientX - r.left) / r.width * EN;
      var l = Math.max(0, Math.min(3, Math.floor((x - SX) / SW)));
      isaret[e.pointerId] = l; vur(l, e.timeStamp || performance.now());
    }
    function pu(e){ var l = isaret[e.pointerId]; if (l == null) return; delete isaret[e.pointerId]; birak(l, e.timeStamp || performance.now()); }
    tuval.addEventListener('pointerdown', pd);
    tuval.addEventListener('pointerup', pu); tuval.addEventListener('pointercancel', pu); tuval.addEventListener('pointerleave', pu);
    tuval.addEventListener('contextmenu', function(e){ e.preventDefault(); });
    function kd(e){
      var l = TUS[e.key.toLowerCase()]; if (l == null) return;
      e.preventDefault(); if (e.repeat) return; vur(l, e.timeStamp || performance.now());
    }
    function ku(e){ var l = TUS[e.key.toLowerCase()]; if (l != null) birak(l, e.timeStamp || performance.now()); }
    function gizli(){ if (document.hidden && durum === 'oyun') duraklat(); }
    document.addEventListener('keydown', kd); document.addEventListener('keyup', ku);
    document.addEventListener('visibilitychange', gizli);
    window.addEventListener('resize', boyutla);
    dur.onclick = function(){ if (durum === 'oyun') duraklat(); };

    function duraklat(){
      durum = 'duraklat'; ctx.suspend();
      for (var l = 0; l < 4; l++) { basli[l] = 0; tut[l] = null; }
      notalar.forEach(function(n){ if (n.j === 'tut') n.j = null; });
      ortuGoster('<div class="adc-pp-kutu"><strong>DURAKLATILDI</strong><small>Şarkı yerinde bekliyor.</small><div class="adc-pp-dugmeler"></div></div>');
      var d = ortu.querySelector('.adc-pp-dugmeler');
      var dv = el('button', 'adc-pp-btn adc-pp-ana', '▶ Devam'); dv.type = 'button';
      dv.onclick = function(){
        ortuGoster('<div class="adc-pp-kutu"><strong>HAZIR…</strong></div>');
        ctx.resume().then(function(){ setTimeout(function(){ if (durum !== 'duraklat') return; durum = 'oyun'; ortuGoster(''); }, 450); });
      };
      var cx = el('button', 'adc-pp-btn', '✕ Şarkıdan çık'); cx.type = 'button'; cx.onclick = function(){ kapat(); if (o.cik) o.cik(); };
      d.appendChild(dv); d.appendChild(cx);
    }
    function kapat(){
      durum = 'kapandi'; cancelAnimationFrame(dongu);
      try { if (src) src.stop(); } catch (e) {}
      if (ctx && ctx.state === 'suspended') ctx.resume();
      document.removeEventListener('keydown', kd); document.removeEventListener('keyup', ku);
      document.removeEventListener('visibilitychange', gizli); window.removeEventListener('resize', boyutla);
    }

    // ===== Döngü =====
    var oncePerf = performance.now();
    function kare(perf){
      if (durum === 'kapandi' || !tuval.isConnected) { if (durum !== 'kapandi') kapat(); return; }
      var dt = Math.min(0.05, (perf - oncePerf) / 1000); oncePerf = perf;
      var t = durum === 'oyun' ? zaman(performance.now()) : sonT;
      if (durum === 'oyun') {
        if (t < sonT && sonT - t < 0.05) t = sonT;   // saat geri gitmesin
        sonT = t;
        for (var i = ilk; i < N; i++) {
          var n = notalar[i]; if (n.t - t > 0.2) break;
          if (n.j === 'tut' && t >= n.u) { tut[n.l] = null; sonuc(n, n.jh, n.dt); }
          else if (!n.j && t - n.t > W.o * pen) sonuc(n, 'm', 1);
        }
        while (ilk < N && notalar[ilk].j && notalar[ilk].j !== 'tut') ilk++;
        if (t > bitis && ilk >= N) { kapat(); o.bitti({p: s.p, g: s.g, o: s.o, m: s.m, k: s.k, n: N}); return; }
      }
      for (var l = 0; l < 4; l++) { tepe[l] = Math.max(0, tepe[l] - dt * 3); kirmizi[l] = Math.max(0, kirmizi[l] - dt * 2.5); }
      for (var q = parca.length - 1; q >= 0; q--) { var pp = parca[q]; pp.t += dt; pp.x += pp.vx * dt; pp.y += pp.vy * dt; pp.vy += 160 * dt; if (pp.t > 0.5) parca.splice(q, 1); }
      ciz(t);
      dongu = requestAnimationFrame(kare);
    }

    function kutu(x, y, w, h, r){ c.fillStyle = r; c.fillRect(Math.round(x), Math.round(y), w, h); }
    function sprite(sp, x, y, b, r){ c.fillStyle = r; for (var i = 0; i < sp.length; i++) for (var j = 0; j < sp[i].length; j++) if (sp[i].charAt(j) === 'x') c.fillRect(x + j * b, y + i * b, b, b); }
    function ciz(t){
      var nabiz = kombo >= 30, ritim = t > -1 ? 1 - ((t / vurus) % 1 + 1) % 1 : 0, at = Math.pow(ritim, 3);
      kutu(0, 0, EN, BOY, nabiz ? '#1c0b33' : '#0d0b2e');
      for (var g = 8; g < BOY; g += 16) kutu(0, g, EN, 1, '#17144a');
      // şeritler
      for (var l = 0; l < 4; l++) {
        var x = SX + l * SW;
        kutu(x, 0, SW, BOY, l % 2 ? '#120f3a' : '#100d34');
        if (basli[l]) { c.globalAlpha = 0.22; kutu(x, 0, SW, HIT, RENK[l]); c.globalAlpha = 1; }
        kutu(x, 0, 1, BOY, '#262070');
      }
      kutu(SX + 4 * SW, 0, 1, BOY, '#262070');
      // arkada atan büyük kalp
      c.globalAlpha = (nabiz ? 0.4 : 0.22) + at * 0.15;
      var b = 5 + (at > 0.5 ? 1 : 0); sprite(KALP, EN / 2 - 4.5 * b, 70 - 4 * b, b, nabiz ? '#ff2bd6' : '#5a3cff');
      c.globalAlpha = 1;
      // alıcılar
      for (l = 0; l < 4; l++) {
        x = SX + l * SW + 4;
        kutu(x, HIT - 4, 24, 9, basli[l] ? RENK[l] : '#0d0b2e');
        kutu(x, HIT - 4, 24, 1, RENK[l]); kutu(x, HIT + 4, 24, 1, RENK[l]); kutu(x, HIT - 3, 1, 7, RENK[l]); kutu(x + 23, HIT - 3, 1, 7, RENK[l]);
      }
      // notalar
      for (var i = ilk; i < N; i++) {
        var n = notalar[i], y = HIT - (n.t - t) * pps;
        if (y < -20) break;
        if (n.j && n.j !== 'tut') continue;
        x = SX + n.l * SW;
        if (n.u) {
          var y2 = HIT - (n.u - t) * pps, yb = n.j === 'tut' ? HIT : y;
          if (yb > y2) { kutu(x + 12, y2, 8, yb - y2, n.j === 'tut' ? RENK[n.l] : KOYU[n.l]); kutu(x + 14, y2, 2, yb - y2, 'rgba(255,255,255,.35)'); kutu(x + 10, y2 - 2, 12, 3, RENK[n.l]); }
          if (n.j === 'tut') y = HIT;
        }
        kutu(x + 3, y - 5, 26, 10, '#05041a');
        kutu(x + 4, y - 4, 24, 8, RENK[n.l]); kutu(x + 5, y - 4, 22, 1, 'rgba(255,255,255,.6)');
        sprite(['x.x', 'xxx', '.x.'], x + 14, y - 2, 1, '#05041a');
      }
      // EKG çizgisi: vuruşta şeridin altında kalp atışı tepesi
      var ey = HIT + 18;
      kutu(0, ey, EN, 1, '#0f6b3a');
      for (l = 0; l < 4; l++) {
        x = SX + l * SW; var A = tepe[l] * 12 + at * 2;
        var renk = kirmizi[l] > 0 ? '#ff5a5a' : '#2bd97a';
        if (kirmizi[l] > 0) { kutu(x, ey, SW, 1, '#ff5a5a'); continue; }
        kutu(x, ey, 10, 1, renk); kutu(x + 10, ey + 1, 2, 1, renk);
        kutu(x + 12, ey - A, 1, A + 1, renk); kutu(x + 13, ey - A, 1, 1, '#ffffff');
        kutu(x + 14, ey, 1, Math.round(A / 3) + 1, renk); kutu(x + 15, ey + Math.round(A / 3), 3, 1, renk);
        kutu(x + 18, ey - 2, 4, 1, renk); kutu(x + 22, ey, 10, 1, renk);
      }
      // parçacıklar
      parca.forEach(function(pp){ kutu(pp.x, pp.y, 2, 2, pp.r); });
      // üst bilgi
      kutu(0, 0, EN, 15, 'rgba(5,4,26,.82)');
      var ilerleme = Math.max(0, Math.min(1, t / bitis));
      kutu(0, 0, EN, 3, '#05041a'); kutu(0, 0, Math.round(EN * ilerleme), 2, '#ff2bd6');
      var yz = s.p + s.g + s.o + s.m;
      P.yaz(c, (yz ? (P.isabet(s, yz) * 100).toFixed(1) : '100.0') + '%', 4, 7, 1, '#ffffff', '#05041a', 'sol');
      P.yaz(c, P.puan(s, N, o.carpan), EN - 4, 7, 1, '#ffffff', '#05041a', 'sag');
      if (kombo >= 3) {
        P.yaz(c, kombo, EN / 2, 112, 3, nabiz ? '#ff9af0' : '#ffffff', '#05041a');
        P.yaz(c, nabiz ? 'PULSE!' : 'COMBO', EN / 2, 131, 1, nabiz ? '#ff2bd6' : '#8f86ff', '#05041a');
      }
      if (t < 0 && durum === 'oyun') P.yaz(c, t < -1 ? 'HAZIR?' : 'BASLA!', EN / 2, 150, 2, '#ffffff', '#05041a');
      // karar yazısı
      if (yazi) {
        var ge = (performance.now() - yazi.t) / 1000;
        if (ge > 0.5) yazi = null;
        else {
          var Y = YAZI[yazi.j];
          P.yaz(c, Y[0], EN / 2, 168 - Math.round(ge * 10), ge < 0.08 ? 3 : 2, Y[1], '#05041a');
          if (yazi.ek) P.yaz(c, yazi.ek, EN / 2, 184, 1, yazi.ek === 'GEÇ' ? '#ffb27a' : '#9ad8ff', '#05041a');
        }
      }
    }
    return {kapat: kapat};
  };

  // ===== Gecikme ayarı: tık sesine dokun, aradaki farkın ortancası = gecikme =====
  P.ayarla = function(alan, bitti){
    var ctx = P.ac(); if (!ctx) { bitti(null); return; }
    alan.innerHTML = '';
    var k = el('div', 'adc-pp-ayar', '<strong>⚙ GECİKME AYARI</strong><small>Kulaklığın ya da telefonun sesi biraz geç verebilir. Her <b>tık</b> sesinde aşağıdaki kalbe dokun (bilgisayarda boşluk tuşu). 12 tık çalacak.</small>');
    var ped = el('button', 'adc-pp-ped', '♥'); ped.type = 'button';
    var bilgi = el('div', 'adc-pp-ayar-bilgi', 'Hazır olunca kalbe dokun');
    k.appendChild(ped); k.appendChild(bilgi); alan.appendChild(k);
    var tiklar = null, farklar = [], ARA = 0.6, SAY = 12;
    function bas(perf){
      if (!tiklar) {
        tiklar = []; var t0 = ctx.currentTime + 0.8;
        for (var i = 0; i < SAY; i++) { tiklar.push(t0 + i * ARA); P.tik(t0 + i * ARA, i < 4 ? 1500 : 1000); }
        bilgi.textContent = 'Dinle… ilk 4 tıktan sonra sen de dokun';
        setTimeout(bitir, (0.8 + SAY * ARA + 0.4) * 1000);
        return;
      }
      ped.classList.remove('adc-pp-vur'); void ped.offsetWidth; ped.classList.add('adc-pp-vur');
      var t = P.sesZamani(perf), en = 9;
      tiklar.forEach(function(x){ if (Math.abs(t - x) < Math.abs(en)) en = t - x; });
      if (Math.abs(en) < 0.3) farklar.push(en);
      bilgi.textContent = 'Dokunuş: ' + farklar.length;
    }
    function bitir(){
      if (!alan.isConnected || !k.isConnected) return;
      document.removeEventListener('keydown', tus);
      if (farklar.length < 5) { bilgi.textContent = 'Yeterli dokunuş olmadı. Kalbe dokunup tekrar dene.'; tiklar = null; farklar = []; document.addEventListener('keydown', tus); return; }
      farklar.sort(function(a, b){ return a - b; });
      var ms = Math.round(farklar[Math.floor(farklar.length / 2)] * 1000);
      ms = Math.max(-250, Math.min(400, ms));
      bitti(ms);
    }
    ped.addEventListener('pointerdown', function(e){ e.preventDefault(); bas(e.timeStamp || performance.now()); });
    function tus(e){ if (e.key === ' ' && k.isConnected) { e.preventDefault(); if (!e.repeat) bas(e.timeStamp || performance.now()); } }
    document.addEventListener('keydown', tus);
  };
})();
</script>
<?php
}, 21 );
