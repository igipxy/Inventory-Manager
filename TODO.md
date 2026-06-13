# TODO

- [x] Identify and fix multi-tenant aggregate query leaks in dashboard (`index.php`).
- [x] Enforce session validation and set `$active_user_id` once.

- [x] Rewrite prepared statements for:
  - [x] Total suppliers (WHERE user_id = ?)
  - [x] Total products (WHERE is_active=1 AND user_id = ?)
  - [x] Total stock (tenant-filtered join + SUM/COALESCE)
  - [x] Recent movements (tenant-filtered ORDER BY/LIMIT)
- [x] Keep UI variables intact so dashboard renders correctly.

- [x] Quick manual test by logging in as different users and verifying counts/movements differ.


