-- Patch database lama untuk akun admin dan produk milik CamRent.
-- Buat backup database sebelum menjalankan patch ini.

ALTER TABLE `products`
  MODIFY `seller_id` bigint(20) UNSIGNED DEFAULT NULL;

INSERT INTO `users` (`name`, `email`, `password`, `phone`, `role`, `status`)
SELECT
  'Admin CamRent',
  'admin@camrent.id',
  '$2y$12$/rRi6/r/y1VAwFR1.F6eEuTUzjOy/htnWw6mGMIX7Ji6chREybV9K',
  '081200000000',
  'admin',
  'Aktif'
WHERE NOT EXISTS (
  SELECT 1 FROM `users` WHERE `email` = 'admin@camrent.id'
);
