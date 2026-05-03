<?php

return [
    'api_key' => env('RAJAONGKIR_API_KEY', ''),
    'base_url' => env('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api/v1'),
    'account_type' => env('RAJAONGKIR_ACCOUNT_TYPE', 'komerce-v2'),
    'origin_destination_id' => env('RAJAONGKIR_ORIGIN_DESTINATION_ID'),
    'origin_label' => env('RAJAONGKIR_ORIGIN_LABEL', 'Dealer Kremo'),
    'default_weight' => env('RAJAONGKIR_DEFAULT_WEIGHT', 125000),
    'default_couriers' => env('RAJAONGKIR_DEFAULT_COURIERS', 'jne:sicepat:jnt:tiki:lion:pos:rex'),
    'couriers' => [
        'jne' => 'JNE',
        'sicepat' => 'SiCepat',
        'jnt' => 'J&T Express',
        'tiki' => 'TIKI',
        'lion' => 'Lion Parcel',
        'pos' => 'POS Indonesia',
        'rex' => 'REX',
        'sap' => 'SAP Express',
        'ninja' => 'Ninja Xpress',
        'wahana' => 'Wahana Express',
        'ide' => 'ID Express',
        'anteraja' => 'Anteraja',
        'sentral' => 'Sentral Cargo',
    ],
];
