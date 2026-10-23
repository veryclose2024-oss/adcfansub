// Bot simülasyonu: node sim.js  (oyuncu yerine basit ya da akıllı bir bot oynar)
global.window = {};
require('./harita.js'); require('./motor.js'); require('./zeka.js');
const M = window.adcHKMotor;
function risk(S, u, k) { // komşu (savaşta ya da savaş açabilecek) en büyük ordu
  let t = 0; for (const j of M.IL[k].komsu) { const s = S.il[j].s; if (s >= 0 && s !== u) t = Math.max(t, S.il[j].a * (S.sv[u][s] ? 1 : 0.6)); } return t; }
function akilli(S, u) {
  const L = S.ul[u];
  if (L.odak < 0) { const i = [5,0,1,3,2,4].find(i => L.bitenOdak.indexOf(i) < 0); if (i !== undefined) M.odakSec(S, u, i); }
  for (let j = 0; j < 10; j++) if (S.sv[u][j] && (S.sk[u][j] >= 2 || [...Array(10).keys()].filter(x=>S.sv[u][x]).length > 1)) M.barisTeklif(S, j);
  // savunma ihtiyacına göre asker
  const gel = M.gelirHesap(S, u);
  let my = M.iller(S, u).map(k => ({ k, v: risk(S, u, k) - S.il[k].a })).sort((a,b)=>b.v-a.v);
  for (const o of my) { if (L.altin - M.fiyat(S,u,'asker') < Math.max(0, -gel.net)) break; M.topla(S, o.k, Math.max(1, Math.min(9, Math.ceil(o.v) + 3))); }
  for (const o of my) M.topla(S, o.k, 9);
  for (let r = 0; r < 15; r++) {
    let best = null;
    for (const f of M.iller(S, u)) { const F = S.il[f]; if (F.h < 1) continue;
      const keep = Math.ceil(risk(S, u, f) * 0.7); const n = F.h - Math.max(0, keep - (F.a - F.h)); if (n < 1) continue;
      for (const t of M.IL[f].komsu) { const T = S.il[t]; if (T.s === u || (T.s >= 0 && !S.sv[u][T.s])) continue;
        if (L.tehdit >= 11 && L.tehdit + (T.s < 0 ? 1 : 2) >= 14) continue; const tm = M.tahmin(S, f, t, n); if (tm.p < 0.75) continue;
        const v = (M.IL[t].gelir * 2 + (T.s >= 0 && S.ul[T.s].bk === t ? 8 : 0)) * tm.p - tm.kayip * 0.3; if (!best || v > best.v) best = { f, t, n, v }; } }
    if (!best) break; M.hamle(S, best.f, best.t, best.n);
  }
  let svs = 0; for (let j = 0; j < 10; j++) svs += S.sv[u][j];
  if (!svs && L.tehdit < 8 && S.tur > 3) for (let j = 0; j < 10; j++) if (j !== u && !S.ul[j].olu && S.at[u][j] <= S.tur && M.komsuUlus(S, u, j) && M.asker(S, j) < M.asker(S, u) * 0.8) { M.savasIlan(S, u, j); break; }
  for (const f of M.iller(S, u)) { const F = S.il[f]; if (F.h < 1 || risk(S,u,f) > 0) continue; const sin = M.IL[f].komsu.some(j => S.il[j].s !== u); if (sin) continue;
    let t = M.IL[f].komsu.filter(j => S.il[j].s === u).sort((a,b)=>risk(S,u,b)-risk(S,u,a))[0]; if (t !== undefined) M.hamle(S, f, t, F.h); }
}
function oyna(ulus, z, tip, seed) {
  const S = M.yeniOyun({ ulus, zorluk: z, seed });
  let guard = 0;
  while (!S.bitti && guard++ < 40) {
    if (S.olay) M.olayCevap(S, 0);
    if (S.teklif !== null && S.teklif !== undefined) M.teklifCevap(S, 1);
    if (tip === 'akilli') akilli(S, ulus);
    else if (tip !== 'pasif') {
      const my = M.iller(S, ulus);
      for (const k of my) M.topla(S, k, 9);
      for (let r = 0; r < 12; r++) {
        let best = null;
        for (const f of M.iller(S, ulus)) { const F = S.il[f]; if (F.h < 1) continue;
          for (const t of M.IL[f].komsu) { const T = S.il[t]; if (T.s === ulus || (T.s >= 0 && !S.sv[ulus][T.s])) continue;
            const tm = M.tahmin(S, f, t, F.h); if (tm.p < 0.45) continue; const v = M.IL[t].gelir * tm.p; if (!best || v > best.v) best = { f, t, v }; } }
        if (!best) break; M.hamle(S, best.f, best.t, S.il[best.f].h);
      }
    }
    M.turBitir(S);
  }
  return M.sonuc(S);
}
const tipler = process.argv[2] ? process.argv[2].split(',') : ['pasif', 'aptal', 'akilli'];
const ad = ['osmanli','roma','mogol','britanya','fransa','rusya','cin','japonya','ispanya','safevi'];
for (const z of [0,1,2]) {
  console.log('== zorluk', z);
  for (let u = 0; u < 10; u++) {
    const row = [];
    for (const tip of tipler) {
      let t = 0, il = 0, kral = 0, yen = 0; const K = +(process.argv[3]||6);
      for (let s = 0; s < K; s++) { const r = oyna(u, z, tip, 'x' + s + tip + u); t += r.puan; il += r.il; kral += r.kral; yen += r.il === 0 ? 1 : 0; }
      row.push(tip + ' ' + Math.round(t/K) + 'p ' + (il/K).toFixed(1) + 'il' + (kral? ' K'+kral:'') + (yen? ' Y'+yen:''));
    }
    console.log(ad[u].padEnd(9), row.join(' | '));
  }
}
