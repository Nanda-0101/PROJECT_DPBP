<?php

/*
|--------------------------------------------------------------------------
| Metode Pembayaran CamRent
|--------------------------------------------------------------------------
| Sumber tunggal untuk validasi, tampilan checkout, dan instruksi bayar.
| Nomor rekening / VA di bawah ini adalah DATA CONTOH — ganti dengan
| rekening resmi CamRent (bisa lewat .env atau langsung di file ini).
|
| type:
|   cash            => bayar saat pengambilan alat
|   bank            => transfer ke rekening bank
|   virtual_account => virtual account / dompet digital
|   qris            => scan QRIS
*/

return [

    'groups' => [
        'tunai'  => ['label' => 'Bayar di Tempat',            'hint' => 'Bayar tunai saat pengambilan alat.'],
        'bank'   => ['label' => 'Transfer Bank',              'hint' => 'Transfer manual ke rekening CamRent.'],
        'wallet' => ['label' => 'Virtual Account & E-Wallet', 'hint' => 'Bayar lewat virtual account atau dompet digital.'],
        'qris'   => ['label' => 'QRIS',                       'hint' => 'Scan dari aplikasi bank / e-wallet apa pun.'],
    ],

    'methods' => [
        'Cash' => [
            'label' => 'Cash', 'group' => 'tunai', 'type' => 'cash', 'icon' => 'bi-cash-coin',
            'account_name' => null, 'account_number' => null,
        ],

        'BCA' => [
            'label' => 'Bank BCA', 'group' => 'bank', 'type' => 'bank', 'icon' => 'bi-bank',
            'account_name'   => 'CamRent Indonesia',
            'account_number' => env('PAY_BCA_NUMBER', '7310 2468 13'),
        ],
        'BRI' => [
            'label' => 'Bank BRI', 'group' => 'bank', 'type' => 'bank', 'icon' => 'bi-bank',
            'account_name'   => 'CamRent Indonesia',
            'account_number' => env('PAY_BRI_NUMBER', '0412 01 000987 30 5'),
        ],
        'BNI' => [
            'label' => 'Bank BNI', 'group' => 'bank', 'type' => 'bank', 'icon' => 'bi-bank',
            'account_name'   => 'CamRent Indonesia',
            'account_number' => env('PAY_BNI_NUMBER', '0987 6543 21'),
        ],

        'SeaBank' => [
            'label' => 'SeaBank', 'group' => 'wallet', 'type' => 'virtual_account', 'icon' => 'bi-wallet2',
            'account_name'   => 'CamRent Indonesia',
            'account_number' => env('PAY_SEABANK_NUMBER', '9012 0812 3456 7890'),
        ],
        'DANA' => [
            'label' => 'DANA', 'group' => 'wallet', 'type' => 'virtual_account', 'icon' => 'bi-wallet2',
            'account_name'   => 'CamRent Indonesia',
            'account_number' => env('PAY_DANA_NUMBER', '8812 0812 3456 7890'),
        ],
        'ShopeePay' => [
            'label' => 'ShopeePay', 'group' => 'wallet', 'type' => 'virtual_account', 'icon' => 'bi-wallet2',
            'account_name'   => 'CamRent Indonesia',
            'account_number' => env('PAY_SHOPEEPAY_NUMBER', '1992 0812 3456 7890'),
        ],
        'GoPay' => [
            'label' => 'GoPay', 'group' => 'wallet', 'type' => 'virtual_account', 'icon' => 'bi-wallet2',
            'account_name'   => 'CamRent Indonesia',
            'account_number' => env('PAY_GOPAY_NUMBER', '7001 0812 3456 7890'),
        ],

        'QRIS' => [
            'label' => 'QRIS', 'group' => 'qris', 'type' => 'qris', 'icon' => 'bi-qr-code',
            'account_name' => 'CamRent Indonesia', 'account_number' => null,
        ],
    ],
];
