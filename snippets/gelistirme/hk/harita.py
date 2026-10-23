# HK - History King haritası üretici. Çalıştır: python3 harita.py  → harita.js + onizleme.png
# Kaba kıyı çizgileri (boylam, enlem) → piksel ızgara → iller (kara üstünde en yakın tohum) → komşuluk.
import json, heapq, math
from PIL import Image, ImageDraw

W, H = 144, 68
LAT0, LAT1 = 66.0, 8.0
SS = 4  # alt örnekleme

def px(lon):
    # Avrupa + Orta Doğu daha geniş (iller sık), Asya biraz sıkışık
    if lon <= 62:
        return (lon + 12) / 74.0 * 86
    return 86 + (lon - 62) / 84.0 * (W - 86)

def py(lat):
    return (LAT0 - lat) / (LAT0 - LAT1) * H

ANAKARA = [(-9,43),(-9,38.7),(-8.8,37),(-6,36.1),(-2,36.7),(0,38.6),(0.2,40),(3.2,42),(3.2,43.3),(6,43.1),(8.7,44.4),(10.3,43.5),(11.5,42.4),(13.5,41.2),(15.6,40),(15.7,38),(16.5,38.4),(17.1,39.4),(16.6,40.4),(18.5,40.1),(17,41),(16,41.9),(14,42.6),(12.4,44.4),(12.5,45.5),(13.7,45.7),(14.6,45.2),(17,43.2),(19.4,41.8),(19.4,40.3),(21,38.4),(21.7,36.8),(23,36.5),(23,37.9),(24,38.1),(22.6,40.3),(24,40.8),(26,40.7),(26.5,40.3),(26.2,39.4),(26.8,38.3),(27.3,37),(28.3,36.7),(30.6,36.6),(32.5,36.1),(34,36.3),(36,36.7),(35.8,35),(35,33),(34.4,31.4),(32.2,31.2),(30,31.5),(25,31.8),(20,32.2),(19.8,30.5),(15.4,31.6),(11,33.4),(10.5,36.4),(9.8,37.3),(8.6,36.9),(3,36.8),(-1,35.6),(-5.9,35.8),(-9.8,30),(-13,27),(-13,8),(49,8),(51,10.5),(51.2,11.8),(45,10.4),(43.3,11.6),(43.3,12.5),(39,15.5),(37.3,19.5),(35.6,23.9),(33.5,27.7),(32.5,29.9),(34.9,29.5),(35,28),(37,25),(39,21.5),(41,17),(43,13),(45,12.8),(52,15.8),(55,17.5),(57,18.9),(59.8,22.5),(57.5,23.8),(56.3,25.5),(56.2,26.3),(55,25),(54,24.2),(51.5,24.5),(51.6,26),(50,26.6),(48.5,29),(48.8,30),(50.5,29.9),(51.5,27.9),(54,26.7),(56.5,27.1),(57.3,25.8),(61.6,25.2),(66.6,25.4),(68,23.8),(70,21),(72.8,19),(73.5,15.8),(74.8,12.8),(76.2,9.9),(77.5,8),(78.2,8.9),(79.8,10.3),(80.2,13),(80,15.8),(82.3,16.6),(85,19.5),(87,21.5),(89,21.8),(91.8,22.3),(92.5,20.5),(94.2,18),(94.5,16),(97.6,16.5),(98.5,13),(98.3,8),(100.3,8),(100.3,13.4),(102.5,12.2),(105,8.6),(106.7,10.4),(109.2,12),(108.8,15.3),(106.6,17.5),(105.7,18.9),(106.6,20.4),(108,21.6),(110,21.2),(113,22.2),(116.5,23),(119.5,25.5),(121.6,28.2),(122,30.8),(121,32.5),(120.3,34.4),(119.2,35),(120.5,36.2),(122.5,37.2),(120.7,37.8),(118.9,37.4),(117.8,38.6),(118,39.2),(121,40.8),(122.2,40.5),(121.2,38.8),(124.3,39.9),(125.2,37.7),(126.5,34.6),(129.2,35.2),(129.4,36.8),(128.4,38.6),(129.7,41),(131,42.6),(135.2,43.6),(138,46.5),(140.4,48.5),(140.6,52),(141.4,53.3),(137.5,54),(141,58.5),(146,59.3),(146,66),
           (31,66),(34.5,64.5),(36.5,64.8),(37.5,66),(25.2,66),(25,65),(22,63.5),(21.3,61.8),(21.4,60.5),(22.9,59.8),(25,60.2),(27,60.5),(29.5,60.2),(28,59.5),(23.5,59.2),(23.4,58.3),(24.3,57.8),(21.5,57.3),(21,56),(21.1,55.2),(19.6,54.4),(18.5,54.8),(16,54.3),(14.2,53.9),(12,54.2),(11,54),(10.6,54.4),(10.4,55.8),(10.6,57.7),(8.3,56.9),(8.2,55.5),(8.6,53.9),(7,53.5),(5,53.3),(4,51.9),(3,51.3),(1.6,50.9),(1.4,50.1),(-1,49.4),(-1.9,48.7),(-4.7,48.4),(-2.3,47.2),(-1.2,46),(-1.4,44),(-1.8,43.4),(-4.5,43.5),(-8,43.7)]
ISKANDINAV = [(4.9,61),(5,60),(5.6,58.8),(7,58),(8.2,58.1),(10.5,59.3),(11.2,58.4),(12.5,56.3),(13,55.4),(14.3,55.6),(16,56.2),(16.6,57.5),(18.5,59.3),(17.3,60.6),(17.5,62.3),(19.5,63.5),(21,64.4),(21.7,66),(12.5,66),(10,64),(7.5,63)]
ADALAR = [
    [(-5.7,50),(1.4,51.2),(1.7,52.7),(0.2,53.5),(-0.5,54.5),(-1.6,55.6),(-2,57.6),(-3.3,58.6),(-5,58.6),(-6.2,57.2),(-5.5,56),(-4.8,55),(-3,54.7),(-3.2,53.4),(-4.6,53.3),(-4.2,52.3),(-5.3,51.8),(-3,51.4)],
    [(-6,52.2),(-6.2,53.9),(-5.6,54.6),(-7.3,55.3),(-8.4,54.5),(-10,54.2),(-9.9,53),(-10.4,51.8),(-8.4,51.6)],
    [(12.4,38.1),(15.6,38.3),(15.1,36.6),(12.4,37.6)],
    [(8.4,41),(9.8,40.9),(9.6,39),(8.4,39)],
    [(23.5,35.6),(26.3,35.3),(26,34.9),(23.6,35.2)],
    [(32.3,35.1),(34.6,35.7),(34,34.6),(32.4,34.7)],
    [(130.9,34),(132.5,35.4),(135.7,35.7),(136.8,37.3),(139,38),(140,40),(140,41.4),(141.5,41.4),(142,39.5),(141,38),(140.8,36),(140,35),(139,34.9),(136.9,34.3),(135,33.6),(132,33.8)],
    [(129.6,33.6),(131.9,33.9),(131.5,31.3),(130.2,31.1),(129.8,32.6)],
    [(132.3,33.9),(134.7,34.2),(134.3,33.3),(132.9,32.8)],
    [(140,41.6),(141.3,43.3),(141.8,45.4),(145.5,43.3),(143.3,42),(140.9,41.8)],
    [(142,46),(143.5,49),(143,54),(142.3,54.3),(141.9,51),(141.8,48)],
    [(120.2,23),(121.5,25.2),(121.9,24.5),(120.8,22)],
    [(108.6,19.2),(110.5,20.1),(111,19.6),(109.6,18.2)],
    [(79.8,9.8),(80.9,9),(81.8,8),(79.7,8)],
]
DENIZLER = [
    [(28.2,41.4),(29,41.2),(31,41.1),(33,42),(35,42),(37,41.1),(39,41),(41.5,41.5),(41.6,42.6),(40,43.4),(38,44.4),(36.6,45.3),(35,45),(33.5,44.5),(32.5,45.4),(33.6,46.1),(31.5,46.6),(30,46),(29.7,45),(28.7,44.3),(27.9,42.7)],
    [(47,44.5),(47.5,45.6),(49,46.6),(51.5,47),(53,46.5),(53,45.2),(51.3,44.5),(52.7,42.5),(52.8,41.2),(54,40.8),(53.8,39),(54,37.4),(51,36.8),(49,37.6),(48.8,38.4),(49.5,40.3),(48.6,41.8),(47.5,43)],
    [(58.5,46.5),(61.5,46.5),(61.5,44),(58.5,44)],
]

# İller: id, ad, boylam, enlem, arazi (o ova, d dağ, f orman, c çöl, b bozkır), gelir, sahip (ulus no, -1 bağımsız)
# Uluslar: 0 Osmanlı, 1 Roma, 2 Moğol, 3 Britanya, 4 Fransa, 5 Rusya, 6 Çin, 7 Japonya, 8 İspanya, 9 Safevi
ILLER = [
    ('istanbul','İstanbul',28.9,41.0,'o',4,0), ('edirne','Edirne',26.3,42.3,'o',2,0), ('anadolu','Ankara',32.8,39.9,'b',2,0),
    ('roma','Roma',12.6,42.1,'o',4,1), ('milano','Milano',9.5,45.4,'o',3,1), ('napoli','Napoli',15.6,40.5,'d',2,1),
    ('karakurum','Karakurum',102.8,47.2,'b',4,2), ('kerulen','Kerulen',112,47.6,'b',2,2), ('altay','Altay',91,48,'d',1,2),
    ('londra','Londra',-0.6,51.8,'o',4,3), ('york','York',-1.8,54.2,'o',2,3), ('iskocya','İskoçya',-4.2,57,'d',1,3),
    ('paris','Paris',2.3,48.9,'o',4,4), ('lyon','Lyon',4.8,45.6,'d',2,4), ('bordo','Bordo',-0.5,44.6,'o',2,4),
    ('moskova','Moskova',37.6,55.6,'f',4,5), ('novgorod','Novgorod',32,58.6,'f',2,5), ('nijni','Nijni',44.5,56.4,'f',2,5),
    ('pekin','Pekin',116.4,39.9,'o',4,6), ('nankin','Nankin',118.5,31.8,'o',3,6), ('sian','Şian',108.9,34.3,'o',2,6), ('kanton','Kanton',113.3,23.6,'o',2,6),
    ('edo','Edo',139.8,36.5,'o',4,7), ('kyoto','Kyoto',135.6,35.1,'o',3,7), ('kyusu','Kyuşu',130.7,32.5,'d',2,7),
    ('madrid','Madrid',-3.7,40.4,'o',4,8), ('sevilla','Sevilla',-5.5,37.5,'o',2,8), ('barselona','Barselona',1,41.8,'d',2,8),
    ('isfahan','İsfahan',51.7,32.6,'d',4,9), ('tebriz','Tebriz',46.5,38.2,'d',2,9), ('mesed','Meşhed',59.6,35.8,'c',2,9),
    # Avrupa
    ('portekiz','Portekiz',-8.4,39.6,'o',2,-1), ('irlanda','İrlanda',-7.9,53.3,'o',1,-1), ('hollanda','Hollanda',5.2,52,'o',3,-1),
    ('bavyera','Bavyera',10.8,49.2,'f',2,-1), ('prusya','Prusya',13.4,52.8,'o',2,-1), ('viyana','Viyana',16.2,47.6,'d',3,-1),
    ('macar','Macaristan',19.8,46.8,'o',2,-1), ('lehistan','Lehistan',19.8,51.8,'o',2,-1), ('litvanya','Litvanya',25,55.3,'f',2,-1),
    ('danimarka','Danimarka',9.5,56,'o',2,-1), ('isvec','İsveç',15.5,60,'f',2,-1), ('norvec','Norveç',8.5,61.5,'d',1,-1),
    ('finlandiya','Finlandiya',26,62.6,'f',1,-1), ('kiev','Kiev',31,50.2,'o',2,-1), ('kirim','Kırım',34.4,45.4,'b',2,-1),
    ('eflak','Eflak',26,44.9,'o',2,-1), ('sirbistan','Sırbistan',20.4,43.6,'d',2,-1), ('mora','Mora',22,38.6,'d',2,-1),
    ('isvicre','İsviçre',7.5,46.8,'d',1,-1), ('lapon','Laponya',29,65,'f',1,-1),
    # Anadolu, Kafkas, Orta Doğu
    ('karaman','Karaman',32.6,37.2,'d',2,-1), ('trabzon','Trabzon',39.6,40.4,'d',2,-1), ('gurcistan','Gürcistan',44.6,42.2,'d',1,-1),
    ('halep','Halep',37.6,35.9,'o',2,-1), ('sam','Şam',36.6,32.6,'c',2,-1), ('bagdat','Bağdat',44.2,33.4,'o',3,-1),
    ('kahire','Kahire',31,29.6,'o',4,-1), ('hicaz','Hicaz',40,22.5,'c',1,-1), ('yemen','Yemen',45,15.3,'d',1,-1),
    ('necd','Necd',46.5,26,'c',1,-1), ('umman','Umman',56.6,21.3,'c',1,-1), ('kirman','Kirman',57.2,29.3,'c',1,-1),
    # Kuzey Afrika
    ('trablus','Trablusgarp',14.5,29.5,'c',1,-1), ('tunus','Tunus',9.5,35.2,'o',2,-1), ('cezayir','Cezayir',3,34,'c',2,-1),
    ('fas','Fas',-6,32,'d',2,-1), ('sahra','Sahra',3,22,'c',1,-1), ('mali','Mali',-5,14,'b',3,-1),
    ('kanem','Kanem',15,14.5,'b',1,-1), ('sudan','Sudan',31.5,16,'c',1,-1), ('habes','Habeşistan',38.8,10.5,'d',2,-1),
    # Orta Asya, Rusya doğusu
    ('astrahan','Astrahan',47,47,'b',2,-1), ('kazan','Kazan',50,55.3,'f',2,-1), ('kipcak','Deşt-i Kıpçak',60,49.5,'b',1,-1),
    ('harezm','Harezm',60,41.8,'c',2,-1), ('semerkant','Semerkant',67,39.6,'o',3,-1), ('sibir','Sibir',67,57.5,'f',1,-1),
    ('yedisu','Yedisu',75,46,'b',1,-1), ('kasgar','Kaşgar',77,39.3,'c',2,-1), ('kabil','Kabil',69.2,34.5,'d',2,-1),
    ('turfan','Turfan',89,42.2,'c',1,-1), ('tibet','Tibet',89,31.5,'d',1,-1), ('yenisey','Yenisey',90,58,'f',1,-1),
    ('baykal','Baykal',106,55,'f',1,-1), ('amur','Amur',128,53,'f',1,-1), ('ural','Ural',57,61.5,'f',1,-1),
    # Hindistan, Güneydoğu Asya
    ('delhi','Delhi',77.2,28.8,'o',4,-1), ('pencap','Pencap',71.5,31,'o',2,-1), ('gucerat','Gücerat',72,23,'o',2,-1),
    ('bengal','Bengal',88.4,23.5,'o',3,-1), ('dekkan','Dekkan',77.5,18,'d',2,-1), ('vicayanagar','Vicayanagar',77.5,12,'o',2,-1),
    ('burma','Burma',96,20.5,'f',2,-1), ('siam','Siam',100.8,14.5,'o',2,-1), ('daiviet','Dai Viet',106,20.5,'o',2,-1),
    # Doğu Asya
    ('yunnan','Yunnan',102.5,25,'d',1,-1), ('sichuan','Siçuan',104,30.6,'d',2,-1), ('mancurya','Mançurya',126,45,'f',2,-1),
    ('kore','Kore',127.2,37,'d',2,-1), ('gobi','Gobi',106,42,'c',1,-1), ('ezo','Ezo',142.6,43.4,'f',1,-1),
]
DENIZ_YOLU = [('londra','paris'), ('londra','hollanda'), ('irlanda','york'), ('irlanda','iskocya'), ('kyusu','kore'),
              ('napoli','tunus'), ('edo','ezo'), ('danimarka','isvec'), ('isvec','finlandiya'), ('umman','kirman'),
              ('mora','karaman'), ('sevilla','fas'), ('kirim','trabzon'), ('kyoto','kore')]

def cokgen(d, pts, renk):
    d.polygon([(px(a) * SS, py(b) * SS) for a, b in pts], fill=renk)

img = Image.new('L', (W * SS, H * SS), 0)
d = ImageDraw.Draw(img)
cokgen(d, ANAKARA, 255); cokgen(d, ISKANDINAV, 255)
for a in ADALAR: cokgen(d, a, 255)
for s in DENIZLER: cokgen(d, s, 0)
kara = [[False] * W for _ in range(H)]
for y in range(H):
    for x in range(W):
        t = 0
        for j in range(SS):
            for i in range(SS):
                t += img.getpixel((x * SS + i, y * SS + j)) > 0
        kara[y][x] = t >= SS * SS * 0.45

# Tohumlar → Dijkstra (kara üstünde), adalar kaldıysa en yakın tohum
N = len(ILLER)
tohum = []
for k, il in enumerate(ILLER):
    x, y = int(px(il[2])), int(py(il[3]))
    x = min(W - 1, max(0, x)); y = min(H - 1, max(0, y))
    if not kara[y][x]:
        best = None
        for yy in range(H):
            for xx in range(W):
                if kara[yy][xx]:
                    dd = (xx - x) ** 2 + (yy - y) ** 2
                    if best is None or dd < best[0]: best = (dd, xx, yy)
        x, y = best[1], best[2]
    tohum.append((x, y))
sahip = [[-1] * W for _ in range(H)]
mesafe = [[1e9] * W for _ in range(H)]
pq = []
for k, (x, y) in enumerate(tohum):
    if mesafe[y][x] > 0:
        mesafe[y][x] = 0; sahip[y][x] = k; heapq.heappush(pq, (0, x, y, k))
while pq:
    dd, x, y, k = heapq.heappop(pq)
    if dd > mesafe[y][x] or sahip[y][x] != k: continue
    for dx, dy, c in ((1,0,1),(-1,0,1),(0,1,1),(0,-1,1),(1,1,1.41),(1,-1,1.41),(-1,1,1.41),(-1,-1,1.41)):
        nx, ny = x + dx, y + dy
        if 0 <= nx < W and 0 <= ny < H and kara[ny][nx]:
            nd = dd + c
            if nd < mesafe[ny][nx]:
                mesafe[ny][nx] = nd; sahip[ny][nx] = k; heapq.heappush(pq, (nd, nx, ny, k))
for y in range(H):
    for x in range(W):
        if kara[y][x] and sahip[y][x] < 0:
            sahip[y][x] = min(range(N), key=lambda k: (tohum[k][0] - x) ** 2 + (tohum[k][1] - y) ** 2)

komsu = [set() for _ in range(N)]
kiyi = [0] * N
hucre = [0] * N
for y in range(H):
    for x in range(W):
        k = sahip[y][x]
        if k < 0: continue
        hucre[k] += 1
        for dx, dy in ((1,0),(0,1),(-1,0),(0,-1)):
            nx, ny = x + dx, y + dy
            if 0 <= nx < W and 0 <= ny < H:
                j = sahip[ny][nx]
                if j < 0: kiyi[k] = 1
                elif j != k: komsu[k].add(j); komsu[j].add(k)
idx = {il[0]: k for k, il in enumerate(ILLER)}
deniz = []
for a, b in DENIZ_YOLU:
    i, j = idx[a], idx[b]
    if j not in komsu[i]:
        deniz.append([i, j])
        komsu[i].add(j); komsu[j].add(i)

# Yazı yeri: ile en içteki hücre (sınıra en uzak)
etiket = []
for k in range(N):
    best = None
    for y in range(H):
        for x in range(W):
            if sahip[y][x] != k: continue
            r = 0
            while r < 6:
                ok = True
                for dy in range(-r - 1, r + 2):
                    for dx in range(-r - 1, r + 2):
                        nx, ny = x + dx, y + dy
                        if not (0 <= nx < W and 0 <= ny < H) or sahip[ny][nx] != k: ok = False; break
                    if not ok: break
                if not ok: break
                r += 1
            sc = r * 100 - ((x - tohum[k][0]) ** 2 + (y - tohum[k][1]) ** 2) ** 0.5
            if best is None or sc > best[0]: best = (sc, x, y)
    etiket.append((best[1], best[2]))

# RLE: her koşu 2 karakter (il harfi + uzunluk harfi)
ALF = ''.join(chr(c) for c in list(range(35, 127)) + list(range(192, 256)) if chr(c) not in '\\')
assert N + 1 <= len(ALF)
dizi = []
for y in range(H):
    for x in range(W):
        dizi.append(sahip[y][x] + 1)
rle = []
i = 0
while i < len(dizi):
    v = dizi[i]; n = 1
    while i + n < len(dizi) and dizi[i + n] == v and n < len(ALF): n += 1
    rle.append(ALF[v] + ALF[n - 1]); i += n
rle = ''.join(rle)

iller = []
for k, il in enumerate(ILLER):
    iller.append([il[0], il[1], etiket[k][0], etiket[k][1], il[4], il[5], il[6], kiyi[k], sorted(komsu[k])])
veri = {'w': W, 'h': H, 'alf': ALF, 'rle': rle, 'il': iller, 'deniz': deniz}
js = 'window.adcHKHarita=' + json.dumps(veri, ensure_ascii=False, separators=(',', ':')) + ';'
open('harita.js', 'w').write(js)
print('il', N, 'rle', len(rle), 'js', len(js.encode()))
for k in range(N):
    if hucre[k] < 6: print('KÜÇÜK', ILLER[k][0], hucre[k])
    if not komsu[k]: print('KOMŞUSUZ', ILLER[k][0])

# Önizleme
import random
random.seed(3)
renk = [(random.randint(80, 240), random.randint(80, 240), random.randint(80, 240)) for _ in range(N)]
U = [(215,38,61),(123,44,191),(31,158,137),(224,87,128),(46,111,219),(43,147,72),(242,193,78),(245,245,245),(231,111,81),(6,182,212)]
im = Image.new('RGB', (W * 6, H * 6), (111, 143, 240))
dr = ImageDraw.Draw(im)
for y in range(H):
    for x in range(W):
        k = sahip[y][x]
        if k >= 0:
            c = U[ILLER[k][6]] if ILLER[k][6] >= 0 else renk[k]
            dr.rectangle([x * 6, y * 6, x * 6 + 5, y * 6 + 5], fill=c)
for k in range(N):
    dr.text((etiket[k][0] * 6 - 6, etiket[k][1] * 6), ILLER[k][0][:6], fill=(0, 0, 0))
im.save('onizleme.png')
