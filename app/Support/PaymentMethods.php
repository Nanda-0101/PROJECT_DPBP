<?php

namespace App\Support;

/**
 * Akses terpusat ke config/payment.php.
 */
class PaymentMethods
{
    /** Semua metode, key => definisi (key ikut disimpan di bookings.payment_method). */
    public static function all(): array
    {
        $methods = [];
        foreach (config('payment.methods', []) as $key => $method) {
            $methods[$key] = ['key' => $key] + $method;
        }

        return $methods;
    }

    /** Daftar key yang valid untuk validasi `Rule::in()`. */
    public static function keys(): array
    {
        return array_keys(config('payment.methods', []));
    }

    public static function find(?string $key): ?array
    {
        return $key ? (static::all()[$key] ?? null) : null;
    }

    /** Label tampilan; metode lama (mis. "Transfer") tetap tampil apa adanya. */
    public static function label(?string $key): string
    {
        return static::find($key)['label'] ?? (string) $key;
    }

    /** Metode dikelompokkan untuk halaman checkout. */
    public static function grouped(): array
    {
        $groups = [];
        foreach (config('payment.groups', []) as $groupKey => $group) {
            $groups[$groupKey] = $group + ['methods' => []];
        }
        foreach (static::all() as $method) {
            if (isset($groups[$method['group']])) {
                $groups[$method['group']]['methods'][] = $method;
            }
        }

        return array_filter($groups, fn ($group) => $group['methods'] !== []);
    }
}
