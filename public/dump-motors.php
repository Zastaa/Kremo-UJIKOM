<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$motors = \App\Models\Motor::with('jenisMotor')->get();
foreach ($motors as $motor) {
    echo $motor->id . " | " . $motor->nama_motor . " | " . ($motor->jenisMotor ? $motor->jenisMotor->merk : 'N/A') . " | " . $motor->foto1 . "\n";
}
