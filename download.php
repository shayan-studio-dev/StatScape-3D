<?php
$q = isset($_GET['q']) ? $_GET['q'] : 'nature';
$apiUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['PHP_SELF']) . "/api.php?q=" . urlencode($q);
$json = @file_get_contents($apiUrl);

if (!$json) { die('خطا در دریافت اطلاعات از سرور.'); }

$html = file_get_contents('world.html');
$search = "let sceneData=null;";
$replace = "let bakedData = " . $json . ";";
$html = str_replace($search, $replace, $html);

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="world-' . urlencode($q) . '.html"');
header('Content-Length: ' . strlen($html));
echo $html;
exit;
?>