const { chromium } = require('playwright');
(async () => {
  const b = await chromium.launch({args:['--autoplay-policy=no-user-gesture-required']});
  const mod = process.argv[2] || 'tel';
  const ctx = await b.newContext(mod==='tel'?{viewport:{width:360,height:616},hasTouch:true,isMobile:true,deviceScaleFactor:2}:{viewport:{width:1280,height:800}});
  const p = await ctx.newPage();
  const errs=[]; p.on('pageerror',e=>errs.push(e.message)); p.on('console',m=>{if(m.type()==='error')errs.push(m.text())});
  await p.route('**/ajax', r=>r.fulfill({json:{success:true,data:{sayildi:true,xp:5,artis:1234,toplam:124690,tablo:{genel:[],bugun:[],ben:{gun_sira:2,hedef_ok:true}}}}}));
  await p.goto('http://localhost:8765/test.html');
  await p.click('[data-adc-oyun]');
  await p.waitForTimeout(300);
  await p.screenshot({path:`menu-${mod}.png`});
  const song = +(process.argv[3]||0), zor = process.argv[4]||'normal', acc = +(process.argv[5]||1);
  await p.evaluate(z=>{document.querySelectorAll('.adc-pp-sekmeler .adc-pp-btn').forEach(b=>{if(b.textContent.toLowerCase().includes(z))b.click();});}, zor);
  await p.evaluate(i=>document.querySelectorAll('.adc-pp-sarki')[i].click(), song);
  await p.waitForFunction(()=>window.adcPP.son, null, {timeout:15000});
  // bot
  await p.evaluate(acc=>{
    const P=window.adcPP, cv=document.querySelector('.adc-pp-sahne canvas');
    window._bot={vur:0};
    const ns=P.son.notalar.map(n=>({t:n.t,u:n.u,l:n.l,done:0,up:0}));
    function ev(type,l,id){const r=cv.getBoundingClientRect();const x=r.left+(8+l*32+16)/144*r.width;cv.dispatchEvent(new PointerEvent(type,{clientX:x,clientY:r.top+r.height*0.8,pointerId:id,bubbles:true,cancelable:true}));}
    function f(){ if(!P.son) return; const t=P.son.zaman(performance.now());
      ns.forEach((n,i)=>{ if(!n.done && t>=n.t-0.004){ n.done=1; if(Math.random()<acc){ev('pointerdown',n.l,100+i); window._bot.vur++; if(!n.u) setTimeout(()=>ev('pointerup',n.l,100+i),40); }}
        if(n.done && n.u && !n.up && t>=n.u+0.02){n.up=1; ev('pointerup',n.l,100+i);} });
      requestAnimationFrame(f);} f();
  }, acc);
  await p.waitForTimeout(9000);
  await p.screenshot({path:`oyun-${mod}.png`});
  await p.waitForSelector('.adc-pp-sonuc',{timeout:120000});
  await p.waitForTimeout(500);
  await p.screenshot({path:`sonuc-${mod}.png`});
  console.log(await p.evaluate(()=>document.querySelector('.adc-pp-sonuc').innerText.replace(/\n/g,' | ')));
  await p.click('text=🏆 En iyiler'); await p.click('text=Tüm zamanlar'); await p.screenshot({path:`siralama-${mod}.png`});
  console.log('errors', errs);
  await b.close();
})();
