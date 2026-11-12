<?php

namespace App\Support;

/**
 * Mengubah baris product_specs menjadi tabel spesifikasi.
 *
 * Baris berformat "Label: nilai" menjadi baris tabel; baris tanpa label
 * (data lama, mis. "Video 4K 30fps") tampil sebagai daftar fitur.
 */
class SpecSheet
{
    private const MAX_LABEL_LENGTH = 32;

    /**
     * @param  iterable<string>  $lines
     * @return array{rows: list<array{label: string, value: string}>, features: list<string>}
     */
    public static function parse(iterable $lines): array
    {
        $rows = [];
        $features = [];

        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }

            $pos = strpos($line, ': ');
            if ($pos !== false && $pos > 0 && $pos <= self::MAX_LABEL_LENGTH) {
                $rows[] = [
                    'label' => trim(substr($line, 0, $pos)),
                    'value' => trim(substr($line, $pos + 2)),
                ];
            } else {
                $features[] = $line;
            }
        }

        return ['rows' => $rows, 'features' => $features];
    }
}
