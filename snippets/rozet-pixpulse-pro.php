<?php

/**
 * Rozet-PixPulse Pro (yeni, 2026-11-08)
 * PixPulse tüm zamanlar sıralamasında (toplam puan) ilk 3'teki üyelere otomatik rozet:
 * 1. altın, 2. gümüş, 3. bronz kalp madalya (pembe + EKG yeşili kurdele, köşede mavi kutuda sıra rakamı).
 * Sıralama değişince rozet yeni sahibine geçer. "PixPulse" çekirdek snippet'i açık olmalı.
 */
function adc_pp_pro_ilk3() {
    $ilk3 = get_transient( 'adc_pp_pro_ilk3' );
    if ( is_array( $ilk3 ) ) { return $ilk3; }
    global $wpdb;
    $ilk3 = array_map( 'intval', $wpdb->get_col(
        "SELECT user_id FROM {$wpdb->usermeta}
         WHERE meta_key = 'adc_pp_puan' AND CAST( meta_value AS UNSIGNED ) > 0
         ORDER BY CAST( meta_value AS UNSIGNED ) DESC, user_id ASC LIMIT 3"
    ) );
    set_transient( 'adc_pp_pro_ilk3', $ilk3, 2 * MINUTE_IN_SECONDS );
    return $ilk3;
}

// 16x16 piksel: pembe + yeşil kurdele, kalp biçimli madalya, sağ altta sıra rakamı
function adc_pp_pro_ikon( $no ) {
    $renk = array(
        1 => array( '#ffcc00', '#b88400' ),
        2 => array( '#d6d6e6', '#7c7c94' ),
        3 => array( '#e09a5a', '#8a5a2b' ),
    );
    list( $ana, $koyu ) = $renk[ (int) $no ];
    $r = function( $x, $y, $w, $h, $c ) { return '<rect x="' . $x . '" y="' . $y . '" width="' . $w . '" height="' . $h . '" fill="' . $c . '"/>'; };
    $s  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" shape-rendering="crispEdges">';
    $s .= $r( 4, 0, 3, 4, '#ff2bd6' ) . $r( 9, 0, 3, 4, '#2bd97a' ) . $r( 7, 2, 2, 2, '#0d0b2e' );
    $kalp = array( '.xx...xx.', 'xxxx.xxxx', 'xxxxxxxxx', 'xxxxxxxxx', 'xxxxxxxxx', '.xxxxxxx.', '..xxxxx..', '...xxx...', '....x....' );
    foreach ( $kalp as $y => $satir ) {
        for ( $x = 0; $x < 9; $x++ ) {
            if ( 'x' !== $satir[ $x ] ) { continue; }
            $kenar = ( 0 === $x || 8 === $x || 0 === $y || 'x' !== ( $satir[ $x - 1 ] ?? '.' ) || 'x' !== ( $satir[ $x + 1 ] ?? '.' ) || 'x' !== ( $kalp[ $y + 1 ][ $x ] ?? '.' ) || 'x' !== ( $kalp[ $y - 1 ][ $x ] ?? '.' ) );
            $s .= $r( 3 + $x, 4 + $y, 1, 1, $kenar ? $koyu : $ana );
        }
    }
    $s .= $r( 5, 6, 1, 1, '#ffffff' );
    // köşede sıra rakamı
    $s .= $r( 11, 9, 5, 7, '#0000ee' );
    $rakam = array(
        1 => array( '.x.', 'xx.', '.x.', '.x.', 'xxx' ),
        2 => array( 'xx.', '..x', '.x.', 'x..', 'xxx' ),
        3 => array( 'xx.', '..x', '.x.', '..x', 'xx.' ),
    );
    foreach ( $rakam[ (int) $no ] as $y => $satir ) {
        for ( $x = 0; $x < 3; $x++ ) { if ( 'x' === $satir[ $x ] ) { $s .= $r( 12 + $x, 10 + $y, 1, 1, '#ffffff' ); } }
    }
    return $s . '</svg>';
}

add_filter( 'adc_rozet_listesi', function( $liste ) {
    if ( ! function_exists( 'adc_pp_ayar' ) ) { return $liste; }
    foreach ( adc_pp_pro_ilk3() as $i => $uid ) {
        $no = $i + 1;
        $kullanici = function_exists( 'bp_core_get_username' ) ? bp_core_get_username( $uid ) : '';
        if ( ! $kullanici ) { continue; }
        $liste[ 'pixpulse_pro_' . $no ] = array(
            'ad'        => 'PixPulse Pro · ' . $no . '.',
            'sira'      => 3,
            'tur'       => 'ozel',
            'kullanici' => $kullanici,
            'ikon'      => adc_pp_pro_ikon( $no ),
        );
    }
    return $liste;
} );
