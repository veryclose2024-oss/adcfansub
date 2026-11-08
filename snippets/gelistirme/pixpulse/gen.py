import re,json
php=open('pixpulse.php').read()
css=re.search(r'<style>(.*?)</style>',php,re.S).group(1)
S=json.load(open('sarkilar.json'))
yild=[[1,1,2],[1,2,3],[1,2,3],[2,3,4],[2,3,5],[2,4,5]]
for s,y in zip(S,yild):
    s['yildiz']=y; s['olcu']=sum(2 if b in 'io' else 8 for b in s['bolum']); s['sure']=round((s['olcu']*16+4)*15/s['bpm']+1.2,1)
V={'giris':True,'sarkilar':S,'gunun':'neon-damar','carpan':{'kolay':.6,'normal':1,'zor':1.4},'xp':5,'hedef_ok':False,'toplam':123456,
 'tablo':{'genel':[{'ad':'Tara','resim':'','deger':250000,'seviye':'Gezgin'},{'ad':'ADÇ','resim':'','deger':200000},{'ad':'Ali','resim':'','deger':150000},{'ad':'Ece','resim':'','deger':90000,'ben':True}],'bugun':[],'ben':{'toplam':90000,'sira':4,'gun_skor':0,'gun_sira':0}},
 'ajax':'/ajax','nonce':'x','giris_link':'#'}
html='<!doctype html><meta charset=utf-8><meta name=viewport content="width=device-width,initial-scale=1"><style>body{background:#c8b6ff;font-family:monospace}'+css+'</style><a href="#" data-adc-oyun="pixpulse">PixPulse aç</a><script>window.adcPPV='+json.dumps(V,ensure_ascii=False)+';</script>'
for f in ['muzik.js','oyun.js','ekran.js']: html+='<script>'+open(f).read()+'</script>'
open('test.html','w').write(html)
