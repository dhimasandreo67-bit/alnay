<?php

/*
|--------------------------------------------------------------------------
| Web Routes — ALNAY.ID
|--------------------------------------------------------------------------
|
| File ini HANYA mengumpulkan file route per area.
| JANGAN tambahkan route langsung di sini.
|
| Tambahkan di file yang sesuai:
|   public.php    → halaman publik (customer, tanpa login)
|   auth.php      → login, register, logout
|   customer.php  → area akun pelanggan (butuh login)
|   admin.php     → area admin (butuh login)
|   owner.php     → area owner (butuh login)
|
| Sistem Event & Voting (event.alnay.id) TERPISAH TOTAL.
| Jangan buat route, query, atau model event di sini.
|
*/

require __DIR__.'/public.php';
require __DIR__.'/auth.php';
require __DIR__.'/customer.php';
require __DIR__.'/admin.php';
require __DIR__.'/owner.php';

