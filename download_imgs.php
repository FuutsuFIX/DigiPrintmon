<?php

function downloadImage($url, $filename) {
    $file = fopen($filename, 'w');
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_FILE, $file);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    fclose($file);

    if ($statusCode >= 200 && $statusCode < 300) {
        return true;
    } else {
        unlink($filename);
        return false;
    }
}

function downloadImages($database) {
    $imgFolder = __DIR__ . '/img';

    if (!file_exists($imgFolder)) {
        mkdir($imgFolder, 0777, true);
    }

    foreach ($database as $item) {
        $imageUrl = $item['imageUrl'];
        $imageName = str_replace('/', '-', $item['cardid']) . '.jpg';
        $imagePath = $imgFolder . '/' . $imageName;

        // Verifica dei doppioni solo se la variabile $checkDuplicates Ã¨ impostata su true
        $checkDuplicates = true; // Imposta a true per abilitare il controllo dei doppioni
        if ($checkDuplicates && file_exists($imagePath)) {
            echo "Skipped existing image: $imageName\n";
            continue;
        }

        if (downloadImage($imageUrl, $imagePath)) {
            echo "Downloaded image: $imageName\n";
        } else {
            echo "Failed to download image: $imageName\n";
        }
    }
}

function fetchCardDatabase() {
    $url = "https://digimoncard.dev/data.php";
    $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true
    );

    $ch = curl_init($url);
    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($statusCode >= 200 && $statusCode < 300) {
        $database = json_decode($response, true);
        downloadImages($database);
    } else {
        echo "Failed to fetch card database\n";
    }
}

fetchCardDatabase();