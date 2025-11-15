# HBM Billing Manager (Lightweight Demo)

Proyek ini adalah versi ringan dari aplikasi billing yang bisa dijalankan tanpa perlu mengunduh dependensi Laravel maupun menjalankan Composer. Seluruh data disimpan di berkas JSON sehingga instalasi dan percobaan dapat dilakukan hanya dengan PHP standar.

## Cara Menjalankan

1. Pastikan PHP 8.2 atau lebih baru telah terpasang.
2. Jalankan server bawaan PHP dari root proyek:

   ```bash
   php -S localhost:8000 -t public
   ```

3. Buka `http://localhost:8000` di browser untuk melihat dashboard sederhana dan daftar endpoint API.

Endpoint API yang tersedia:

- `GET /api/status`
- `GET /api/clients`
- `GET /api/products`
- `GET /api/subscriptions`
- `GET /api/invoices`
- `POST /api/invoices`

Contoh payload `POST /api/invoices`:

```json
{
  "client_id": 2,
  "line_items": [
    { "product_id": 1, "quantity": 2 },
    { "product_id": 3, "quantity": 1 }
  ]
}
```

## Pengujian

Script pengujian sederhana tersedia pada `tests/run.php`.

```bash
php tests/run.php
```

Script tersebut memverifikasi perhitungan invoice, jumlah data, serta proses penambahan invoice baru pada penyimpanan JSON.
