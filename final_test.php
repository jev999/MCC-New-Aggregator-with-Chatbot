<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Final Storage URL Test:\n";
echo "Storage URL: " . \Illuminate\Support\Facades\Storage::disk('public')->url('test.jpg') . "\n";
echo "Config APP_URL: " . config('app.url') . "\n";
