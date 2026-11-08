# PixPulse: muzik.js / oyun.js / ekran.js -> snippet PHP dosyaları
import os, sys
D = os.path.dirname(os.path.abspath(__file__))
OUT = sys.argv[1] if len(sys.argv) > 1 else D
def oku(a): return open(os.path.join(D, a), encoding='utf-8').read()
GUARD = "if ( ! function_exists( 'adc_pp_ayar' ) || ! function_exists( 'bp_is_activity_directory' ) || ! bp_is_activity_directory() ) { return; }"
def yaz(ad, bas, prio, js, ek=''):
    s = "<?php\n\n/**\n" + bas + "\n */\nadd_action( 'wp_footer', function() {\n    " + GUARD + "\n" + ek + "    ?>\n<script>\n" + js.strip() + "\n</script>\n<?php\n}, " + str(prio) + " );\n"
    open(os.path.join(OUT, ad), 'w', encoding='utf-8').write(s)
    print(ad, len(s.encode('utf-8')))
yaz('pixpulse-muzik.php', " * PixPulse - müzik (yeni 2026-11-08)\n * Şarkıları tarayıcıda besteler (tohumdan, herkese aynı, telif yok), nota haritasını müzikten çıkarır,\n * sesi takılmasın diye önceden arabelleğe çizer, dokunuşu çalan sesin zamanına çevirir, 3x5 piksel yazı.\n * \"PixPulse\" çekirdek snippet'i açık olmalı. Sıra: müzik (20) → oyun (21) → ekran (22).", 20, oku('muzik.js'))
yaz('pixpulse-oyun.php', " * PixPulse - oyun (yeni 2026-11-08)\n * Oyun alanı: 4 şerit, kalp notaları, uzun notalar, EKG çizgisi, PERFECT / GREAT / GOOD / MISS, kombo, PULSE modu,\n * duraklatma, gecikme ayarı. \"PixPulse\" ve \"PixPulse - müzik\" açık olmalı.", 21, oku('oyun.js'))
EK = """    $ayar = adc_pp_ayar();
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
"""
yaz('pixpulse-ekran.php', " * PixPulse - ekran (yeni 2026-11-08)\n * Akış sayfasında PixPulse penceresi (#pixpulse): şarkı listesi, zorluk, nota hızı, gecikme ayarı, sonuç ekranı, En iyiler.\n * Diğer üç PixPulse snippet'i açık olmalı.", 22, oku('ekran.js'), EK)
