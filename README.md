# KissOkey – Web Sitesi

KissOkey mobil oyunu için tek sayfalık, çok dilli (TR/EN), SEO odaklı tanıtım sitesi. Framework yok: PHP 8.1+, saf CSS ve tek bir ES module.

## Gereksinimler

- Apache 2.4 (`mod_rewrite`, `mod_headers`, `mod_expires`, `mod_deflate`/`mod_brotli`)
- PHP 8.1+ (`mbstring`, galeri küçük görselleri için `gd` + WebP desteği)
- Sunucuda çalışan `mail()` (paylaşımlı hostinglerde varsayılan olarak açıktır)

## Canlıya almadan önce

1. `app/config.php` dosyasında `site_url`, mağaza linkleri, `app_store_id`, `contact.to` ve `contact.from` değerlerini güncelle.
2. Domain `kissokey.com` değilse `robots.txt`, `llms.txt`, `sitemap.xml` ve `site.webmanifest` içindeki adresleri değiştir.
3. `contact.from` adresi sitenin kendi domaininde olmalı (SPF/DKIM uyumu için), aksi halde mailler spam'e düşebilir.
4. Repo herkese açıksa form gizli anahtarını `KISSOKEY_FORM_SECRET` ortam değişkeniyle ver.

## Galeriye görsel ekleme

`assets/gallery/` klasörüne görseli bırakman yeterli; site otomatik listeler.

- Sıralama dosya adına göre yapılır: `01-lobi.webp`, `02-sise.webp` …
- Genişliği 600px'ten büyük görseller için ilk ziyarette `assets/gallery/thumbs/` altına WebP küçük görsel üretilir; tarayıcı ekran yoğunluğuna göre küçük ya da orijinal dosyayı seçer.
- Alt metinler dosya adından üretilir. Dile göre özel açıklama için `assets/gallery/captions.json` oluştur:

```json
{
    "01-lobi.webp": { "tr": "Lobi ekranı: 8 oyuncu masada", "en": "Lobby screen: 8 players at the table" }
}
```

## Metinler ve diller

Tüm metinler `app/lang/tr.php` ve `app/lang/en.php` içinde. Yeni dil eklemek için dosyayı kopyala (ör. `de.php`), `app/config.php` içindeki `langs` listesine ekle ve `sitemap.xml`'e yeni URL'yi yaz. `/de/` adresi `.htaccess` tarafından otomatik yönlendirilir.

## Yerelde çalıştırma

`php -S` `.htaccess` okumadığı için kök sayfa `http://localhost:8080/`, İngilizce sürüm `http://localhost:8080/index.php?lang=en` adresinden açılır:

```bash
php -S localhost:8080
```