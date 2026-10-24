<?php

/**
 * HK - şerit (yeni, 2026-10-23)
 * History King Mini Games ızgarasında küçük kutu değil (Deniz: "kapsamlı bir oyun"): Underworld şeridinin hemen üstünde kendi şeridi.
 * Sol: HK piksel logo + HISTORY KING. Orta: Günün Seferi (bugünün devleti) + tüm zamanlar zirvesindeki üye (ad, puan, unvan).
 * Sağ: ⚔ SEFERE ÇIK (bu tarayıcıda yarım kalmış sefer varsa ▶ DEVAM ET: devlet + tur).
 * Şeridin her yeri oyunu açar (data-adc-oyun="hk", pencereyi "HK - ekran" açar).
 * Yer: öncelik 14 ile yazılır, sonra küçük bir kod onu Underworld şeridinin (.adc-uws) hemen önüne taşır
 * (Underworld yoksa Boss Savaşı şeridinin, o da yoksa Mini Games kutusunun altına).
 * Gerekenler: HK (çekirdek). Eski kurulumda HK Mini Games'te kutucuksa onu da kaldırır.
 */

add_filter( 'adc_mini_oyunlar', function( $oyunlar ) {
    foreach ( $oyunlar as $i => $o ) {
        if ( isset( $o['id'] ) && 'hk' === $o['id'] ) { unset( $oyunlar[ $i ] ); }
    }
    return array_values( $oyunlar );
}, 99 );

add_action( 'bp_before_directory_activity_content', function() {
    if ( ! function_exists( 'adc_hk_uluslar' ) || ! function_exists( 'adc_hk_tablo' ) ) { return; }
    $uluslar = adc_hk_uluslar();
    $gu      = $uluslar[ adc_hk_gunun_ulusu( current_time( 'Y-m-d' ) ) ];
    $tablo   = adc_hk_tablo();
    $zirve   = $tablo['genel'] ? $tablo['genel'][0] : null;
    $adlar   = array();
    foreach ( $uluslar as $u ) { $adlar[] = $u['ad']; }

    echo '<a id="adc-hks" class="adc-hks" href="#hk" data-adc-oyun="hk">';
    echo '<span class="adc-hks-sol">' . adc_hk_logo( 34 ) . '</span>';
    echo '<span class="adc-hks-orta"><span class="adc-hks-ad"><b>HISTORY KING</b><small>strateji · 10 devlet · 102 il</small></span>';
    echo '<span class="adc-hks-satir">📅 Günün Seferi: <i style="background:' . esc_attr( $gu['renk'] ) . '"></i><b>' . esc_html( $gu['ad'] ) . '</b></span>';
    if ( $zirve ) {
        echo '<span class="adc-hks-satir">👑 Zirvede <b>' . esc_html( $zirve['ad'] ) . '</b> · ' . (int) $zirve['deger'] . ' · ' . esc_html( $zirve['unvan'] ) . '</span>';
    } else {
        echo '<span class="adc-hks-satir">👑 Taht boş · ilk kral sen ol!</span>';
    }
    echo '</span>';
    echo '<span class="adc-hks-btn" data-hks-btn>⚔ SEFERE ÇIK</span>';
    echo '</a>';
    ?>
<style>
.adc-hks,.adc-hks *{box-sizing:border-box;}
.adc-hks{display:flex;align-items:center;gap:10px;margin:0 0 12px;padding:8px 10px;border:2px solid #0000ee;box-shadow:3px 3px 0 #0000ee;color:#fff3c4;text-decoration:none !important;font-size:12px;line-height:1.35;
  background:#2a1a5e;background-image:linear-gradient(#3a2a7e 1px,transparent 1px),linear-gradient(90deg,#3a2a7e 1px,transparent 1px);background-size:12px 12px;transition:transform .08s,box-shadow .08s;}
.adc-hks:hover{transform:translate(-1px,-1px);box-shadow:4px 4px 0 #0000ee;}
.adc-hks:active{transform:translate(1px,1px);box-shadow:1px 1px 0 #0000ee;}
.adc-hks-sol{display:flex;align-items:center;flex:none;}
.adc-hks-sol .adc-hk-logo{image-rendering:pixelated;flex:none;}
.adc-hks-ad{display:flex;align-items:baseline;gap:8px;white-space:nowrap;overflow:hidden;}
.adc-hks-ad b{color:#ffcc00;font-size:15px;letter-spacing:2px;text-shadow:2px 2px 0 #0000ee;}
.adc-hks-ad small{color:#c8b6ff;font-size:11px;overflow:hidden;text-overflow:ellipsis;}
.adc-hks-orta{flex:1 1 auto;min-width:0;display:flex;flex-direction:column;gap:1px;}
.adc-hks-satir{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#efeaff;}
.adc-hks-satir b{color:#ffcc00;}
.adc-hks-satir i{display:inline-block;width:10px;height:10px;border:1px solid #fff3c4;margin:0 4px 0 2px;vertical-align:-1px;}
.adc-hks-btn{flex:none;text-align:center;white-space:nowrap;border:2px solid #ffcc00;background:#ffcc00;color:#1a1a2e;font-weight:900;letter-spacing:1px;padding:6px 10px;box-shadow:2px 2px 0 #ff2bd6;font-size:12px;}
.adc-hks-btn small{display:block;font-weight:400;letter-spacing:0;font-size:10px;}
@media (max-width:520px){.adc-hks{gap:8px;padding:7px 8px;font-size:11px;}.adc-hks-sol .adc-hk-logo{width:30px;height:auto;}.adc-hks-ad small{display:none;}.adc-hks-ad b{font-size:13px;letter-spacing:1px;}
  .adc-hks-btn{padding:5px 7px;font-size:11px;letter-spacing:0;}.adc-hks-btn small{font-size:9px;max-width:72px;overflow:hidden;text-overflow:ellipsis;}}
@media (prefers-reduced-motion: reduce){.adc-hks{transition:none;}}
</style>
<script>(function(){
  var s = document.getElementById('adc-hks'); if (!s) return;
  function yerlestir() {
  var u = document.querySelector('.adc-uws'), b = document.getElementById('adc-bss'), m = document.querySelector('.adc-mg-kutu');
  if (u && u.parentNode) u.parentNode.insertBefore(s, u);
  else if (b && b.parentNode) b.parentNode.insertBefore(s, b.nextSibling);
  else if (m && m.parentNode) m.parentNode.insertBefore(s, m.nextSibling);
  }
  // Underworld şeridi bizden sonra yazıldığı için sayfa hazır olunca taşı
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', yerlestir); else yerlestir();
  try {
    var k = JSON.parse(localStorage.getItem('adc_hk_kayit') || 'null'), adlar = <?php echo wp_json_encode( $adlar ); ?>;
    if (k && k.v === 1 && !k.bitti && adlar[k.oy]) s.querySelector('[data-hks-btn]').innerHTML = '▶ DEVAM ET<small>' + adlar[k.oy] + ' · ' + (k.tur + 1) + '. tur</small>';
  } catch (e) {}
})();</script>
<?php
}, 14 );
