<?php

/**
 * PixPulse - ekran (yeni 2026-11-08)
 * Akış sayfasında PixPulse penceresi (#pixpulse): şarkı listesi, zorluk, nota hızı, gecikme ayarı, sonuç ekranı, En iyiler.
 * Diğer üç PixPulse snippet'i açık olmalı.
 */
add_action( 'wp_footer', function() {
    if ( ! function_exists( 'adc_pp_ayar' ) || ! function_exists( 'bp_is_activity_directory' ) || ! bp_is_activity_directory() ) { return; }
    $ayar = adc_pp_ayar();
    $giris = is_user_logged_in();
    $uid = get_current_user_id();
    $tablo = adc_pp_tablo();
    $sarkilar = array();
    foreach ( adc_pp_sarkilar() as $s ) {
        $s['bolum'] = str_split( $s['bolum'] );
        unset( $s['n'] );
        $sarkilar[] = $s;
    }
    $veri = array(
        'giris'      => $giris,
        'sarkilar'   => $sarkilar,
        'gunun'      => adc_pp_gunun(),
        'carpan'     => $ayar['carpan'],
        'xp'         => function_exists( 'adc_xp_ver' ) ? $ayar['xp'] : 0,
        'hedef_ok'   => ! empty( $tablo['ben']['hedef_ok'] ),
        'toplam'     => $giris ? (int) get_user_meta( $uid, 'adc_pp_puan', true ) : 0,
        'tablo'      => $tablo,
        'ajax'       => admin_url( 'admin-ajax.php' ),
        'nonce'      => $giris ? wp_create_nonce( 'adc_pp' ) : '',
        'giris_link' => wp_login_url( home_url( '/' ) ),
    );
    echo '<script>window.adcPPV = ' . wp_json_encode( $veri ) . ';</script>';
    ?>
<script>
(function(){
  var V = window.adcPPV, P = window.adcPP;
  if (!V || !P || !P.oyna) return;
  var ZOR = [['kolay', '🐣 Kolay'], ['normal', '♥ Normal'], ['zor', '🔥 Zor']], RENK = ['#ff2bd6', '#ffcc00', '#2bd97a', '#4fc3ff', '#ff8a00', '#7a3cff'];
  var ayar = {zor: 'normal', hiz: 3, gecikme: 0}, yerel = {};
  try { var a = JSON.parse(localStorage.getItem('adc_pp_ayar') || '{}'); for (var k in a) ayar[k] = a[k]; yerel = JSON.parse(localStorage.getItem('adc_pp_yerel') || '{}') || {}; } catch (e) {}
  function sakla(){ try { localStorage.setItem('adc_pp_ayar', JSON.stringify(ayar)); localStorage.setItem('adc_pp_yerel', JSON.stringify(yerel)); } catch (e) {} }
  function el(t, c, h){ var e = document.createElement(t); if (c) e.className = c; if (h != null) e.innerHTML = h; return e; }
  function dugme(c, h, f){ var b = el('button', c, h); b.type = 'button'; if (f) b.onclick = f; return b; }
  function say(n){ return Number(n || 0).toLocaleString('tr-TR'); }
  function yazi(t){ var s = el('span'); s.textContent = t; return s.innerHTML; }
  function istek(v, cb){
    var f = new FormData(); f.append('n', V.nonce); for (var k in v) f.append(k, v[k]);
    fetch(V.ajax, {method: 'POST', body: f, credentials: 'same-origin'}).then(function(r){ return r.json(); })
      .then(function(j){ if (cb && j && j.success) cb(j.data); }).catch(function(){});
  }
  function sarki(id){ for (var i = 0; i < V.sarkilar.length; i++) if (V.sarkilar[i].id === id) return V.sarkilar[i]; return V.sarkilar[0]; }
  var kok, alan, aktif = null, son = null;

  function ac(){
    if (!kok) kur();
    kok.style.display = 'flex'; document.documentElement.style.overflow = 'hidden';
    menu();
  }
  function kapat(){
    if (aktif) { aktif.kapat(); aktif = null; }
    kok.style.display = 'none'; document.documentElement.style.overflow = '';
    if (location.hash === '#pixpulse') history.replaceState(null, '', location.pathname + location.search);
  }
  function kur(){
    kok = el('div', 'adc-pp'); kok.setAttribute('role', 'dialog'); kok.setAttribute('aria-modal', 'true'); kok.setAttribute('aria-label', 'PixPulse');
    var panel = el('div', 'adc-pp-panel'), bas = el('div', 'adc-pp-bas', '<strong>♥ PIXPULSE</strong>');
    var x = dugme('adc-pp-x', '✕', kapat); x.setAttribute('aria-label', 'Kapat');
    bas.appendChild(x); panel.appendChild(bas);
    alan = el('div'); panel.appendChild(alan); kok.appendChild(panel);
    kok.addEventListener('click', function(e){ if (e.target === kok && !aktif) kapat(); });
    document.addEventListener('keydown', function(e){
      if (kok.style.display !== 'flex') return;
      if (e.key === 'Escape') { kapat(); return; }
      if (e.key === 'Enter' && son && !aktif) { e.preventDefault(); oyna(son.S); }
    });
    document.body.appendChild(kok);
  }
  function gen(){ return Math.min(window.innerWidth - 26, 440) + 'px'; }

  // ===== Menü =====
  function menu(){
    if (aktif) { aktif.kapat(); aktif = null; }
    son = null; alan.innerHTML = '';
    var g = el('div', 'adc-pp-govde'); g.style.width = gen(); alan.appendChild(g);
    var ust = el('div', 'adc-pp-ust');
    ust.appendChild(el('span', 'adc-pp-rekor', V.giris ? 'Toplam: ' + say(V.toplam) + ' puan' : 'Misafir olarak oynuyorsun'));
    ust.appendChild(dugme('adc-pp-btn', '🏆 En iyiler', function(){ siralama('bugun'); }));
    g.appendChild(ust);
    var gs = sarki(V.gunun);
    var hb = el('div', 'adc-pp-hedef', '🎯 Günün şarkısı: <b>' + yazi(gs.ad) + '</b> · Normal ya da Zor\'da B ve üstü'
      + (V.hedef_ok ? ' · ✓ bugün tamam' : (V.giris && V.xp ? ' · +' + V.xp + ' XP' : '')));
    g.appendChild(hb);
    var zs = el('div', 'adc-pp-sekmeler');
    ZOR.forEach(function(z){ zs.appendChild(dugme('adc-pp-btn' + (ayar.zor === z[0] ? ' adc-pp-secili' : ''), z[1], function(){ ayar.zor = z[0]; sakla(); menu(); })); });
    g.appendChild(zs);
    var liste = el('div', 'adc-pp-sarkilar');
    V.sarkilar.forEach(function(S, i){
      var y = yerel[S.id + '|' + ayar.zor], yild = S.yildiz[ayar.zor === 'kolay' ? 0 : ayar.zor === 'normal' ? 1 : 2];
      var b = dugme('adc-pp-sarki' + (S.id === V.gunun ? ' adc-pp-gunun' : ''));
      b.style.setProperty('--r', RENK[i % RENK.length]);
      b.innerHTML = '<i aria-hidden="true">♥</i><span><b>' + yazi(S.ad) + '</b><small>' + S.bpm + ' BPM · ' + '★★★★★'.slice(0, yild) + '☆☆☆☆☆'.slice(0, 5 - yild)
        + ' · ' + Math.round(S.sure) + ' sn' + (S.id === V.gunun ? ' · 🎯 bugün' : '') + '</small></span>'
        + '<em>' + (y ? '<b>' + y.h + '</b>' + say(y.s) : '▶') + '</em>';
      b.onclick = function(){ P.ac(); oyna(S); };
      liste.appendChild(b);
    });
    g.appendChild(liste);
    var alt = el('div', 'adc-pp-ayarlar');
    var hz = el('span', '', 'Nota hızı: <b>' + ayar.hiz + '</b>');
    alt.appendChild(hz);
    alt.appendChild(dugme('adc-pp-btn adc-pp-kucuk', '−', function(){ ayar.hiz = Math.max(1, ayar.hiz - 1); sakla(); menu(); }));
    alt.appendChild(dugme('adc-pp-btn adc-pp-kucuk', '+', function(){ ayar.hiz = Math.min(5, ayar.hiz + 1); sakla(); menu(); }));
    alt.appendChild(dugme('adc-pp-btn', '⚙ Gecikme: ' + ayar.gecikme + ' ms', gecikme));
    g.appendChild(alt);
    g.appendChild(el('p', 'adc-pp-not', '🔊 Sesi aç, en güzeli kulaklıkla. iPhone\'da yan taraftaki sessiz mod düğmesi kapalı olmalı. Notalar çizgiye gelince şeride dokun, uzun notalarda parmağını kaldırma. Bilgisayarda D F J K ya da ok tuşları.'));
  }

  // ===== Oyun =====
  function oyna(S){
    if (aktif) { aktif.kapat(); aktif = null; }
    alan.innerHTML = '';
    var kutu = el('div', 'adc-pp-oyun'); alan.appendChild(kutu);
    var sunucu = V.giris;
    aktif = P.oyna({
      S: S, zor: ayar.zor, hiz: ayar.hiz, gecikme: ayar.gecikme, carpan: V.carpan[ayar.zor], alan: kutu,
      basladi: function(){ if (sunucu) istek({action: 'adc_pp_basla', sarki: S.id, zorluk: ayar.zor}); },
      cik: menu,
      bitti: function(r){ aktif = null; sonuc(S, ayar.zor, r, sunucu); }
    });
  }

  function sonuc(S, zor, r, sunucu){
    son = {S: S};
    var puan = P.puan(r, r.n, V.carpan[zor]), harf = P.harf(r, r.n), anahtar = S.id + '|' + zor, eski = yerel[anahtar];
    var yeni = !eski || puan > eski.s;
    if (yeni) { yerel[anahtar] = {s: puan, h: harf}; sakla(); }
    var fc = r.m === 0;
    alan.innerHTML = '';
    var g = el('div', 'adc-pp-govde adc-pp-sonuc'); g.style.width = gen(); alan.appendChild(g);
    g.innerHTML = '<div class="adc-pp-sarkiadi">♥ ' + yazi(S.ad) + ' · ' + ZOR.filter(function(z){ return z[0] === zor; })[0][1] + '</div>'
      + '<div class="adc-pp-harf adc-pp-h' + harf.charAt(0) + '">' + harf + '</div>'
      + '<div class="adc-pp-buyuk">' + say(puan) + '</div>'
      + (r.p === r.n ? '<span class="adc-pp-yeni">PERFECT PULSE!</span>' : fc ? '<span class="adc-pp-yeni">FULL COMBO!</span>' : '')
      + (yeni && eski ? ' <span class="adc-pp-yeni">YENİ EN İYİ!</span>' : '')
      + '<div class="adc-pp-sayilar"><span style="color:#d6009f">PERFECT ' + r.p + '</span><span style="color:#b38600">GREAT ' + r.g + '</span><span style="color:#0f8a4a">GOOD ' + r.o + '</span><span style="color:#d42a2a">MISS ' + r.m + '</span></div>'
      + '<small>İsabet %' + (P.isabet(r, r.n) * 100).toFixed(1) + ' · En uzun kombo ' + r.k + ' / ' + r.n + '</small>'
      + '<small class="adc-pp-odul">' + (V.giris ? 'Puan kaydediliyor…' : 'Giriş yaparsan puanın sıralamaya girer!') + '</small>'
      + '<div class="adc-pp-dugmeler"></div>';
    var d = g.querySelector('.adc-pp-dugmeler');
    d.appendChild(dugme('adc-pp-btn adc-pp-ana', '↻ Tekrar', function(){ P.ac(); oyna(S); }));
    d.appendChild(dugme('adc-pp-btn', '♪ Şarkılar', menu));
    d.appendChild(dugme('adc-pp-btn', '🏆 En iyiler', function(){ siralama('bugun'); }));
    if (!sunucu) return;
    istek({action: 'adc_pp_bitti', sarki: S.id, zorluk: zor, p: r.p, g: r.g, o: r.o, m: r.m, k: r.k}, function(x){
      var od = g.querySelector('.adc-pp-odul');
      if (x.tablo) V.tablo = x.tablo;
      if (x.toplam != null) V.toplam = x.toplam;
      if (V.tablo.ben) V.hedef_ok = !!V.tablo.ben.hedef_ok;
      if (!od) return;
      if (!x.sayildi) { od.textContent = 'Bu el sayılmadı (şarkı yarıda kalmış olabilir).'; return; }
      var m = [];
      if (x.xp) m.push('🎯 Günün şarkısı tamam! +' + x.xp + ' XP');
      if (x.artis > 0) m.push('Toplam puanın +' + say(x.artis) + ' arttı');
      var b = V.tablo.ben; if (b && b.gun_sira) m.push('Bugün ' + b.gun_sira + '. sıradasın');
      od.textContent = m.join(' · ') || 'Puan kaydedildi ✓';
    });
  }

  // ===== Gecikme ayarı =====
  function gecikme(){
    alan.innerHTML = '';
    var g = el('div', 'adc-pp-govde'); g.style.width = gen(); alan.appendChild(g);
    var ic = el('div'); g.appendChild(ic);
    P.ayarla(ic, function(ms){
      g.innerHTML = '';
      var k = el('div', 'adc-pp-ayar', ms == null ? '<strong>Ses açılamadı</strong>' : '<strong>Gecikmen: ' + ms + ' ms</strong><small>' + (Math.abs(ms) < 30 ? 'Neredeyse sıfır, harika!' : ms > 0 ? 'Ses biraz geç geliyor, oyun bunu hesaba katacak.' : 'Biraz erken dokunuyorsun, oyun bunu hesaba katacak.') + '</small>');
      var d = el('div', 'adc-pp-dugmeler');
      if (ms != null) d.appendChild(dugme('adc-pp-btn adc-pp-ana', '✓ Kaydet', function(){ ayar.gecikme = ms; sakla(); menu(); }));
      d.appendChild(dugme('adc-pp-btn', '↻ Tekrar', gecikme));
      d.appendChild(dugme('adc-pp-btn', '0 ms yap', function(){ ayar.gecikme = 0; sakla(); menu(); }));
      d.appendChild(dugme('adc-pp-btn', '← Geri', menu));
      k.appendChild(d); g.appendChild(k);
    });
    var geri = dugme('adc-pp-btn', '← Geri', menu); geri.style.marginTop = '10px'; g.appendChild(geri);
  }

  // ===== En iyiler =====
  var TAC = '<svg width="28" height="20" viewBox="0 0 7 5" shape-rendering="crispEdges" aria-hidden="true"><path fill="#ffcc00" d="M0 0h1v5H0zM3 0h1v5H3zM6 0h1v5H6zM1 1h1v4H1zM5 1h1v4H5zM2 2h1v3H2zM4 2h1v3H4z"/><path fill="#ff2bd6" d="M3 3h1v1H3z"/><path fill="#e0a800" d="M0 4h7v1H0z"/></svg>';
  function avatar(k, b, s){ var i = el('img', 'adc-pp-av adc-pp-av' + s); i.src = k.resim || ''; i.alt = ''; i.width = b; i.height = b; return i; }
  function siralama(sekme){
    if (aktif) { aktif.kapat(); aktif = null; }
    alan.innerHTML = '';
    var g = el('div', 'adc-pp-govde'); g.style.width = gen(); alan.appendChild(g);
    var ust = el('div', 'adc-pp-ust'); ust.style.padding = '0 0 10px';
    ust.appendChild(dugme('adc-pp-btn', '← Şarkılar', menu)); ust.appendChild(el('span', 'adc-pp-rekor', '🏆 En iyiler'));
    g.appendChild(ust);
    var sek = el('div', 'adc-pp-sekmeler');
    [['bugun', 'Bugün'], ['genel', 'Tüm zamanlar']].forEach(function(s){ sek.appendChild(dugme('adc-pp-btn' + (s[0] === sekme ? ' adc-pp-secili' : ''), s[1], function(){ siralama(s[0]); })); });
    g.appendChild(sek);
    var liste = V.tablo[sekme] || [];
    if (!liste.length) g.appendChild(el('div', 'adc-pp-bos', TAC + '<br>' + (sekme === 'bugun' ? 'Bugün henüz kimse çalmadı. İlk sen ol!' : 'Henüz kimse yok. İlk sen ol!')));
    else {
      var pod = el('div', 'adc-pp-podyum');
      [1, 0, 2].forEach(function(i){
        var k = liste[i]; if (!k) { pod.appendChild(el('div', 'adc-pp-basamak')); return; }
        var b = el('div', 'adc-pp-basamak adc-pp-b' + (i + 1));
        b.appendChild(el('div', 'adc-pp-tac', i === 0 ? TAC : ''));
        b.appendChild(avatar(k, i === 0 ? 52 : 42, i));
        var ad = el('div', 'adc-pp-isim'); ad.textContent = k.ad; b.appendChild(ad);
        b.appendChild(el('div', 'adc-pp-deger', say(k.deger)));
        b.appendChild(el('div', 'adc-pp-blok', String(i + 1)));
        pod.appendChild(b);
      });
      g.appendChild(pod);
      if (liste.length > 3) {
        var ol = el('div', 'adc-pp-liste');
        liste.slice(3).forEach(function(k, i){
          var r = el('div', 'adc-pp-satir' + (k.ben ? ' adc-pp-ben' : ''));
          r.appendChild(el('span', 'adc-pp-no', (i + 4) + '.')); r.appendChild(avatar(k, 28, 9));
          var a = el('span', 'adc-pp-isim2'); a.textContent = k.ad;
          if (k.seviye) { var sv = el('small'); sv.textContent = ' · ' + k.seviye; a.appendChild(sv); }
          r.appendChild(a); r.appendChild(el('span', 'adc-pp-deger', say(k.deger))); ol.appendChild(r);
        });
        g.appendChild(ol);
      }
    }
    var ben = el('div', 'adc-pp-benim'), b2 = V.tablo.ben;
    if (!V.giris) { ben.appendChild(document.createTextNode('Sen de listeye girmek ister misin? ')); var gl = el('a'); gl.href = V.giris_link; gl.textContent = 'Giriş yap'; ben.appendChild(gl); }
    else if (sekme === 'genel') ben.textContent = b2 && b2.sira ? 'Sen: ' + b2.sira + '. sıra · toplam ' + say(b2.toplam) : 'Bir şarkı çal, listeye gir!';
    else ben.textContent = b2 && b2.gun_sira ? 'Bugün ' + b2.gun_sira + '. sıradasın · ' + say(b2.gun_skor) : 'Bugün bir şarkı çal, listeye gir!';
    g.appendChild(ben);
    g.appendChild(el('p', 'adc-pp-not', '<strong>Bugün:</strong> bugün çalınan en yüksek tek şarkı puanı, her gece sıfırlanır. <strong>Tüm zamanlar:</strong> her şarkıdaki en iyi puanının toplamı (yani her şarkı ayrı sayılır). İlk 3 madalya rozeti kazanır! Zorluk çarpanı: Kolay ×' + V.carpan.kolay + ', Normal ×1, Zor ×' + V.carpan.zor + '.'));
  }

  document.addEventListener('click', function(e){
    var a = e.target.closest && e.target.closest('[data-adc-oyun="pixpulse"],a[href$="#pixpulse"]');
    if (!a) return;
    e.preventDefault(); ac();
  });
  window.addEventListener('hashchange', function(){ if (location.hash === '#pixpulse') ac(); });
  if (location.hash === '#pixpulse') ac();
})();
</script>
<?php
}, 22 );
