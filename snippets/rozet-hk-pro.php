<?php

/**
 * Rozet-HK Pro (yeni, 2026-10-23)
 * HK · History King sıralamasında (tüm zamanlar rekoru) ilk 3'teki üyelere otomatik rozet:
 * madalya renginde (altın / gümüş / bronz) piksel taç ve altında mavi levhada "HK". Sıralama değişince rozet de yeni sahibine geçer.
 * Haftanın Gamer'ında da sayılır ('hk' satırını HK çekirdek snippet'i ekler).
 */
function adc_hk_pro_ilk3() {
    $ilk3 = get_transient( 'adc_hk_pro_ilk3' );
    if ( is_array( $ilk3 ) ) { return $ilk3; }
    global $wpdb;
    $ilk3 = array_map( 'intval', $wpdb->get_col(
        "SELECT user_id FROM {$wpdb->usermeta}
         WHERE meta_key = 'adc_hk_rekor' AND CAST( meta_value AS UNSIGNED ) > 0
         ORDER BY CAST( meta_value AS UNSIGNED ) DESC, user_id ASC LIMIT 3"
    ) );
    set_transient( 'adc_hk_pro_ilk3', $ilk3, 2 * MINUTE_IN_SECONDS );
    return $ilk3;
}

// 16x16 piksel: üç sivri uçlu taç (madalya rengi, pembe/mavi taşlar) + mavi levha üstünde HK
function adc_hk_pro_ikon( $no ) {
    $renk = array(
        1 => array( '#ffcc00', '#c79400' ), // altın
        2 => array( '#d6d6e6', '#8a8aa0' ), // gümüş
        3 => array( '#e09a5a', '#8a5a2b' ), // bronz
    );
    list( $ana, $koyu ) = $renk[ $no ];
    $tac = array( 'x....xx....x', 'xx..xxxx..xx', 'xxxxxxxxxxxx', 'kpkkkbbkkkpk' );
    $hk  = array( 'x.x.x.x', 'x.x.xx.', 'xxx.x..', 'x.x.xx.', 'x.x.x.x' );
    $r = function( $x, $y, $w, $h, $c ) {
        return '<rect x="' . $x . '" y="' . $y . '" width="' . $w . '" height="' . $h . '" fill="' . $c . '"/>';
    };
    $s = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" shape-rendering="crispEdges">';
    $c = array( 'x' => $ana, 'k' => $koyu, 'p' => '#ff2bd6', 'b' => '#0000ee' );
    foreach ( $tac as $y => $satir ) {
        for ( $x = 0; $x < 12; $x++ ) { if ( isset( $c[ $satir[ $x ] ] ) ) { $s .= $r( 2 + $x, 1 + $y, 1, 1, $c[ $satir[ $x ] ] ); } }
    }
    $s .= $r( 1, 6, 14, 9, '#0000ee' ) . $r( 2, 7, 12, 7, '#1a1a8e' ) . $r( 1, 6, 14, 1, $koyu );
    foreach ( $hk as $y => $satir ) {
        for ( $x = 0; $x < 7; $x++ ) { if ( 'x' === $satir[ $x ] ) { $s .= $r( 4 + $x, 8 + $y, 1, 1, $ana ); } }
    }
    for ( $i = 0; $i < $no; $i++ ) { $s .= $r( 12, 8 + $i * 2, 1, 1, '#ffffff' ); } // sıra noktaları
    return $s . '</svg>';
}

add_filter( 'adc_rozet_listesi', function( $liste ) {
    foreach ( adc_hk_pro_ilk3() as $i => $uid ) {
        $no = $i + 1;
        $kullanici = function_exists( 'bp_core_get_username' ) ? bp_core_get_username( $uid ) : '';
        if ( ! $kullanici ) { continue; }
        $liste[ 'hk_pro_' . $no ] = array(
            'ad'        => 'History King · ' . $no . '.',
            'sira'      => 4,
            'tur'       => 'ozel',
            'kullanici' => $kullanici,
            'ikon'      => adc_hk_pro_ikon( $no ),
        );
    }
    return $liste;
});
