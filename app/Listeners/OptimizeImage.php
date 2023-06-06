<?php

namespace App\Listeners;

use TCG\Voyager\Events\MediaFileAdded;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class OptimizeImage
{
    public function handle(MediaFileAdded $event)
    {
        $path = Storage::disk('public')->path($event->path);

        // Optimize image to jpeg and webp format
        ImageOptimizer::optimize($path);

        $image = Image::make($path);

        // Create 3 sizes for each format
        foreach (['jpeg', 'webp'] as $format) {
            foreach ([300, 600, 900] as $size) {
                $filename = basename($event->path, '.' . pathinfo($event->path, PATHINFO_EXTENSION));
                
                $resizedImage = clone $image;
                
                $resizedImage->fit($size, $size, function ($constraint) {
                    $constraint->upsize();
                })->encode($format, 100)->save(public_path("storage/images/{$format}/{$size}_{$filename}.{$format}"));
            }
        }
    }
}
