<?php


/**
 * Her hoca için renkler oluşturur ve ders programını hazırlar.
 *
 * @param array $hocaList - Hocaların bulunduğu dizi
 *
 * @return array - Hocaların renkleri ve ders programları
 */
function renklendirmeAlgoritmasi($hocaList)
{
    $renkler = []; // Hoca renklerini tutacak dizi
    $program = []; // Hocaların ders programlarını tutacak dizi

    foreach ($hocaList as $hoca) {
        $hash = $hoca->getHash(); // Hoca nesnesinin hash değerini al
        if (!isset($renkler[$hash])) {
            $renkler[$hash] = []; // Eğer renk henüz eklenmemişse, boş bir dizi oluştur
        }

        $renk = $hash; // Renk değerini hash'e eşitle
        foreach ($hoca->givenLessons as $ders) {
            if (!isset($program[$renk])) {
                $program[$renk] = []; // Eğer program henüz eklenmemişse, boş bir dizi oluştur
            }
            $program[$renk][] = $ders; // Hocanın derslerini programına ekle
        }
    }

    return $program; // Oluşturulan programı döndür
}

/**
 * Hocaların ders programlarını otomatik olarak oluşturur.
 *
 * @param array $hocalar - Hocaların bulunduğu dizi
 *
 * @return array - Oluşturulan ders programı
 */
function otomatikDersProgramiOlustur($hocalar)
{
    $program = []; // Ders programını tutacak dizi
    $renkler = renklendirmeAlgoritmasi($hocalar); // Renkleri ve programları oluştur

    $index = 0;

    foreach ($renkler as $renk => $dersler) {
        $workingHours = WorkingHours::workingHours(); // Çalışma saatlerini al

        if (!isset($program[$renk])) {
            $program[$renk] = []; // Eğer program henüz eklenmemişse, boş bir dizi oluştur
        }

        if (isset($hocalar[$index]->istenmeyen_gun) and $hocalar[$index]->istenmeyen_gun != null) {
            $workingHours = WorkingHours::excludeDays($hocalar[$index]->istenmeyen_gun);
        }


        foreach ($dersler as $ders) {
            if ($ders->gun != "") {
                if ($ders->saat != "") {
                    $hour = $ders->saat;
                } else {
                    $hour = $workingHours[$ders->gun][0];
                }
                $derslik = Classes::bosDerslikBul($ders->gun, $hour, $program);
                if ($derslik !== null) {
                    $program[$renk][] = new Ders($ders->id, $ders->ad, $ders->gun, $hour, $derslik);
                    array_shift($workingHours[$ders->gun]);
                }
            } else {
                $derslik = null;
                foreach ($workingHours as $day => $hours) {
                    foreach ($hours as $saat) {
                        $derslik = Classes::bosDerslikBul($day, $saat, $program);
                        if ($derslik !== null) {
                            $program[$renk][] = new Ders($ders->id, $ders->ad, $day, $saat, $derslik);
                            array_shift($workingHours[$day]);
                            break; // İçteki döngüden çıkarak bir sonraki ders için devam et
                        }
                    }
                    if ($derslik !== null) {
                        break; // Dıştaki döngüden çıkarak bir sonraki hoca için devam et
                    }
                }
            }
        }
        $index++;
    }


    return $program; // Oluşturulan programı döndür
}

function arrayOfExplodedDays($days)
{
    if ($days != null) {
        return explode(",", $days);
    } else {
        return null;
    }
}

function findLessonAtHourAndDay($dersler, $saat, $gun)
{
    foreach ($dersler as $ders) {
        if ($ders->saat == $saat && $ders->gun == $gun) {
            return $ders;
        }
    }
    return null;
}
