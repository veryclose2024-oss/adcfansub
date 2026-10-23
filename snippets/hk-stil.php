<?php

/**
 * HK - stil (yeni, 2026-10-23)
 * History King penceresinin görünümü: menü, devlet kartları, oyun ekranı (harita, üst çubuk, il bilgisi, alt düğmeler),
 * hamle / olay / diplomasi pencereleri, En iyiler podyumu, madalyalar. Önce "HK" çekirdek snippet'i açık olmalı.
 */
add_action( 'wp_footer', function() {
    if ( ! function_exists( 'adc_hk_sayfa' ) || ! adc_hk_sayfa() ) { return; }
    ?>
<style>
.adc-mg-oyunlar .adc-mg-oyun[data-adc-oyun="hk"]{--r:#fff3c4;--g:#c79400;}
.adc-hk{position:fixed;inset:0;z-index:99999;background:rgba(0,0,60,.55);display:none;align-items:center;justify-content:center;padding:10px;box-sizing:border-box;}
.adc-hk *{box-sizing:border-box;}
.adc-hk-panel{position:relative;background:#fff;border:3px solid #0000ee;box-shadow:4px 4px 0 #0000ee;width:min(480px,100%);max-height:100%;overflow:auto;color:#1a1a2e;font-size:14px;line-height:1.4;font-family:inherit;display:flex;flex-direction:column;}
.adc-hk-panel.adc-hk-tam{width:min(1000px,100%);height:100%;overflow:hidden;}
.adc-hk-bas{display:flex;align-items:center;justify-content:space-between;background:#0000ee;color:#fff;padding:5px 8px;letter-spacing:1px;flex:none;font-weight:700;}
.adc-hk-baslik{display:flex;align-items:center;gap:6px;font-size:13px;}
.adc-hk-x{background:none;border:0;color:#fff;font-size:18px;line-height:1;cursor:pointer;padding:2px 6px;}
.adc-hk-govde{padding:10px;flex:1;min-height:0;overflow:auto;}
.adc-hk-tam .adc-hk-govde{display:flex;flex-direction:column;padding:6px;gap:6px;overflow:hidden;}
.adc-hk-btn{border:2px solid #0000ee;background:#fff;color:#0000ee;padding:5px 9px;font:inherit;font-size:13px;font-weight:700;cursor:pointer;box-shadow:2px 2px 0 #0000ee;touch-action:manipulation;}
.adc-hk-btn:hover:not(:disabled){transform:translate(-1px,-1px);}
.adc-hk-btn:active:not(:disabled){transform:translate(1px,1px);box-shadow:1px 1px 0 #0000ee;}
.adc-hk-btn:disabled{opacity:.45;cursor:default;box-shadow:none;}
.adc-hk-btn small{font-weight:400;}
.adc-hk-secili{background:#0000ee;color:#fff;}
.adc-hk-buyuk{display:block;width:100%;font-size:17px;padding:9px 12px;margin:8px 0;background:#ffcc00;color:#1a1a2e;letter-spacing:1px;}
.adc-hk-buyuk small{display:block;font-size:12px;letter-spacing:0;}
.adc-hk-ikinci{background:#fff;color:#0000ee;font-size:15px;}
.adc-hk-vitrin{text-align:center;border:2px solid #0000ee;padding:12px 8px 8px;background:#2a1a5e;background-image:linear-gradient(#3a2a7e 1px,transparent 1px),linear-gradient(90deg,#3a2a7e 1px,transparent 1px);background-size:14px 14px;color:#fff3c4;}
.adc-hk-vitrin p{margin:6px 0 0;font-size:13px;}
.adc-hk-logo{image-rendering:pixelated;}
.adc-hk-unvan{border:2px dashed #0000ee;margin:8px 0;padding:6px 8px;background:#f4efff;}
.adc-hk-unvan b{color:#0000ee;font-size:16px;margin-right:6px;}
.adc-hk-unvan small{color:#444;}
.adc-hk-unvan i{display:block;height:8px;border:2px solid #0000ee;margin-top:4px;background:#fff;}
.adc-hk-unvan em{display:block;height:100%;background:#ffcc00;}
.adc-hk-gunluk{display:flex;gap:8px;align-items:center;width:100%;text-align:left;border:2px solid #0000ee;background:#fff8d6;padding:7px 8px;margin:0 0 8px;font:inherit;color:#1a1a2e;cursor:pointer;box-shadow:2px 2px 0 #0000ee;}
.adc-hk-gunluk b{color:#0000ee;display:block;}
.adc-hk-gunluk small{display:block;font-size:12px;}
.adc-hk-gunluk.adc-hk-tamam2{background:#d9fbe9;}
.adc-hk-renk{display:inline-block;flex:none;width:14px;height:14px;border:2px solid #1a1a2e;vertical-align:-2px;}
.adc-hk-gunluk .adc-hk-renk{width:22px;height:22px;}
.adc-hk-sekmeler{display:flex;gap:6px;margin:6px 0 10px;}
.adc-hk-sekmeler .adc-hk-btn{flex:1;padding:6px 4px;}
.adc-hk-ipucu{font-size:12px;color:#444;margin:6px 0;}
.adc-hk h3,.adc-hk-h{font-family:inherit;}
.adc-hk-h{margin:4px 0 8px;font-size:15px;color:#0000ee;}
.adc-hk-ulusgrid{display:grid;grid-template-columns:1fr 1fr;gap:6px;}
.adc-hk-ulkart{display:flex;flex-direction:column;gap:2px;text-align:left;border:2px solid #0000ee;border-left:10px solid var(--u);background:#fff;padding:6px;font:inherit;color:#1a1a2e;cursor:pointer;box-shadow:2px 2px 0 #0000ee;min-width:0;}
.adc-hk-ulkart b{font-size:15px;color:#0000ee;}
.adc-hk-ulkart em{font-style:normal;font-size:12px;font-weight:700;}
.adc-hk-ulkart small{font-size:11px;line-height:1.3;color:#333;}
.adc-hk-ulkart i{font-style:normal;font-size:11px;font-weight:700;margin-top:2px;}
.adc-hk-ulkart.adc-hk-secili{background:#fff3c4;outline:3px solid #ff2bd6;outline-offset:1px;}
.adc-hk-z0{color:#12b76a;}.adc-hk-z1{color:#ff8a00;}.adc-hk-z2{color:#e8323c;}
.adc-hk-zorluk{display:flex;gap:6px;}
.adc-hk-zorluk .adc-hk-btn{flex:1;display:flex;flex-direction:column;align-items:center;}
.adc-hk-bos{text-align:center;padding:24px 10px;color:#555;}
.adc-hk-son{text-align:center;}
.adc-hk-buyukyazi{font-size:24px;font-weight:900;color:#ffcc00;letter-spacing:2px;text-shadow:2px 2px 0 #0000ee,-1px -1px 0 #0000ee;line-height:1.2;}
.adc-hk-puan{font-size:46px;font-weight:900;color:#0000ee;line-height:1.1;}
.adc-hk-dokum{border:2px solid #0000ee;margin:8px 0;text-align:left;}
.adc-hk-dokum div{display:flex;justify-content:space-between;gap:8px;padding:3px 8px;border-bottom:1px solid #c8b6ff;font-size:13px;}
.adc-hk-dokum div:last-child{border-bottom:0;font-weight:700;color:#0000ee;background:#f4efff;}
.adc-hk-odul{font-size:13px;font-weight:700;color:#12b76a;min-height:1em;}
.adc-hk-yeni{display:inline-block;background:#ff2bd6;color:#fff;padding:1px 6px;animation:adc-hk-yan .5s steps(1) infinite;}
@keyframes adc-hk-yan{50%{opacity:.4;}}
.adc-hk-eylem{display:flex;gap:6px;flex-wrap:wrap;margin:6px 0 2px;}
.adc-hk-eylem .adc-hk-btn{flex:1 1 auto;}
.adc-hk-kirmizi{color:#e8323c;}
.adc-hk-kirmizib{background:#e8323c;color:#fff;}
.adc-hk-yesilb{background:#12b76a;color:#fff;}
.adc-hk-podyum{display:flex;align-items:flex-end;justify-content:center;gap:6px;margin:0 0 12px;}
.adc-hk-basamak{flex:1;max-width:120px;display:flex;flex-direction:column;align-items:center;text-align:center;min-width:0;}
.adc-hk-av{display:block;object-fit:cover;border:2px solid #0000ee;background:#efeaff;flex:none;}
.adc-hk-av0{border-color:#ffcc00;}.adc-hk-av1{border-color:#b8b8cc;}.adc-hk-av2{border-color:#d08a4a;}
.adc-hk-isim{font-weight:700;font-size:13px;max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-top:4px;}
.adc-hk-deger{font-size:12px;color:#0000ee;font-weight:700;white-space:nowrap;display:flex;gap:4px;align-items:center;}
.adc-hk-deger .adc-hk-renk{width:10px;height:10px;border-width:1px;}
.adc-hk-unv{font-size:10px;color:#555;}
.adc-hk-blok{width:100%;margin-top:4px;border:2px solid #0000ee;box-shadow:3px 3px 0 #0000ee;display:flex;justify-content:center;font-size:22px;font-weight:700;color:#fff;padding-top:4px;text-shadow:2px 2px 0 #0000ee;}
.adc-hk-b1 .adc-hk-blok{height:70px;background:#ffcc00;}.adc-hk-b2 .adc-hk-blok{height:50px;background:#c8c8dc;}.adc-hk-b3 .adc-hk-blok{height:36px;background:#d08a4a;}
.adc-hk-liste{border:2px solid #0000ee;margin-bottom:10px;}
.adc-hk-sira{display:flex;align-items:center;gap:8px;padding:5px 8px;border-bottom:1px solid #c8b6ff;}
.adc-hk-sira:last-child{border-bottom:0;}
.adc-hk-no{width:22px;font-weight:700;color:#0000ee;flex:none;}
.adc-hk-isim2{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px;}
.adc-hk-isim2 small{color:#777;}
.adc-hk-ben{background:#efeaff;}
.adc-hk-benim{border:2px dashed #0000ee;padding:8px 10px;text-align:center;font-weight:700;color:#0000ee;margin-bottom:10px;background:#efeaff;}
.adc-hk-madalyalar{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-bottom:10px;}
.adc-hk-mdl{border:2px solid #0000ee;background:#fff3c4;padding:6px 4px;text-align:center;font-size:11px;line-height:1.25;box-shadow:2px 2px 0 #0000ee;}
.adc-hk-mdl span{display:block;font-size:24px;line-height:1.2;}
.adc-hk-mdl b{display:block;color:#0000ee;font-size:12px;}
.adc-hk-mdl.adc-hk-yok{background:#f2f2f6;box-shadow:none;border-style:dashed;color:#777;}
.adc-hk-mdl.adc-hk-yok span{filter:grayscale(1);opacity:.35;}
.adc-hk-nasil{padding-left:20px;margin:0 0 8px;}
.adc-hk-nasil li{margin-bottom:6px;font-size:13px;}
/* oyun ekranı */
.adc-hk-hud{display:flex;flex-wrap:wrap;gap:4px;flex:none;font-size:12px;font-weight:700;}
.adc-hk-hud span{border:2px solid #0000ee;background:#fff;padding:1px 6px;white-space:nowrap;color:#0000ee;}
.adc-hk-hud small{color:#12b76a;}
.adc-hk-bayrak{border-left:8px solid var(--u) !important;color:#1a1a2e !important;}
.adc-hk-ilad .adc-hk-bayrak{border:2px solid #1a1a2e;font-size:11px;padding:0 5px;margin-left:4px;}
.adc-hk-acil{color:#e8323c !important;border-color:#e8323c !important;animation:adc-hk-yan 1s steps(1) infinite;}
.adc-hk-hsar{position:relative;flex:1;min-height:150px;border:2px solid #0000ee;}
.adc-hk-harita{position:absolute;inset:0;overflow:auto;background:#5b7cfa;-webkit-overflow-scrolling:touch;touch-action:pan-x pan-y;}
.adc-hk-tuval{display:block;image-rendering:pixelated;image-rendering:crisp-edges;cursor:pointer;}
.adc-hk-zoom{position:absolute;right:6px;bottom:6px;display:flex;flex-direction:column;gap:4px;}
.adc-hk-zoom button{width:34px;height:34px;border:2px solid #0000ee;background:#fff;color:#0000ee;font-size:18px;font-weight:700;box-shadow:2px 2px 0 #0000ee;cursor:pointer;padding:0;}
.adc-hk-bilgi{flex:none;max-height:34vh;overflow:auto;border:2px solid #0000ee;padding:6px 8px;background:#fbf9ff;}
.adc-hk-ilad{font-size:15px;}
.adc-hk-ilad b{color:#0000ee;}
.adc-hk-oz{font-size:12px;margin:2px 0;}
.adc-hk-bilgi .adc-hk-btn{padding:4px 6px;font-size:12px;}
.adc-hk-bilgi .adc-hk-ipucu{margin:4px 0 0;}
.adc-hk-alt{flex:none;display:flex;gap:4px;flex-wrap:wrap;}
.adc-hk-alt .adc-hk-btn{flex:1 1 auto;padding:6px 6px;font-size:12px;}
.adc-hk-alt .adc-hk-bitir{flex:2 1 120px;background:#ffcc00;color:#1a1a2e;font-size:14px;}
.adc-hk-sayfa{position:absolute;inset:0;z-index:5;background:rgba(26,26,46,.45);display:none;align-items:center;justify-content:center;padding:10px;}
.adc-hk-kart{position:relative;background:#fff;border:3px solid #0000ee;box-shadow:4px 4px 0 #0000ee;width:min(420px,100%);max-height:100%;overflow:auto;padding:12px;}
.adc-hk-kart h3{margin:0 24px 6px 0;font-size:16px;color:#0000ee;}
.adc-hk-kapat{position:absolute;top:4px;right:4px;border:0;background:none;font-size:18px;color:#0000ee;cursor:pointer;}
.adc-hk-miktar{display:block;font-weight:700;margin:6px 0;}
.adc-hk-miktar input{display:block;width:100%;margin-top:4px;accent-color:#ff2bd6;height:28px;}
.adc-hk-sans{border:2px solid #0000ee;position:relative;padding:4px 8px;margin:6px 0;background:#fff;overflow:hidden;}
.adc-hk-sans b{position:relative;z-index:1;font-size:16px;}
.adc-hk-sans i{position:absolute;left:0;top:0;bottom:0;width:var(--p);background:var(--r);opacity:.35;}
.adc-hk-notlar{list-style:none;margin:4px 0;padding:0;font-size:12px;}
.adc-hk-arti{color:#12b76a;}.adc-hk-eksi{color:#e8323c;}
.adc-hk-sonuc{text-align:center;}
.adc-hk-yenilgi .adc-hk-buyukyazi{color:#e8323c;font-size:20px;}
.adc-hk-zafer .adc-hk-buyukyazi{animation:adc-hk-patla .5s steps(3) 2;}
@keyframes adc-hk-patla{50%{transform:scale(1.12);}}
.adc-hk-deha{font-weight:900;color:#ff2bd6;}
.adc-hk-tamam{display:block;width:100%;margin-top:8px;padding:8px;font-size:15px;}
.adc-hk-tehdit{border:2px solid #0000ee;position:relative;padding:3px 8px;overflow:hidden;}
.adc-hk-tehdit b{position:relative;z-index:1;}
.adc-hk-tehdit i{position:absolute;left:0;top:0;bottom:0;background:#e8323c;opacity:.35;}
.adc-hk-ulus{display:flex;align-items:center;gap:8px;padding:5px 0;border-bottom:1px solid #c8b6ff;}
.adc-hk-ulus div{flex:1;min-width:0;}
.adc-hk-ulus small{display:block;font-size:11px;}
.adc-hk-ulus .adc-hk-btn{padding:3px 6px;font-size:12px;}
.adc-hk-olu{opacity:.45;}
.adc-hk-odaklar{display:grid;grid-template-columns:1fr 1fr;gap:6px;}
.adc-hk-odak{display:flex;flex-direction:column;text-align:left;border:2px solid #0000ee;background:#fff;padding:6px;font:inherit;color:#1a1a2e;cursor:pointer;box-shadow:2px 2px 0 #0000ee;}
.adc-hk-odak b{color:#0000ee;font-size:13px;}
.adc-hk-odak small{font-size:11px;}
.adc-hk-odak i{font-style:normal;font-size:11px;color:#777;}
.adc-hk-odak:disabled{cursor:default;box-shadow:none;}
.adc-hk-odak.adc-hk-ozel{background:#fff3c4;}
.adc-hk-odak.adc-hk-bitti{background:#d9fbe9;}
.adc-hk-odak.adc-hk-simdi{outline:3px solid #ff2bd6;}
.adc-hk-danis,.adc-hk-log{list-style:none;margin:0;padding:0;}
.adc-hk-danis li,.adc-hk-log li{padding:5px 0;border-bottom:1px solid #c8b6ff;font-size:13px;}
.adc-hk-danis .adc-hk-btn{padding:1px 6px;font-size:11px;}
.adc-hk-log li.adc-hk-kotu{color:#c0122a;font-weight:700;}
.adc-hk-log li.adc-hk-iyi{color:#0b8a50;font-weight:700;}
.adc-hk-log li.adc-hk-dunya{color:#555;}
.adc-hk-bekle{text-align:center;font-weight:700;color:#0000ee;padding:16px 0;}
.adc-hk-olay{text-align:center;border:2px solid #c79400;background:#fff8d6;padding:8px;margin-bottom:8px;}
.adc-hk-olay small{letter-spacing:2px;color:#c79400;font-weight:700;}
.adc-hk-olay h3{margin:2px 0 4px !important;}
.adc-hk-secim{display:flex;flex-direction:column;gap:6px;}
.adc-hk-secim .adc-hk-btn{text-align:left;}
@media (max-width:520px){.adc-hk{padding:0;}.adc-hk-panel{max-height:100%;height:auto;}.adc-hk-panel.adc-hk-tam{height:100%;border-width:2px;}.adc-hk-govde{padding:8px;}.adc-hk-hud{font-size:11px;}.adc-hk-hud span{padding:1px 4px;}.adc-hk-alt .adc-hk-btn{font-size:11px;padding:6px 4px;}}
@media (prefers-reduced-motion: reduce){.adc-hk *{animation:none !important;}}
</style>
<?php
}, 20 );
