<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Picqer\Barcode\BarcodeGeneratorPNG;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'sku_code',
        'barcode',
        'name',
        'image_url',
        'stock_qty',
        'unit',
        'price',
        'rack_location',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->barcode)) {
                $generator = new BarcodeGeneratorPNG();
                $barcodeData = $generator->getBarcode($product->sku_code, $generator::TYPE_CODE_128, 2, 60);

                $padding = 20;
                $image = imagecreatefromstring($barcodeData);
                $width = imagesx($image);
                $height = imagesy($image);

                $newWidth = $width + ($padding * 2);
                $newHeight = $height + ($padding * 2);

                $whiteImage = imagecreatetruecolor($newWidth, $newHeight);
                $white = imagecolorallocate($whiteImage, 255, 255, 255);
                imagefill($whiteImage, 0, 0, $white);

                imagecopy($whiteImage, $image, $padding, $padding, 0, 0, $width, $height);

                ob_start();
                imagepng($whiteImage);
                $finalBarcode = ob_get_clean();

                imagedestroy($image);
                imagedestroy($whiteImage);

                $filename = 'barcodes/' . $product->sku_code . '.png';
                Storage::disk('public')->put($filename, $finalBarcode);
                $product->barcode = $filename;
            }
        });
    }
}
