<?php



try {
    
    $db = new PDO("mysql:host=localhost;dbname=canan;charset=utf8", 'root', '');
    //echo "veritabanı bağlantısı başarılı";
} catch (PDOException $e) {
    // Burada oluşan hataların yönetimi için kodlar yazılır
    echo "Hata kodu: " . $e->getCode() . "<br>";
    echo "Hata mesajı: " . $e->getMessage() . "<br>";
}
