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

    if ($statusCode !== 200) {
        unlink($filename);
        throw new Exception("Failed to download image from URL: $url. Status code: $statusCode");
    }
}

function downloadImages($database) {
    $imgFolder = __DIR__ . '/img';

    if (!file_exists($imgFolder)) {
        mkdir($imgFolder, 0777, true);
    }

    foreach ($database as $item) {
        $imageUrl = $item['imageUrl'];
        $cardid = str_replace('/', '-', $item['cardid']);
        $imageName = !is_null($item['p']) ? $cardid . '_' . $item['p'] . '.jpg' : $cardid . '.jpg';
        $imagePath = $imgFolder . '/' . $imageName;

        // Verifica dei doppioni solo se la variabile checkDuplicates è impostata su true
        if ($GLOBALS['checkDuplicates'] && file_exists($imagePath)) {
            echo "Skipped existing image: $imageName\n";
            continue;
        }

        try {
            downloadImage($imageUrl, $imagePath);
            echo "Downloaded image: $imageName\n";
        } catch (Exception $e) {
            echo "Failed to download image: $imageName\n";
        }
    }
}

function fetchCardDatabase() {
    $url = "https://digimoncard.dev/data.php";
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($statusCode !== 200) {
        throw new Exception("Failed to fetch card database. Status code: $statusCode");
    }

    $database = json_decode($response, true);
    downloadImages($database);
}

// Variabile per il controllo dei doppioni
$checkDuplicates = true;

try {
    fetchCardDatabase();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>