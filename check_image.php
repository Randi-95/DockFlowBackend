<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$p = \App\Models\Product::whereNotNull('barcode')->first();
if ($p) {
    $path = storage_path('app/public/' . $p->barcode);
    if (file_exists($path)) {
        echo "File size: " . filesize($path) . "\n";
        $img = imagecreatefrompng($path);
        echo "Width: " . imagesx($img) . "\n";
        echo "Height: " . imagesy($img) . "\n";
        
        $rgb = imagecolorat($img, 25, 25);
        $colors = imagecolorsforindex($img, $rgb);
        print_r($colors);
    }
}
