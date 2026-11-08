<?php

/**
 * PixPulse (Mini Games, yeni 2026-11-08) - çekirdek
 * Piksel ritim oyunu: 4 şeritte aşağı inen kalp notaları, çizgiye gelince dokun. Müzikler sitenin kendi bestesi
 * (tarayıcıda tohumdan üretilir, telif yok). Fikir: Tara (@tara-natula) öneri #20315.
 * Bu snippet: şarkı listesi, ayarlar, Mini Games kutucuğu, pencere CSS'i, sıralama, sunucu kontrolü, XP, günün şarkısı.
 * Diğerleri: "PixPulse - müzik" (pixpulse-muzik.php), "PixPulse - oyun" (pixpulse-oyun.php), "PixPulse - ekran" (pixpulse-ekran.php), "Rozet-PixPulse Pro".
 * Kendini oyun duyurularına, Haftanın Gamer'ına, ADÇ Panel üye kontrolüne ve oyun arşivine filtreyle ekler (başka snippet değişmez).
 * Kayıtlar: meta adc_pp_puan (toplam = her şarkının en iyisi), adc_pp_enler, adc_pp_sayac, adc_pp_basla, adc_pp_xp_gun; option adc_pp_gunluk.
 */

function adc_pp_ayar() {
    return array(
        'xp'     => 5,     // günde bir kez: günün şarkısını Normal / Zor'da B (%80 isabet) ve üstüyle bitirene
        'hedef'  => 0.80,
        'carpan' => array( 'kolay' => 0.6, 'normal' => 1, 'zor' => 1.4 ),
    );
}

// Şarkılar. n = nota sayısı (kolay, normal, zor). Müzik kodu değişirse bu sayılar gelistirme/pixpulse/kontrol.js ile yeniden alınmalı.
function adc_pp_sarkilar() {
    $l = array(
        array( 'id' => 'ilk-nabiz',        'ad' => 'İlk Nabız',        'bpm' => 92,  'kok' => 60, 'mod' => 'maj', 'prog' => array( 0, 5, 3, 4 ), 'tohum' => 11, 'enerji' => 0, 'bolum' => 'iABAo',  'yildiz' => array( 1, 1, 2 ), 'n' => array( 79, 116, 125 ) ),
        array( 'id' => 'piksel-kalp',      'ad' => 'Piksel Kalp',      'bpm' => 108, 'kok' => 62, 'mod' => 'maj', 'prog' => array( 0, 4, 5, 3 ), 'tohum' => 23, 'enerji' => 1, 'bolum' => 'iABAo',  'yildiz' => array( 1, 2, 3 ), 'n' => array( 85, 126, 146 ) ),
        array( 'id' => 'gece-monitoru',    'ad' => 'Gece Monitörü',    'bpm' => 100, 'kok' => 57, 'mod' => 'min', 'prog' => array( 0, 5, 2, 6 ), 'tohum' => 37, 'enerji' => 1, 'bolum' => 'iABAo',  'yildiz' => array( 1, 2, 3 ), 'n' => array( 78, 129, 145 ) ),
        array( 'id' => 'neon-damar',       'ad' => 'Neon Damar',       'bpm' => 122, 'kok' => 64, 'mod' => 'min', 'prog' => array( 0, 3, 5, 4 ), 'tohum' => 41, 'enerji' => 2, 'bolum' => 'iABABo', 'yildiz' => array( 2, 3, 4 ), 'n' => array( 118, 181, 227 ) ),
        array( 'id' => '8bit-adrenalin',   'ad' => '8-Bit Adrenalin',  'bpm' => 138, 'kok' => 65, 'mod' => 'maj', 'prog' => array( 0, 3, 4, 4 ), 'tohum' => 53, 'enerji' => 2, 'bolum' => 'iABABo', 'yildiz' => array( 2, 3, 5 ), 'n' => array( 102, 201, 239 ) ),
        array( 'id' => 'glitch-tasikardi', 'ad' => 'Glitch Taşikardi', 'bpm' => 156, 'kok' => 62, 'mod' => 'min', 'prog' => array( 0, 6, 5, 4 ), 'tohum' => 67, 'enerji' => 2, 'bolum' => 'iABABo', 'yildiz' => array( 2, 4, 5 ), 'n' => array( 84, 145, 159 ) ),
    );
    $sonuc = array();
    foreach ( $l as $s ) {
        $olcu = 0;
        foreach ( str_split( $s['bolum'] ) as $b ) { $olcu += ( 'i' === $b || 'o' === $b ) ? 2 : 8; }
        $s['olcu'] = $olcu;
        $s['sure'] = round( ( $olcu * 16 + 4 ) * 15 / $s['bpm'] + 1.2, 1 );
        $sonuc[ $s['id'] ] = $s;
    }
    return $sonuc;
}

// Günün şarkısı: tarihten, herkese aynı
function adc_pp_gunun( $tarih = '' ) {
    $tarih = $tarih ? $tarih : current_time( 'Y-m-d' );
    $id = array_keys( adc_pp_sarkilar() );
    return $id[ abs( crc32( 'pixpulse' . $tarih ) ) % count( $id ) ];
}

// Puan: isabet (PERFECT 1, GREAT 0,7, GOOD 0,35) %90 + en uzun kombo %10, zorluk çarpanıyla. Oyun da aynısını hesaplar.
function adc_pp_puan( $p, $g, $o, $k, $n, $carpan ) {
    if ( $n < 1 ) { return 0; }
    $isabet = ( $p + $g * 0.7 + $o * 0.35 ) / $n;
    return (int) round( ( $isabet * 90000 + ( $k / $n ) * 10000 ) * $carpan );
}

// 16x16 piksel logo: gece mavisi ekran, pembe kalp, içinden geçen yeşil EKG çizgisi
function adc_pp_logo( $boyut = 32 ) {
    $r = function( $x, $y, $w, $h, $c ) { return '<rect x="' . $x . '" y="' . $y . '" width="' . $w . '" height="' . $h . '" fill="' . $c . '"/>'; };
    $s = '<svg width="' . (int) $boyut . '" height="' . (int) $boyut . '" viewBox="0 0 16 16" shape-rendering="crispEdges" aria-hidden="true" style="display:block;flex:none;">'
        . $r( 0, 0, 16, 16, '#0d0b2e' ) . $r( 0, 4, 16, 1, '#17144a' ) . $r( 0, 12, 16, 1, '#17144a' );
    $kalp = array( '.xx...xx.', 'xxxx.xxxx', 'xxxxxxxxx', 'xxxxxxxxx', '.xxxxxxx.', '..xxxxx..', '...xxx...', '....x....' );
    foreach ( $kalp as $y => $satir ) {
        for ( $x = 0; $x < 9; $x++ ) { if ( 'x' === $satir[ $x ] ) { $s .= $r( 4 + $x, 3 + $y, 1, 1, '#ff2bd6' ); } }
    }
    $s .= $r( 5, 4, 1, 1, '#ff9af0' ) . $r( 6, 4, 1, 1, '#ffffff' );
    // EKG
    $s .= $r( 0, 9, 5, 1, '#2bd97a' ) . $r( 5, 8, 1, 1, '#2bd97a' ) . $r( 6, 5, 1, 3, '#2bd97a' ) . $r( 7, 2, 1, 3, '#2bd97a' ) . $r( 7, 1, 1, 1, '#ffffff' )
        . $r( 8, 5, 1, 7, '#2bd97a' ) . $r( 9, 12, 1, 1, '#2bd97a' ) . $r( 10, 9, 1, 3, '#2bd97a' ) . $r( 11, 9, 5, 1, '#2bd97a' );
    return $s . '</svg>';
}

add_filter( 'adc_mini_oyunlar', function( $oyunlar ) {
    $oyunlar[] = array( 'id' => 'pixpulse', 'ad' => 'PixPulse', 'ikon' => adc_pp_logo( 32 ) );
    return $oyunlar;
} );

add_filter( 'adc_oyun_duyuru_oyunlar', function( $o ) {
    $o['pixpulse'] = array( 'ad' => 'PixPulse', 'meta' => 'adc_pp_puan', 'rekor' => false );
    return $o;
} );
add_filter( 'adc_hg_oyunlar', function( $o ) {
    $o['pixpulse'] = array( 'ad' => 'PixPulse', 'gunluk' => 'adc_pp_gunluk', 'alan' => 'skorlar', 'ikon' => 'adc_pp_pro_ikon' );
    return $o;
} );
add_filter( 'adc_uk_oyunlar', function( $o ) {
    $o['pixpulse'] = array( 'ad' => '♥ PixPulse', 'meta' => 'adc_pp_puan', 'birim' => 'toplam puan', 'ek' => array(), 'gunluk' => 'adc_pp_gunluk', 'onbellek' => 'adc_pp_pro_ilk3' );
    return $o;
} );
add_filter( 'adc_oa_oyunlar', function( $o ) {
    $o['pixpulse'] = array( 'ad' => '♥ PixPulse', 'hg' => 'pixpulse', 'fn' => 'adc_pp_ayar', 'meta' => 'adc_pp_puan', 'sayac' => array( 'adc_pp_sayac', '' ), 'ajax' => array( 'adc_pp_basla' ) );
    return $o;
} );

// Pencere görünümü (+ kutucuğun pembe rengi)
add_action( 'wp_head', function() {
    if ( ! function_exists( 'bp_is_activity_directory' ) || ! bp_is_activity_directory() ) { return; }
    ?>
<style>
.adc-mg-oyunlar .adc-mg-oyun[data-adc-oyun="pixpulse"]{--r:#ffe0f6;--g:#ff2bd6;}
.adc-pp{position:fixed;inset:0;z-index:99999;background:rgba(0,0,60,.55);display:none;align-items:center;justify-content:center;padding:10px;box-sizing:border-box;}
.adc-pp *{box-sizing:border-box;}
.adc-pp-panel{background:#fff;border:3px solid #0000ee;box-shadow:4px 4px 0 #0000ee;max-width:100%;max-height:100%;overflow:auto;color:#000;font-size:14px;line-height:1.4;}
.adc-pp-bas{display:flex;align-items:center;gap:8px;background:#0000ee;color:#fff;padding:5px 10px;letter-spacing:1px;}
.adc-pp-bas strong{flex:1;}
.adc-pp-x{background:none;border:0;color:#fff;font-size:18px;line-height:1;cursor:pointer;padding:2px 4px;}
.adc-pp-govde{padding:10px;max-width:100%;}
.adc-pp-ust{display:flex;align-items:center;gap:6px;padding:0 0 8px;}
.adc-pp-rekor{flex:1;font-weight:700;color:#0000ee;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.adc-pp-btn{border:2px solid #0000ee;background:#fff;color:#0000ee;padding:4px 8px;font:inherit;font-size:13px;cursor:pointer;box-shadow:2px 2px 0 #0000ee;white-space:nowrap;}
.adc-pp-btn:hover{transform:translate(-1px,-1px);}
.adc-pp-btn.adc-pp-secili{background:#0000ee;color:#fff;}
.adc-pp-btn.adc-pp-ana{background:#ff2bd6;color:#fff;font-weight:700;font-size:15px;padding:6px 14px;}
.adc-pp-kucuk{padding:2px 9px;font-weight:700;}
.adc-pp-hedef{border:2px dashed #0000ee;background:#ffe0f6;padding:4px 8px;font-size:12px;color:#0000ee;margin-bottom:10px;}
.adc-pp-sekmeler{display:flex;gap:8px;margin-bottom:10px;}
.adc-pp-sekmeler .adc-pp-btn{flex:1;}
.adc-pp-sarkilar{display:flex;flex-direction:column;gap:6px;margin-bottom:10px;}
.adc-pp-sarki{display:flex;align-items:center;gap:8px;width:100%;text-align:left;border:2px solid #0000ee;background:#fff;box-shadow:3px 3px 0 #0000ee;padding:6px 8px;font:inherit;color:#000;cursor:pointer;}
.adc-pp-sarki:hover{transform:translate(-1px,-1px);}
.adc-pp-sarki:active{transform:translate(2px,2px);box-shadow:1px 1px 0 #0000ee;}
.adc-pp-sarki i{font-style:normal;flex:none;width:30px;height:30px;display:flex;align-items:center;justify-content:center;background:#0d0b2e;color:var(--r);border:2px solid #0000ee;font-size:16px;}
.adc-pp-sarki span{flex:1;min-width:0;display:flex;flex-direction:column;}
.adc-pp-sarki b{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.adc-pp-sarki small{color:#555;font-size:11px;}
.adc-pp-sarki em{font-style:normal;font-size:12px;color:#0000ee;font-weight:700;text-align:right;white-space:nowrap;}
.adc-pp-sarki em b{display:block;font-size:15px;color:#ff2bd6;}
.adc-pp-gunun{background:#fff3fb;border-style:dashed;}
.adc-pp-ayarlar{display:flex;align-items:center;gap:6px;flex-wrap:wrap;font-size:13px;margin-bottom:8px;}
.adc-pp-ayarlar > span{color:#0000ee;}
.adc-pp-not{font-size:12px;color:#444;margin:0;border-top:2px dashed #c8b6ff;padding-top:8px;}
.adc-pp-oyun{padding:10px;}
.adc-pp-sahne{position:relative;border:2px solid #0000ee;line-height:0;touch-action:none;user-select:none;-webkit-user-select:none;-webkit-touch-callout:none;-webkit-tap-highlight-color:transparent;margin:0 auto;width:max-content;}
.adc-pp-sahne canvas{display:block;image-rendering:pixelated;image-rendering:crisp-edges;touch-action:none;}
.adc-pp-dur{position:absolute;left:50%;top:6px;transform:translateX(-50%);background:rgba(13,11,46,.7);color:#fff;border:1px solid #5a3cff;font-size:12px;line-height:1;padding:3px 6px;cursor:pointer;}
.adc-pp-ortu{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;text-align:center;line-height:1.4;padding:10px;}
.adc-pp-kutu{background:#fff;border:3px solid #0000ee;box-shadow:4px 4px 0 #0000ee;padding:10px 12px;min-width:76%;display:flex;flex-direction:column;align-items:center;gap:4px;}
.adc-pp-kutu strong{color:#0000ee;letter-spacing:2px;}
.adc-pp-dugmeler{display:flex;gap:8px;flex-wrap:wrap;justify-content:center;margin-top:6px;}
.adc-pp-sonuc{display:flex;flex-direction:column;align-items:center;text-align:center;gap:4px;}
.adc-pp-sarkiadi{font-weight:700;color:#0000ee;}
.adc-pp-harf{font-size:64px;font-weight:900;line-height:1;color:#ff2bd6;text-shadow:4px 4px 0 #0000ee;font-family:monospace;}
.adc-pp-hA{color:#2bd97a;} .adc-pp-hB{color:#4fc3ff;} .adc-pp-hC,.adc-pp-hD{color:#b8b8cc;}
.adc-pp-buyuk{font-size:28px;font-weight:700;color:#0000ee;}
.adc-pp-sayilar{display:flex;gap:8px;flex-wrap:wrap;justify-content:center;font-weight:700;font-size:12px;font-family:monospace;}
.adc-pp-yeni{display:inline-block;background:#ff2bd6;color:#fff;padding:1px 6px;font-size:12px;font-weight:700;animation:adc-pp-yan .5s steps(1) infinite;}
@keyframes adc-pp-yan{50%{opacity:.4;}}
.adc-pp-ayar{display:flex;flex-direction:column;align-items:center;text-align:center;gap:8px;}
.adc-pp-ayar strong{color:#0000ee;letter-spacing:1px;}
.adc-pp-ped{width:150px;height:150px;font-size:70px;line-height:1;background:#0d0b2e;color:#ff2bd6;border:3px solid #0000ee;box-shadow:4px 4px 0 #0000ee;cursor:pointer;touch-action:manipulation;-webkit-tap-highlight-color:transparent;}
.adc-pp-ped.adc-pp-vur{animation:adc-pp-at .18s steps(2);}
@keyframes adc-pp-at{50%{transform:scale(.9);color:#fff;}}
.adc-pp-ayar-bilgi{font-weight:700;color:#0000ee;}
.adc-pp-av{display:block;object-fit:cover;border:2px solid #0000ee;background:#efeaff;}
.adc-pp-av0{border-color:#ffcc00;} .adc-pp-av1{border-color:#b8b8cc;} .adc-pp-av2{border-color:#d08a4a;}
.adc-pp-podyum{display:flex;align-items:flex-end;justify-content:center;gap:6px;margin:0 0 12px;}
.adc-pp-basamak{flex:1;max-width:120px;display:flex;flex-direction:column;align-items:center;text-align:center;min-width:0;}
.adc-pp-tac{height:20px;}
.adc-pp-isim{font-weight:700;font-size:13px;max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-top:4px;}
.adc-pp-deger{font-size:12px;color:#0000ee;font-weight:700;white-space:nowrap;}
.adc-pp-blok{width:100%;margin-top:4px;border:2px solid #0000ee;box-shadow:3px 3px 0 #0000ee;display:flex;align-items:flex-start;justify-content:center;font-size:22px;font-weight:700;color:#fff;padding-top:4px;text-shadow:2px 2px 0 #0000ee;}
.adc-pp-b1 .adc-pp-blok{height:70px;background:#ffcc00;}
.adc-pp-b2 .adc-pp-blok{height:50px;background:#c8c8dc;}
.adc-pp-b3 .adc-pp-blok{height:36px;background:#d08a4a;}
.adc-pp-liste{border:2px solid #0000ee;margin-bottom:12px;}
.adc-pp-satir{display:flex;align-items:center;gap:8px;padding:5px 8px;border-bottom:1px solid #c8b6ff;}
.adc-pp-satir:last-child{border-bottom:0;}
.adc-pp-no{width:22px;font-weight:700;color:#0000ee;flex:none;}
.adc-pp-isim2{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px;}
.adc-pp-isim2 small{color:#777;}
.adc-pp-ben{background:#efeaff;}
.adc-pp-benim{border:2px dashed #0000ee;padding:8px 10px;text-align:center;font-weight:700;color:#0000ee;margin-bottom:12px;background:#efeaff;}
.adc-pp-bos{text-align:center;padding:20px 10px;color:#555;}
@media (prefers-reduced-motion: reduce){.adc-pp-yeni,.adc-pp-ped.adc-pp-vur{animation:none;}}
</style>
<?php
} );

// ===== Sıralama =====
function adc_pp_kisi( $uid ) {
    $u = get_userdata( $uid );
    if ( ! $u ) { return null; }
    $seviye = '';
    if ( function_exists( 'adc_xp_seviye' ) ) {
        $s = adc_xp_seviye( (int) get_user_meta( $uid, 'adc_xp', true ) );
        $seviye = isset( $s['ad'] ) ? $s['ad'] : '';
    }
    return array( 'ad' => $u->display_name, 'resim' => get_avatar_url( $uid, array( 'size' => 64 ) ), 'seviye' => $seviye );
}

function adc_pp_gunluk() {
    $g = get_option( 'adc_pp_gunluk' );
    if ( ! is_array( $g ) || ! isset( $g['tarih'], $g['skorlar'] ) || ! is_array( $g['skorlar'] ) || $g['tarih'] !== current_time( 'Y-m-d' ) ) {
        $g = array( 'tarih' => current_time( 'Y-m-d' ), 'skorlar' => array() );
    }
    return $g;
}

function adc_pp_tablo() {
    global $wpdb;
    $uid = get_current_user_id();
    $genel = array();
    $satirlar = $wpdb->get_results(
        "SELECT user_id, CAST( meta_value AS UNSIGNED ) AS p FROM {$wpdb->usermeta}
         WHERE meta_key = 'adc_pp_puan' AND CAST( meta_value AS UNSIGNED ) > 0
         ORDER BY p DESC, user_id ASC LIMIT 10"
    );
    foreach ( $satirlar as $s ) {
        $k = adc_pp_kisi( (int) $s->user_id );
        if ( $k ) { $k['deger'] = (int) $s->p; $k['ben'] = ( (int) $s->user_id === $uid ); $genel[] = $k; }
    }
    $skorlar = adc_pp_gunluk()['skorlar'];
    arsort( $skorlar );
    $bugun = array();
    foreach ( array_slice( $skorlar, 0, 10, true ) as $id => $p ) {
        $k = adc_pp_kisi( (int) $id );
        if ( $k ) { $k['deger'] = (int) $p; $k['ben'] = ( (int) $id === $uid ); $bugun[] = $k; }
    }
    $ben = null;
    if ( $uid ) {
        $toplam = (int) get_user_meta( $uid, 'adc_pp_puan', true );
        $sira = 0;
        if ( $toplam > 0 ) {
            $sira = 1 + (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = 'adc_pp_puan' AND CAST( meta_value AS UNSIGNED ) > %d", $toplam
            ) );
        }
        $ben = array(
            'toplam'   => $toplam,
            'sira'     => $sira,
            'gun_skor' => isset( $skorlar[ $uid ] ) ? (int) $skorlar[ $uid ] : 0,
            'gun_sira' => isset( $skorlar[ $uid ] ) ? array_search( $uid, array_keys( $skorlar ) ) + 1 : 0,
            'hedef_ok' => get_user_meta( $uid, 'adc_pp_xp_gun', true ) === current_time( 'Y-m-d' ),
        );
    }
    return array( 'genel' => $genel, 'bugun' => $bugun, 'ben' => $ben );
}

// Şarkı başlarken sunucu saati + hangi şarkı tutulur
add_action( 'wp_ajax_adc_pp_basla', function() {
    check_ajax_referer( 'adc_pp', 'n' );
    $sarki = sanitize_key( $_POST['sarki'] ?? '' );
    $zor   = sanitize_key( $_POST['zorluk'] ?? '' );
    if ( ! isset( adc_pp_sarkilar()[ $sarki ] ) || ! isset( adc_pp_ayar()['carpan'][ $zor ] ) ) { wp_send_json_error(); }
    update_user_meta( get_current_user_id(), 'adc_pp_basla', array( 't' => microtime( true ), 's' => $sarki, 'z' => $zor ) );
    wp_send_json_success();
} );

add_action( 'wp_ajax_adc_pp_bitti', function() {
    check_ajax_referer( 'adc_pp', 'n' );
    $uid   = get_current_user_id();
    $ayar  = adc_pp_ayar();
    $liste = adc_pp_sarkilar();
    $sarki = sanitize_key( $_POST['sarki'] ?? '' );
    $zor   = sanitize_key( $_POST['zorluk'] ?? '' );
    $say   = array();
    foreach ( array( 'p', 'g', 'o', 'm', 'k' ) as $a ) { $say[ $a ] = isset( $_POST[ $a ] ) ? absint( $_POST[ $a ] ) : 0; }
    $basla = get_user_meta( $uid, 'adc_pp_basla', true );
    delete_user_meta( $uid, 'adc_pp_basla' );
    $red = array( 'sayildi' => false, 'tablo' => adc_pp_tablo() );
    $zi = array_search( $zor, array( 'kolay', 'normal', 'zor' ), true );
    if ( ! isset( $liste[ $sarki ] ) || false === $zi || ! is_array( $basla ) || $basla['s'] !== $sarki || $basla['z'] !== $zor ) { wp_send_json_success( $red ); }
    $S = $liste[ $sarki ];
    $n = (int) $S['n'][ $zi ];
    // Nota sayısı tutmalı, kombo vurulan notadan büyük olamaz, şarkı gerçekten çalınmış olmalı
    $gecen = microtime( true ) - (float) $basla['t'];
    $vurulan = $say['p'] + $say['g'] + $say['o'];
    if ( $vurulan + $say['m'] !== $n || $say['k'] > $vurulan || $gecen < $S['olcu'] * 60 * 4 / $S['bpm'] * 0.9 || $gecen > 3 * HOUR_IN_SECONDS ) {
        wp_send_json_success( $red );
    }
    $skor = adc_pp_puan( $say['p'], $say['g'], $say['o'], $say['k'], $n, $ayar['carpan'][ $zor ] );

    update_user_meta( $uid, 'adc_pp_sayac', (int) get_user_meta( $uid, 'adc_pp_sayac', true ) + 1 );
    $enler = get_user_meta( $uid, 'adc_pp_enler', true );
    $enler = is_array( $enler ) ? $enler : array();
    $eski_toplam = (int) get_user_meta( $uid, 'adc_pp_puan', true );
    if ( $skor > (int) ( $enler[ $sarki ] ?? 0 ) ) {
        $enler[ $sarki ] = $skor;
        $enler = array_intersect_key( $enler, $liste );
        update_user_meta( $uid, 'adc_pp_enler', $enler );
        update_user_meta( $uid, 'adc_pp_puan', array_sum( $enler ) );
        delete_transient( 'adc_pp_pro_ilk3' );
    }
    $toplam = (int) get_user_meta( $uid, 'adc_pp_puan', true );

    $g = adc_pp_gunluk();
    if ( $skor > 0 && ( ! isset( $g['skorlar'][ $uid ] ) || $skor > $g['skorlar'][ $uid ] ) ) {
        $g['skorlar'][ $uid ] = $skor;
        update_option( 'adc_pp_gunluk', $g, false );
    }

    // Günün şarkısı: Normal / Zor'da %80 isabet, günde bir kez XP
    $xp = 0;
    $bugun = current_time( 'Y-m-d' );
    $isabet = ( $say['p'] + $say['g'] * 0.7 + $say['o'] * 0.35 ) / $n;
    if ( $sarki === adc_pp_gunun( $bugun ) && 'kolay' !== $zor && $isabet >= $ayar['hedef'] && function_exists( 'adc_xp_ver' ) && get_user_meta( $uid, 'adc_pp_xp_gun', true ) !== $bugun ) {
        update_user_meta( $uid, 'adc_pp_xp_gun', $bugun );
        $xp = $ayar['xp'];
        adc_xp_ver( $uid, $xp );
    }
    wp_send_json_success( array( 'sayildi' => true, 'skor' => $skor, 'toplam' => $toplam, 'artis' => $toplam - $eski_toplam, 'enler' => $enler, 'xp' => $xp, 'tablo' => adc_pp_tablo() ) );
} );
