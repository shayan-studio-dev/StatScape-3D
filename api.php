<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
$prompt = isset($_GET['q']) ? trim($_GET['q']) : 'طبیعت';
$text = mb_strtolower($prompt, 'UTF-8');

mt_srand(crc32($prompt));

$isNight = (mb_strpos($text, 'شب') !== false || mb_strpos($text, 'تاریک') !== false);

$weights = ['house' => 0.25, 'tree' => 0.45, 'mountain' => 0.20, 'car' => 0.10];
if (mb_strpos($text, 'جنگل') !== false) $weights = ['house' => 0.05, 'tree' => 0.90, 'mountain' => 0.05, 'car' => 0.0];
elseif (mb_strpos($text, 'شهر') !== false) $weights = ['house' => 0.70, 'tree' => 0.20, 'mountain' => 0.0, 'car' => 0.10];
elseif (mb_strpos($text, 'کوه') !== false) $weights = ['house' => 0.0, 'tree' => 0.20, 'mountain' => 0.80, 'car' => 0.0];
elseif (mb_strpos($text, 'ماشین') !== false) $weights = ['house' => 0.10, 'tree' => 0.10, 'mountain' => 0.0, 'car' => 0.80];

$gridSize = 40;
$terrain = [];
for ($i = 0; $i <= $gridSize; $i++) {
    for ($j = 0; $j <= $gridSize; $j++) {
        $height = sin($i * 0.15) * cos($j * 0.15) * 4;
        $height += sin($i * 0.4) * cos($j * 0.4) * 1.5;
        $terrain[] = max(0, $height);
    }
}

function gaussian_rand() {
    $u1 = 0; $u2 = 0;
    while ($u1 == 0) $u1 = mt_rand() / mt_getrandmax();
    while ($u2 == 0) $u2 = mt_rand() / mt_getrandmax();
    return sqrt(-2 * log($u1)) * cos(2 * M_PI * $u2);
}

$objects = [];
$count = 120;
for ($i = 0; $i < $count; $i++) {
    $r = mt_rand() / mt_getrandmax();
    $type = 'tree';
    if ($r < $weights['house']) $type = 'house';
    elseif ($r < $weights['house'] + $weights['tree']) $type = 'tree';
    elseif ($r < $weights['house'] + $weights['tree'] + $weights['mountain']) $type = 'mountain';
    else $type = 'car';

    if ($type === 'mountain') {
        $angle = mt_rand(0, 360) * (M_PI / 180);
        $radius = mt_rand(90, 140);
        $gx = cos($angle) * $radius;
        $gz = sin($angle) * $radius;
    } else {
        $gx = gaussian_rand() * 25;
        $gz = gaussian_rand() * 25;
        $gx = max(-60, min(60, $gx));
        $gz = max(-60, min(60, $gz));
    }

    $objects[] = [
        'type' => $type,
        'x' => $gx,
        'z' => $gz,
        'scale' => 0.8 + abs(gaussian_rand()) * 0.5,
        'rot' => mt_rand(0, 360) * (M_PI / 180),
        'color' => mt_rand(0, 3)
    ];
}

echo json_encode([
    'time' => $isNight ? 'night' : 'day',
    'terrain' => $terrain,
    'gridSize' => $gridSize,
    'objects' => $objects
], JSON_UNESCAPED_UNICODE);