// PixPulse: PHP'deki nota sayıları (n) ve süreler müzik koduyla aynı mı? Kullanım: node kontrol.js pixpulse.php
global.window = {}; global.performance = {now: () => 0};
require('./muzik.js'); const P = window.adcPP;
const php = process.argv[2] || 'pixpulse.php';
const out = require('child_process').execSync(`php -r 'function add_filter(){} function add_action(){} require "${php}"; echo json_encode(array_values(adc_pp_sarkilar()));'`).toString();
let hata = 0;
for (const s of JSON.parse(out)) {
  s.bolum = s.bolum.split('');
  const n = ['kolay', 'normal', 'zor'].map(z => P.nota(Object.assign({}, s), z).length);
  const sure = P.bestele(Object.assign({}, s)).sure.toFixed(1);
  const ok = n.join() === s.n.join() && sure == s.sure.toFixed(1);
  if (!ok) hata++;
  console.log(ok ? 'TAMAM' : 'FARKLI', s.id, 'js', n.join(), sure, 'php', s.n.join(), s.sure);
}
process.exit(hata ? 1 : 0);
