# PHPLanGames Oyun Platformu - Tamamen yapay zeka ile oluşturuldu

Bu proje, çeşitli eğlenceli ve eğitici oyunları bir araya getiren bir web tabanlı oyun platformudur. Platformda "İsim Şehir", "Bilgi Yarışı", "Çizim Tahmin", "Cümle Kurmaca", "Gizli Kelime", "Kelime Zinciri", "Kimdir O?" ve "Trivia Race" gibi oyunlar oynanabilir.

## Özellikler

- Çok oyunculu lobi sistemi
- Oyun odaları ve oyun başlatma
- Her oyun için özel form ve arayüzler
- SQLite veritabanı ile veri yönetimi
- PHP tabanlı backend mimarisi

## Klasör Yapısı

- `games/` : Oyunların PHP sınıfları
- `templates/` : Oyunlara ait form ve arayüz dosyaları
- `includes/` : Yardımcı fonksiyonlar ve ortak dosyalar
- `data/` : SQLite veritabanı dosyası
- Ana dizin: Oyun yönetimi ve lobi dosyaları

## Kurulum

1. Projeyi bilgisayarınıza klonlayın.
2. PHP 7.4+ ve SQLite desteği olduğundan emin olun.
3. `data/isimsehir.sqlite` dosyasının yazılabilir olduğundan emin olun.
4. Proje dizinini bir web sunucusunda çalıştırın (ör. XAMPP, MAMP veya yerel Apache/Nginx).

## Kullanım

- Ana sayfadan lobiye giriş yapın.
- Oyun odası oluşturun veya mevcut bir odaya katılın.
- Oyun başlatıldığında ilgili oyun arayüzü otomatik olarak açılır.
- Oyunlar tamamlandığında sonuçlar ekranda görüntülenir.

## Katkı

Katkıda bulunmak için pull request gönderebilir veya issue açabilirsiniz.

---

Her türlü öneri ve geri bildirim için iletişime geçebilirsiniz.
