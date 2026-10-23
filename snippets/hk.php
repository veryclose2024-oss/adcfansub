<?php

/**
 * HK · History King (8. oyun, kendi şeridi, yeni 2026-10-23) · çekirdek
 * Hearts of Iron / Europa tarzı mini strateji: tarihin 10 büyük devletinden birini seç (Osmanlı, Roma, Moğol, Britanya, Fransa,
 * Rusya, Çin, Japonya, İspanya, Safevi), 102 illik piksel haritada 30 turda (7,5 yıl) topraklarını büyüt. Savaş (arazi, kale, kış,
 * çok yönlü saldırı, kuşatma), ekonomi (gelir, maaş, iflas), ikmal, diplomasi (savaş ilanı, barış, tehdit → koalisyon),
 * ulusal odaklar, tarihî olaylar. Yapay zekâ devletler de birbiriyle savaşır.
 * Bu snippet: devletler, unvanlar, madalyalar, HK piksel logo, şerit ikonu, sunucu puan kontrolü, sıralama, XP, pencere verisi.
 * Harita "HK - harita", kurallar "HK - motor", oyun ekranı "HK - oyun", menüler "HK - ekran", görünüm "HK - stil" snippet'lerinde.
 * Kayıt (kullanıcı meta): adc_hk_rekor (sıralama), adc_hk_rekor_ulus, adc_hk_sayac, adc_hk_madalya, adc_hk_xp_gun, adc_hk_hedef_gun,
 * adc_hk_basla (aktif seferin tohumu + başlama zamanı). Option: adc_hk_gunluk (bugünün skorları).
 */

// ===== AYARLAR =====
function adc_hk_ayar() {
    return array(
        'xp'      => 5,  // günde bir kez, en az 'xp_puan' puanla biten sefere
        'xp_puan' => 60,
        'gun_xp'  => 5,  // günün seferinin hedefini alana günde bir kez
        'il'      => 102, // haritadaki il sayısı (HK - harita ile aynı)
    );
}

// Devletler: sıra HK - motor'daki ile aynı olmalı. zorluk: 0 kolay, 1 orta, 2 zor başlangıç
function adc_hk_uluslar() {
    return array(
        array( 'id' => 'osmanli', 'ad' => 'Osmanlı', 'lider' => 'Fatih Sultan Mehmed', 'renk' => '#d7263d', 'zorluk' => 0,
            'g1' => '💣 Şahi Topları: düşman kaleleri yarı etkili', 'g2' => '🤝 İstimâlet: fethedilen il 1 turda uyum sağlar',
            'odak' => 'Nizâm-ı Âlem', 'odak_acik' => '+2 altın her tur, moral +15' ),
        array( 'id' => 'roma', 'ad' => 'Roma', 'lider' => 'Augustus', 'renk' => '#7b2cbf', 'zorluk' => 1,
            'g1' => '🛡 Lejyon Disiplini: savunma +%15, yenilince %40 asker döner', 'g2' => '🏛 Roma Hukuku: fetihler yarı tehdit yaratır',
            'odak' => 'Pax Romana', 'odak_acik' => 'Tehdit sıfırlanır, moral +15, savunma +%10' ),
        array( 'id' => 'mogol', 'ad' => 'Moğol', 'lider' => 'Cengiz Han', 'renk' => '#1f9e89', 'zorluk' => 2,
            'g1' => '🐎 Bozkır Atlıları: ova, bozkır, çölde saldırı +%25', 'g2' => '📯 Yam Teşkilatı: ikmal hiç kopmaz, atlılar kendi topraklarında tekrar yürür',
            'odak' => 'Büyük Kurultay', 'odak_acik' => 'Moral +20, saldırı +%10' ),
        array( 'id' => 'britanya', 'ad' => 'Britanya', 'lider' => 'Kraliçe Victoria', 'renk' => '#e05780', 'zorluk' => 1,
            'g1' => '⚓ Deniz Hâkimiyeti: denizden çıkarma cezası yok', 'g2' => '💼 Ticaret Şirketleri: kıyı illeri +1 altın',
            'odak' => 'Kraliyet Donanması', 'odak_acik' => '+3 altın her tur, denizden saldırı +%20' ),
        array( 'id' => 'fransa', 'ad' => 'Fransa', 'lider' => 'Napolyon Bonapart', 'renk' => '#2e6fdb', 'zorluk' => 2,
            'g1' => '🎖 Büyük Ordu: 10+ askerle saldırıda +%20', 'g2' => '📣 Kitlesel Seferberlik: asker 1 altın ucuz',
            'odak' => 'Medeni Kanun', 'odak_acik' => '+3 altın her tur, moral +10' ),
        array( 'id' => 'rusya', 'ad' => 'Rusya', 'lider' => 'I. Petro', 'renk' => '#2b9348', 'zorluk' => 0,
            'g1' => '❄ General Kış: kışın sana saldıran -%30, sınırdaki düşman erir', 'g2' => '👥 Geniş Topraklar: her ilde +2 asker toplanır',
            'odak' => 'Petersburg Penceresi', 'odak_acik' => '+3 altın her tur, asker 1 altın ucuz' ),
        array( 'id' => 'cin', 'ad' => 'Çin', 'lider' => 'Kangxi İmparatoru', 'renk' => '#f2c14e', 'zorluk' => 1,
            'g1' => '🧱 Büyük Sur: kaleler yarı fiyat ve çok daha güçlü', 'g2' => '🌾 Pirinç Tarlaları: gelir +%20',
            'odak' => 'Hazine Filosu', 'odak_acik' => '+4 altın her tur' ),
        array( 'id' => 'japonya', 'ad' => 'Japonya', 'lider' => 'Tokugawa Ieyasu', 'renk' => '#f3efe6', 'zorluk' => 1,
            'g1' => '⛩ Bushido: savunma +%20, savunurken moral düşmez', 'g2' => '🌊 Ada Ülkesi: denizden çıkarma cezası yok',
            'odak' => 'Edo Barışı', 'odak_acik' => 'Moral +20, savunma +%10' ),
        array( 'id' => 'ispanya', 'ad' => 'İspanya', 'lider' => 'Kraliçe I. Isabel', 'renk' => '#e76f51', 'zorluk' => 0,
            'g1' => '💰 Yeni Dünya Altını: her tur +4 altın', 'g2' => '⚔ Tercio Alayları: saldırı +%10',
            'odak' => 'Altın Çağ', 'odak_acik' => '+3 altın her tur, moral +10' ),
        array( 'id' => 'safevi', 'ad' => 'Safevi', 'lider' => 'Şah Abbas', 'renk' => '#06b6d4', 'zorluk' => 1,
            'g1' => '🐫 İpek Yolu: barıştaki her komşu devlet +1 altın', 'g2' => '🎨 İsfahan Ustaları: binalar 3 altın ucuz, saldırı/savunma +%5',
            'odak' => 'Nısf-ı Cihan', 'odak_acik' => '+4 altın her tur' ),
    );
}

// Unvanlar: tüm zamanlar rekoruna göre
function adc_hk_unvanlar() {
    return array(
        array( 'ad' => 'Er', 'esik' => 0 ), array( 'ad' => 'Onbaşı', 'esik' => 40 ), array( 'ad' => 'Çavuş', 'esik' => 80 ),
        array( 'ad' => 'Yüzbaşı', 'esik' => 130 ), array( 'ad' => 'Binbaşı', 'esik' => 200 ), array( 'ad' => 'Albay', 'esik' => 300 ),
        array( 'ad' => 'General', 'esik' => 450 ), array( 'ad' => 'Mareşal', 'esik' => 650 ), array( 'ad' => 'Tarihin Kralı', 'esik' => 900 ),
    );
}
function adc_hk_unvan( $puan ) {
    $ad = 'Er';
    foreach ( adc_hk_unvanlar() as $u ) { if ( (int) $puan >= $u['esik'] ) { $ad = $u['ad']; } }
    return $ad;
}

function adc_hk_madalyalar() {
    return array(
        'zafer'    => array( 'ad' => 'İlk Zafer',       'acik' => 'Bir muharebe kazan',                      'ikon' => '⚔' ),
        'deha'     => array( 'ad' => 'Az ile Çok',      'acik' => 'Sayıca azken bir muharebe kazan',         'ikon' => '🧠' ),
        'baskent'  => array( 'ad' => 'Başkent Fatihi',  'acik' => 'Bir devletin başkentini alıp tut',        'ikon' => '🏛' ),
        'kale'     => array( 'ad' => 'Kale Kıran',      'acik' => 'Kaleli bir ili fethet',                   'ikon' => '♜' ),
        'kis'      => array( 'ad' => 'Kış Seferi',      'acik' => 'Kışın bir devletten il al',               'ikon' => '❄' ),
        'baris'    => array( 'ad' => 'Masada Kazanan',  'acik' => 'Bir savaşı barışla kazan',                'ikon' => '🕊' ),
        'cephe'    => array( 'ad' => 'Üç Cephe',        'acik' => 'Aynı anda 3 devletle savaş, ayakta kal',  'ikon' => '🔥' ),
        'hazine'   => array( 'ad' => 'Dolu Hazine',     'acik' => 'Hazinende 200 altın biriksin',            'ikon' => '💰' ),
        'kusursuz' => array( 'ad' => 'Kusursuz Sefer',  'acik' => 'Hiç yenilmeden 10 muharebe kazan',        'ikon' => '✨' ),
        'imparator'=> array( 'ad' => 'İmparatorluk',    'acik' => 'Bir seferde 25 ile ulaş',                 'ikon' => '🗺' ),
        'kral'     => array( 'ad' => 'Tarihin Kralı',   'acik' => 'Haritanın yarısını al',                   'ikon' => '👑' ),
        'dahi'     => array( 'ad' => 'Dâhi',            'acik' => 'Dâhi zorlukta 300 puan yap',              'ikon' => '🎓' ),
        'gunluk'   => array( 'ad' => 'Günün Komutanı',  'acik' => 'Günün seferinin hedefini al',             'ikon' => '📅' ),
        'dunya'    => array( 'ad' => 'Dünya Turu',      'acik' => '10 devletin hepsiyle bir sefer bitir',    'ikon' => '🌍' ),
    );
}

// ===== HK piksel logo: taç + HK harfleri (mavi çerçeve, pembe gölge) =====
function adc_hk_logo( $boy = 64, $stil = '' ) {
    $tac = array( 'Y.....YY.....Y', 'YY...YYYY...YY', 'YYY.YYYYYY.YYY', 'YYYYYYYYYYYYYY', 'DPDDDBDDBDDDPD', 'DDDDDDDDDDDDDD' );
    $h = array( 'xx...xx', 'xx...xx', 'xx...xx', 'xxxxxxx', 'xxxxxxx', 'xx...xx', 'xx...xx', 'xx...xx', 'xx...xx' );
    $k = array( 'xx...xx', 'xx..xx.', 'xx.xx..', 'xxxx...', 'xxx....', 'xxxx...', 'xx.xx..', 'xx..xx.', 'xx...xx' );
    $harf = array();
    foreach ( array( array( $h, 2 ), array( $k, 12 ) ) as $hk ) {
        foreach ( $hk[0] as $y => $s ) {
            for ( $x = 0; $x < 7; $x++ ) { if ( 'x' === $s[ $x ] ) { $harf[ ( $hk[1] + $x ) . ',' . ( 8 + $y ) ] = 1; } }
        }
    }
    $cerceve = array();
    foreach ( $harf as $p => $v ) {
        list( $x, $y ) = explode( ',', $p );
        for ( $dy = -1; $dy <= 1; $dy++ ) { for ( $dx = -1; $dx <= 1; $dx++ ) { $cerceve[ ( $x + $dx ) . ',' . ( $y + $dy ) ] = 1; } }
    }
    $r = function( $x, $y, $c ) { return '<rect x="' . $x . '" y="' . $y . '" width="1" height="1" fill="' . $c . '"/>'; };
    $s = '';
    foreach ( $cerceve as $p => $v ) { list( $x, $y ) = explode( ',', $p ); $s .= $r( $x + 2, $y + 2, '#ff2bd6' ); }
    foreach ( $cerceve as $p => $v ) { list( $x, $y ) = explode( ',', $p ); $s .= $r( $x, $y, '#0000ee' ); }
    foreach ( $harf as $p => $v ) { list( $x, $y ) = explode( ',', $p ); $s .= $r( $x, $y, $y < 12 ? '#ffffff' : '#fff3c4' ); }
    $renk = array( 'Y' => '#ffcc00', 'D' => '#c79400', 'P' => '#ff2bd6', 'B' => '#0000ee' );
    foreach ( $tac as $y => $satir ) {
        for ( $x = 0; $x < 14; $x++ ) { $c = $satir[ $x ]; if ( isset( $renk[ $c ] ) ) { $s .= $r( 4 + $x, $y, $renk[ $c ] ); } }
    }
    $s .= $r( 5, 3, '#fff3a0' ) . $r( 11, 2, '#fff3a0' );
    return '<svg class="adc-hk-logo" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 20" width="' . round( $boy * 1.2 ) . '" height="' . $boy . '" shape-rendering="crispEdges" role="img" aria-label="HK History King"' . ( $stil ? ' style="' . $stil . '"' : '' ) . '>' . $s . '</svg>';
}

// Günün seferinin devleti (HK - motor'daki gununSeferi ile aynı hesap)
function adc_hk_gunun_ulusu( $tarih ) {
    $h = 2166136261;
    $s = 'hk' . $tarih;
    for ( $i = 0, $n = strlen( $s ); $i < $n; $i++ ) {
        $h = ( ( $h ^ ord( $s[ $i ] ) ) * 16777619 ) & 0xFFFFFFFF;
    }
    if ( $h >= 2147483648 ) { $h -= 4294967296; }
    $u = count( adc_hk_uluslar() );
    return ( ( $h % $u ) + $u ) % $u;
}

// Mini Games'te kutucuk yok: HK'nin kendi şeridi var ("HK - şerit", Underworld'ün üstünde)

// Oyun duyuruları + bildirimler + Haftanın Gamer'ı (o snippet'ler açıksa kendiliğinden)
add_filter( 'adc_oyun_duyuru_oyunlar', function( $o ) {
    $o['hk'] = array( 'ad' => 'History King', 'meta' => 'adc_hk_rekor', 'rekor' => true );
    return $o;
} );
add_filter( 'adc_hg_oyunlar', function( $o ) {
    $o['hk'] = array( 'ad' => 'History King', 'gunluk' => 'adc_hk_gunluk', 'alan' => 'skorlar', 'ikon' => 'adc_hk_pro_ikon' );
    return $o;
} );

// ===== Puan (HK - motor'daki puanDokum ile aynı) =====
function adc_hk_puan( $r ) {
    $carpan = array( 0.5, 1, 1.5 );
    $ham = $r['il'] * 10 + $r['baskent'] * 40 + min( $r['zafer'], 150 ) * 2 + min( $r['deha'], 60 ) * 8 + min( $r['baris'], 9 ) * 20
        + (int) floor( min( $r['altin'], 300 ) / 10 ) - (int) floor( $r['kayip'] / 4 );
    if ( ! $r['kendi'] ) { $ham -= 100; }
    if ( $r['kral'] ) { $ham += ( 30 - $r['tur'] ) * 15 + 100; }
    if ( $r['hedef'] ) { $ham += 100; }
    return (int) round( max( 0, $ham ) * $carpan[ $r['zorluk'] ] );
}

// ===== Sıralama =====
function adc_hk_kisi( $uid ) {
    $u = get_userdata( $uid );
    if ( ! $u ) { return null; }
    return array( 'ad' => $u->display_name, 'resim' => get_avatar_url( $uid, array( 'size' => 64 ) ),
        'unvan' => adc_hk_unvan( get_user_meta( $uid, 'adc_hk_rekor', true ) ), 'ulus' => (int) get_user_meta( $uid, 'adc_hk_rekor_ulus', true ) );
}
function adc_hk_gunluk() {
    $g = get_option( 'adc_hk_gunluk' );
    if ( ! is_array( $g ) || ! isset( $g['tarih'], $g['skorlar'] ) || ! is_array( $g['skorlar'] ) || $g['tarih'] !== current_time( 'Y-m-d' ) ) {
        $g = array( 'tarih' => current_time( 'Y-m-d' ), 'skorlar' => array(), 'ulus' => array() );
    }
    return $g;
}
function adc_hk_tablo() {
    global $wpdb;
    $uid = get_current_user_id();
    $genel = array();
    $satirlar = $wpdb->get_results(
        "SELECT user_id, CAST( meta_value AS UNSIGNED ) AS p FROM {$wpdb->usermeta}
         WHERE meta_key = 'adc_hk_rekor' AND CAST( meta_value AS UNSIGNED ) > 0
         ORDER BY p DESC, user_id ASC LIMIT 10"
    );
    foreach ( $satirlar as $s ) {
        $k = adc_hk_kisi( (int) $s->user_id );
        if ( $k ) { $k['deger'] = (int) $s->p; $k['ben'] = ( (int) $s->user_id === $uid ); $genel[] = $k; }
    }
    $g = adc_hk_gunluk();
    $skorlar = $g['skorlar'];
    arsort( $skorlar );
    $bugun = array();
    foreach ( array_slice( $skorlar, 0, 10, true ) as $id => $p ) {
        $k = adc_hk_kisi( (int) $id );
        if ( $k ) {
            $k['deger'] = (int) $p; $k['ben'] = ( (int) $id === $uid );
            if ( isset( $g['ulus'][ $id ] ) ) { $k['ulus'] = (int) $g['ulus'][ $id ]; }
            $bugun[] = $k;
        }
    }
    $ben = null;
    if ( $uid ) {
        $rekor = (int) get_user_meta( $uid, 'adc_hk_rekor', true );
        $sira  = 0;
        if ( $rekor > 0 ) {
            $sira = 1 + (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = 'adc_hk_rekor' AND CAST( meta_value AS UNSIGNED ) > %d", $rekor
            ) );
        }
        $gun_sira = isset( $skorlar[ $uid ] ) ? array_search( $uid, array_keys( $skorlar ) ) + 1 : 0;
        $ben = array( 'rekor' => $rekor, 'sira' => $sira, 'gun_skor' => isset( $skorlar[ $uid ] ) ? (int) $skorlar[ $uid ] : 0, 'gun_sira' => (int) $gun_sira );
    }
    return array( 'genel' => $genel, 'bugun' => $bugun, 'ben' => $ben );
}
function adc_hk_benim( $uid ) {
    $b = $uid ? get_user_meta( $uid, 'adc_hk_madalya', true ) : array();
    return is_array( $b ) ? array_values( array_intersect( $b, array_keys( adc_hk_madalyalar() ) ) ) : array();
}

// ===== Sefer başı / sonu (sunucu kontrolü) =====
add_action( 'wp_ajax_adc_hk_basla', function() {
    check_ajax_referer( 'adc_hk', 'n' );
    $uid    = get_current_user_id();
    $tarih  = current_time( 'Y-m-d' );
    $gunluk = ! empty( $_POST['gunluk'] );
    $ulus   = $gunluk ? adc_hk_gunun_ulusu( $tarih ) : min( count( adc_hk_uluslar() ) - 1, absint( $_POST['ulus'] ?? 0 ) );
    $zorluk = $gunluk ? 1 : min( 2, absint( $_POST['zorluk'] ?? 1 ) );
    $seed   = $gunluk ? 'gun' . $tarih : 'u' . $uid . '-' . wp_generate_password( 10, false, false );
    update_user_meta( $uid, 'adc_hk_basla', array( 't' => time(), 'seed' => $seed, 'ulus' => $ulus, 'zorluk' => $zorluk, 'gunluk' => $gunluk ? 1 : 0, 'tarih' => $tarih ) );
    wp_send_json_success( array( 'seed' => $seed, 'ulus' => $ulus, 'zorluk' => $zorluk ) );
} );

add_action( 'wp_ajax_adc_hk_bitti', function() {
    check_ajax_referer( 'adc_hk', 'n' );
    $uid  = get_current_user_id();
    $ayar = adc_hk_ayar();
    $b    = get_user_meta( $uid, 'adc_hk_basla', true );
    $seed = isset( $_POST['seed'] ) ? sanitize_text_field( wp_unslash( $_POST['seed'] ) ) : '';
    $hayir = function( $neden ) { wp_send_json_success( array( 'sayildi' => false, 'neden' => $neden ) ); };
    if ( ! is_array( $b ) || $b['seed'] !== $seed ) { $hayir( 'bu sefer başka bir cihazda ya da çok önce başlamış' ); }
    $r = array();
    foreach ( array( 'il', 'baskent', 'zafer', 'deha', 'kayip', 'baris', 'altin', 'kendi', 'kral', 'tur', 'hedef', 'kaleKir', 'kisZafer', 'cephe', 'maxAltin', 'yenilgi' ) as $k ) {
        $r[ $k ] = isset( $_POST[ $k ] ) ? absint( $_POST[ $k ] ) : 0;
    }
    $r['zorluk'] = (int) $b['zorluk'];
    $r['hedef']  = $b['gunluk'] && $r['hedef'] ? 1 : 0;
    $gecen = time() - (int) $b['t'];
    $olur = $r['tur'] >= 1 && $r['tur'] <= 30 && $gecen >= max( 20, $r['tur'] * 3 ) && $gecen <= 2 * DAY_IN_SECONDS
        && $r['il'] <= $ayar['il'] && $r['baskent'] <= 9 && $r['baskent'] <= $r['il'] && $r['zafer'] <= 600 && $r['deha'] <= $r['zafer']
        && $r['baris'] <= 30 && $r['kendi'] <= 1 && $r['kral'] <= 1 && ( ! $r['kral'] || $r['il'] >= ceil( $ayar['il'] / 2 ) )
        && ( $r['kral'] || $r['tur'] >= 30 || 0 === $r['il'] ) && $r['altin'] <= 20000 && $r['kaleKir'] <= $r['zafer'] && $r['kisZafer'] <= $r['zafer'];
    if ( ! $olur ) { $hayir( 'süre ya da sayılar tutmadı' ); }
    delete_user_meta( $uid, 'adc_hk_basla' );

    $puan = adc_hk_puan( $r );
    $eski = (int) get_user_meta( $uid, 'adc_hk_rekor', true );
    $rekor_mu = false;
    if ( $puan > $eski ) {
        update_user_meta( $uid, 'adc_hk_rekor_ulus', (int) $b['ulus'] );
        update_user_meta( $uid, 'adc_hk_rekor', $puan );
        delete_transient( 'adc_hk_pro_ilk3' );
        $rekor_mu = true;
    }
    $g = adc_hk_gunluk();
    if ( $puan > 0 && ( ! isset( $g['skorlar'][ $uid ] ) || $puan > $g['skorlar'][ $uid ] ) ) {
        $g['skorlar'][ $uid ] = $puan;
        $g['ulus'][ $uid ] = (int) $b['ulus'];
        update_option( 'adc_hk_gunluk', $g, false );
    }

    $bugun = current_time( 'Y-m-d' );
    $say = get_user_meta( $uid, 'adc_hk_sayac', true );
    $say = wp_parse_args( is_array( $say ) ? $say : array(), array( 'sefer' => 0, 'uluslar' => 0, 'gun' => 0, 'son_gun' => '' ) );
    $say['sefer']++;
    $say['uluslar'] = (int) $say['uluslar'] | ( 1 << (int) $b['ulus'] );
    if ( $say['son_gun'] !== $bugun ) { $say['gun']++; $say['son_gun'] = $bugun; }
    update_user_meta( $uid, 'adc_hk_sayac', $say );

    $hayatta = $r['il'] > 0;
    $kosul = array(
        'zafer'     => $r['zafer'] >= 1,
        'deha'      => $r['deha'] >= 1,
        'baskent'   => $r['baskent'] >= 1,
        'kale'      => $r['kaleKir'] >= 1,
        'kis'       => $r['kisZafer'] >= 1,
        'baris'     => $r['baris'] >= 1,
        'cephe'     => $r['cephe'] >= 3 && $hayatta,
        'hazine'    => $r['maxAltin'] >= 200,
        'kusursuz'  => 0 === $r['yenilgi'] && $r['zafer'] >= 10,
        'imparator' => $r['il'] >= 25,
        'kral'      => 1 === $r['kral'],
        'dahi'      => 2 === $r['zorluk'] && $puan >= 300,
        'gunluk'    => 1 === $r['hedef'],
        'dunya'     => 1023 === ( $say['uluslar'] & 1023 ),
    );
    $benim = adc_hk_benim( $uid );
    $yeni  = array();
    foreach ( $kosul as $id => $tamam ) {
        if ( $tamam && ! in_array( $id, $benim, true ) ) { $benim[] = $id; $yeni[] = $id; }
    }
    if ( $yeni ) { update_user_meta( $uid, 'adc_hk_madalya', $benim ); }

    $xp = 0;
    if ( function_exists( 'adc_xp_ver' ) ) {
        if ( $puan >= $ayar['xp_puan'] && get_user_meta( $uid, 'adc_hk_xp_gun', true ) !== $bugun ) {
            update_user_meta( $uid, 'adc_hk_xp_gun', $bugun );
            $xp += $ayar['xp'];
        }
        if ( $r['hedef'] && get_user_meta( $uid, 'adc_hk_hedef_gun', true ) !== $bugun ) {
            update_user_meta( $uid, 'adc_hk_hedef_gun', $bugun );
            $xp += $ayar['gun_xp'];
        }
        if ( $xp ) { adc_xp_ver( $uid, $xp ); }
    }
    $rekor = (int) get_user_meta( $uid, 'adc_hk_rekor', true );
    wp_send_json_success( array(
        'sayildi' => true, 'puan' => $puan, 'rekor' => $rekor_mu, 'rekor_deger' => $rekor, 'xp' => $xp, 'yeni' => $yeni, 'benim' => $benim,
        'unvan' => adc_hk_unvan( $rekor ), 'unvan_atladi' => adc_hk_unvan( $rekor ) !== adc_hk_unvan( $eski ),
        'gunluk_bitti' => get_user_meta( $uid, 'adc_hk_hedef_gun', true ) === $bugun, 'tablo' => adc_hk_tablo(),
    ) );
} );

// ===== Pencere verisi (akış sayfasında) =====
function adc_hk_sayfa() {
    return function_exists( 'bp_is_activity_directory' ) && bp_is_activity_directory();
}
add_action( 'wp_footer', function() {
    if ( ! adc_hk_sayfa() ) { return; }
    $ayar = adc_hk_ayar();
    $uid  = get_current_user_id();
    $veri = array(
        'giris'        => (bool) $uid,
        'ajax'         => admin_url( 'admin-ajax.php' ),
        'nonce'        => $uid ? wp_create_nonce( 'adc_hk' ) : '',
        'bugun'        => current_time( 'Y-m-d' ),
        'uluslar'      => adc_hk_uluslar(),
        'unvanlar'     => adc_hk_unvanlar(),
        'madalyalar'   => adc_hk_madalyalar(),
        'benim'        => adc_hk_benim( $uid ),
        'rekor'        => $uid ? (int) get_user_meta( $uid, 'adc_hk_rekor', true ) : 0,
        'gunluk_bitti' => $uid && get_user_meta( $uid, 'adc_hk_hedef_gun', true ) === current_time( 'Y-m-d' ),
        'xp'           => function_exists( 'adc_xp_ver' ) ? $ayar['gun_xp'] : 0,
        'tablo'        => adc_hk_tablo(),
        'logo'         => adc_hk_logo( 70 ),
        'logo_kucuk'   => adc_hk_logo( 16, 'vertical-align:-3px;' ),
        'giris_link'   => wp_login_url( home_url( '/' ) ),
    );
    echo '<script>window.adcHK = ' . wp_json_encode( $veri ) . ';</script>';
}, 20 );
