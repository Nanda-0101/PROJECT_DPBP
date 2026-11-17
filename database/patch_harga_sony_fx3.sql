UPDATE products
SET price_per_day      = 980000,
    price_per_half_day = 588000
WHERE name LIKE '%FX3%' OR name LIKE '%FX-3%';

-- Cek hasilnya:
SELECT id, name, price_per_day, price_per_half_day FROM products
WHERE name LIKE '%FX3%' OR name LIKE '%FX-3%';
