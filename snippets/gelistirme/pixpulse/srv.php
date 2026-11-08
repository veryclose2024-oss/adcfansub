<?php
$META=[]; $OPT=[]; $UID=5; $ACT=[]; $XP=0;
function add_filter(){} function add_action($h,$f){ global $ACT; $ACT[$h]=$f; }
function check_ajax_referer(){} function get_current_user_id(){ global $UID; return $UID; }
function sanitize_key($s){ return strtolower(preg_replace('/[^a-z0-9_\-]/i','',$s)); }
function absint($x){ return abs((int)$x); }
function get_user_meta($u,$k,$s=true){ global $META; return $META[$u][$k] ?? ''; }
function update_user_meta($u,$k,$v){ global $META; $META[$u][$k]=$v; }
function delete_user_meta($u,$k){ global $META; unset($META[$u][$k]); }
function get_option($k){ global $OPT; return $OPT[$k] ?? false; } function update_option($k,$v){ global $OPT; $OPT[$k]=$v; }
function delete_transient(){} function current_time($f){ return date($f); }
function wp_send_json_success($d=null){ throw new Exception(json_encode($d)); } function wp_send_json_error(){ throw new Exception('ERR'); }
function adc_xp_ver($u,$m){ global $XP; $XP+=$m; }
class DB{ public $usermeta='um'; function get_results(){return [];} function get_var(){return 0;} function prepare($q){return $q;} }
$wpdb=new DB(); define('HOUR_IN_SECONDS',3600);
function get_userdata(){ return null; }
require 'pixpulse.php';
function call($h,$post){ global $ACT; $_POST=$post; try{ $ACT[$h](); } catch(Exception $e){ return json_decode($e->getMessage(),true) ?? $e->getMessage(); } }
$gun = adc_pp_gunun();
call('wp_ajax_adc_pp_basla',['sarki'=>$gun,'zorluk'=>'normal']);
$META[5]['adc_pp_basla']['t'] -= 80; $n = adc_pp_sarkilar()[$gun]['n'][1];
$r = call('wp_ajax_adc_pp_bitti',['sarki'=>$gun,'zorluk'=>'normal','p'=>$n-10,'g'=>5,'o'=>0,'m'=>5,'k'=>60]);
echo "ok1 ", json_encode([$r['sayildi'],$r['skor'],$r['toplam'],$r['xp']]), " XP=$XP\n";
// cheat: too fast
call('wp_ajax_adc_pp_basla',['sarki'=>$gun,'zorluk'=>'normal']);
$r = call('wp_ajax_adc_pp_bitti',['sarki'=>$gun,'zorluk'=>'normal','p'=>$n,'g'=>0,'o'=>0,'m'=>0,'k'=>$n]); echo "hizli ", json_encode($r['sayildi']), "\n";
// wrong count
call('wp_ajax_adc_pp_basla',['sarki'=>'ilk-nabiz','zorluk'=>'zor']); $META[5]['adc_pp_basla']['t'] -= 80;
$r = call('wp_ajax_adc_pp_bitti',['sarki'=>'ilk-nabiz','zorluk'=>'zor','p'=>3,'g'=>0,'o'=>0,'m'=>0,'k'=>3]); echo "sayi ", json_encode($r['sayildi']), "\n";
// second song adds to total, XP not again
call('wp_ajax_adc_pp_basla',['sarki'=>'ilk-nabiz','zorluk'=>'kolay']); $META[5]['adc_pp_basla']['t'] -= 80;
$r = call('wp_ajax_adc_pp_bitti',['sarki'=>'ilk-nabiz','zorluk'=>'kolay','p'=>79,'g'=>0,'o'=>0,'m'=>0,'k'=>79]); echo "ikinci ", json_encode([$r['skor'],$r['toplam'],$r['artis'],$r['xp']]), " XP=$XP gunluk=", json_encode($OPT['adc_pp_gunluk']['skorlar']), "\n";
