<?php

/**
 * Yerel bir kez çalıştırın: php scripts/download_turkiye_geo.php
 * database/data/*.json dosyalarını üretir (SSL sorunlu ortamlar için).
 */
require __DIR__.'/../vendor/autoload.php';

$client = new GuzzleHttp\Client([
    'verify' => false,
    'timeout' => 120,
]);

$base = 'https://api.turkiyeapi.dev/v1';
$outDir = __DIR__.'/../database/data';
if (! is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

$r = $client->get($base.'/provinces', ['query' => ['limit' => 100, 'fields' => 'id,name']]);
file_put_contents($outDir.'/provinces.json', (string) $r->getBody());

$all = [];
$offset = 0;
$limit = 100;
do {
    $r = $client->get($base.'/districts', [
        'query' => [
            'limit' => $limit,
            'offset' => $offset,
            'activatePostalCodes' => 'true',
            'fields' => 'id,name,provinceId,postalCode',
        ],
    ]);
    $j = json_decode((string) $r->getBody(), true);
    $rows = $j['data'] ?? [];
    if (! is_array($rows) || $rows === []) {
        break;
    }
    foreach ($rows as $row) {
        $all[] = $row;
    }
    $offset += $limit;
} while (count($rows) === $limit);

file_put_contents($outDir.'/districts.json', json_encode($all, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo count($all)." ilçe yazıldı.\n";

$allN = [];
$offset = 0;
$limit = 1000;
do {
    $r = $client->get($base.'/neighborhoods', [
        'query' => [
            'limit' => $limit,
            'offset' => $offset,
            'fields' => 'id,name,districtId,provinceId',
        ],
    ]);
    $j = json_decode((string) $r->getBody(), true);
    $rows = $j['data'] ?? [];
    if (! is_array($rows) || $rows === []) {
        break;
    }
    foreach ($rows as $row) {
        $allN[] = $row;
    }
    $offset += $limit;
    echo "mahalle offset {$offset}…\n";
} while (count($rows) === $limit);

file_put_contents($outDir.'/neighborhoods.json', json_encode($allN, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo count($allN)." mahalle yazıldı.\n";
