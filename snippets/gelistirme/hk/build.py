# HK snippet'lerini JS kaynaklarından üretir: python3 build.py (çıktılar ../../ yani snippets/ içine)
import os
def wrap(js_file, head, prio):
    js = '\n'.join(l.strip() for l in open(js_file).read().rstrip().split('\n') if l.strip() and not l.strip().startswith('// '))
    assert '<?' not in js and '</script' not in js
    return ("<?php\n\n/**\n" + head + "\n */\nadd_action( 'wp_footer', function() {\n"
            "    if ( ! function_exists( 'adc_hk_sayfa' ) || ! adc_hk_sayfa() ) { return; }\n    ?>\n<script>\n"
            + js + "\n</script>\n<?php\n}, " + str(prio) + " );\n")
cikti = {
 'hk-harita.php': ('harita.js', " * HK - harita (yeni, 2026-10-23)\n * History King'in piksel haritası: 144x68 hücre, 102 il (ad, arazi, gelir, ilk sahibi, kıyı, komşular), deniz yolları.\n * gelistirme/hk/harita.py ile üretilir. Önce \"HK\" çekirdek snippet'i açık olmalı.", 21),
 'hk-motor.php': ('motor.js', " * HK - motor (yeni, 2026-10-23)\n * Oyunun kuralları: savaş (arazi, kale, kış, destek, kuşatma, deniz), ekonomi, ikmal, diplomasi, tehdit/koalisyon,\n * ulusal odaklar, olaylar, yapay zekâ devletler, puan, danışman, günün seferi. Çizimden bağımsız. Gerekenler: HK, HK - harita.", 22),
 'hk-zeka.php': ('zeka.js', " * HK - zekâ (yeni, 2026-10-23)\n * Yapay zekâ devletlerin turu (asker, inşa, saldırı, savaş ilanı), tarihî olaylar (iki seçenekli kararlar, devlete özel olanlar\n * dahil) ve 💡 danışman ipuçları. Gerekenler: HK, HK - harita, HK - motor.", 23),
 'hk-oyun.php': ('oyun.js', " * HK - oyun (yeni, 2026-10-23)\n * Oyun ekranı: piksel harita (yakınlaştır, kaydır, dokun), üst çubuk, il bilgisi ve düğmeleri, hamle penceresi (zafer şansı,\n * neden-sonuç listesi), diplomasi, odak, danışman, günlük, tur raporu, olaylar. Gerekenler: HK, HK - harita, HK - motor, HK - zekâ.", 24),
 'hk-ekran.php': ('ekran.js', " * HK - ekran (yeni, 2026-10-23)\n * Pencere ve menüler: HK logosu, unvan, Devam et / Yeni Sefer / Günün Seferi, devlet seçimi + zorluk, sefer sonu (puan dökümü,\n * sunucuya kayıt, rekor, XP, madalya), En iyiler (podyum), madalyalar, nasıl oynanır, ilk sefer tanıtımı.\n * Mini Games şeridindeki History King'e ya da akıştaki \"Sen de oyna\"ya basınca açılır. Gerekenler: HK, HK - harita, HK - motor, HK - zekâ, HK - oyun.", 25),
}
for ad, (js, head, prio) in cikti.items():
    s = wrap(js, head, prio)
    open(os.path.join('..', '..', ad), 'w').write(s)
    print(ad, len(s.encode()))
