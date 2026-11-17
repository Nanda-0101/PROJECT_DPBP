-- 1) Harga + deskripsi singkat 
UPDATE products
SET price_per_day      = 980000,
    price_per_half_day = 588000,
    description        = 'Kamera cinema full-frame ringkas dengan sensitivitas tinggi, cocok untuk video sinematik dan produksi solo.'
WHERE name LIKE '%FX3%' OR name LIKE '%FX-3%';

-- 2) Hapus spesifikasi lama FX-3 
DELETE FROM product_specs
WHERE product_id IN (SELECT id FROM products WHERE name LIKE '%FX3%' OR name LIKE '%FX-3%');

INSERT INTO product_specs (product_id, spec_text, sort_order)
SELECT p.id, s.t, s.o
FROM products p
JOIN (
    SELECT 'Tipe: Cinema Line full-frame'                                          AS t,  1 AS o
    UNION ALL SELECT 'Sensor: 12,1 MP full-frame 35 mm Exmor R CMOS (35,6 x 23,8 mm)',     2
    UNION ALL SELECT 'Video: 4K hingga 120p',                                              3
    UNION ALL SELECT 'ISO: 80–102.400 (diperluas 409.600)',                                4
    UNION ALL SELECT 'Layar: 3,0" touchscreen, 2,36 juta titik, rotasi 270°',              5
    UNION ALL SELECT 'Slot kartu: Dual CFexpress Type A / SD',                             6
    UNION ALL SELECT 'Mount: Sony E',                                                      7
) s
WHERE p.name LIKE '%FX3%' OR p.name LIKE '%FX-3%';

-- 3) Cek hasilnya
SELECT id, name, price_per_day, price_per_half_day, description
FROM products WHERE name LIKE '%FX3%' OR name LIKE '%FX-3%';

SELECT product_id, spec_text, sort_order FROM product_specs
WHERE product_id IN (SELECT id FROM products WHERE name LIKE '%FX3%' OR name LIKE '%FX-3%')
ORDER BY product_id, sort_order;
