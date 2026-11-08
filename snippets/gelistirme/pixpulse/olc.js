global.window={};global.performance={now:()=>0};require('./'+(process.argv[2]||'muzik.js'));const P=window.adcPP;
for(const s of require("./sarkilar.json"))for(const z of ["kolay","normal","zor"]){const n=P.nota(JSON.parse(JSON.stringify(s)),z);const c=[0,0,0,0];n.forEach(x=>c[x.l]++);
let run=1,mx=1;for(let i=1;i<n.length;i++){run=n[i].l===n[i-1].l?run+1:1;mx=Math.max(mx,run);}
let ps=0;for(let i=0;i<n.length;i++){let j=i;while(j<n.length&&n[j].t-n[i].t<1)j++;ps=Math.max(ps,j-i);}
// rhythm variety: distinct inter-onset gaps (in steps) and distinct 4-gram lane patterns
const ad=P.bestele(JSON.parse(JSON.stringify(s))).adim; const gaps=new Set();for(let i=1;i<n.length;i++){const g=Math.round((n[i].t-n[i-1].t)/ad);if(g>0&&g<=8)gaps.add(g);}
const pat=new Set();for(let i=3;i<n.length;i++)pat.add(n.slice(i-3,i+1).map(x=>x.l).join(''));
const pct=c.map(x=>Math.round(x/n.length*100)+'%').join(' ');
console.log(s.id.padEnd(17),z.padEnd(7),String(n.length).padStart(3),'nps',(n.length/ (n[n.length-1].t)).toFixed(1),'max/s',ps,'renk',pct,'üstüste',mx,'aralık',[...gaps].sort((a,b)=>a-b).join(','),'desen',pat.size);}
