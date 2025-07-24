# 📦 Laravel Inventory Management System

Sistem Manajemen Inventaris Barang berbasis Laravel 11 dengan fitur CRUD lengkap, audit trail, manajemen akses (role & permission), dan kemampuan ekspor-impor data Excel.

---

## 🧩 Fitur Utama

- **Autentikasi & Autorizasi**
  - Login berbasis JWT dengan `tymon/jwt-auth`
  - Role dan Permission menggunakan `spatie/laravel-permission`

- **Manajemen Inventaris**
  - Kategori barang (`categories`)
  - Barang (`items`)
  - Transaksi keluar/masuk barang (`item_transactions`)
  - Lampiran file (hanya PDF, 100–500 KB)
  - UUID sebagai primary key untuk semua entitas
  - Soft deletes dan field boolean aktif/nonaktif

- **Audit Trail**
  - Tracking semua perubahan data menggunakan `owen-it/laravel-auditing`
  - Menyimpan nilai lama dan baru, user, IP address, dan user agent

- **Ekspor & Impor**
  - Menggunakan `maatwebsite/excel` untuk ekspor/impor data barang

- **UI/UX**
  - Select2 untuk pilihan data relasional
  - Filtering, searching, dan sorting

---

## 📁 Struktur Tabel

### users
| Field         | Tipe     | Keterangan               |
|---------------|----------|--------------------------|
| id            | UUID     | Primary Key              |
| name, email   | String   | Informasi pengguna       |
| password      | String   |                         |
| is_active     | Boolean  | Status aktif             |
| softDeletes   | Datetime | Untuk penghapusan lembut |

### categories
| Field       | Tipe    | Keterangan                |
|-------------|---------|---------------------------|
| id          | UUID    | Primary Key               |
| name        | String  | Nama kategori             |
| metadata    | JSON    | Informasi tambahan        |
| is_active   | Boolean | Status aktif              |

### items
| Field       | Tipe    | Keterangan                   |
|-------------|---------|------------------------------|
| id          | UUID    |                              |
| category_id | UUID    | Relasi ke categories         |
| specs       | JSON    | Spesifikasi barang           |
| file_path   | String  | Path file PDF                |

### item_transactions
| Field        | Tipe   | Keterangan                     |
|--------------|--------|--------------------------------|
| id           | UUID   |                                |
| item_id      | UUID   | Barang yang dimutasi           |
| type         | Enum   | 'in' atau 'out'                |
| attachment   | String | File PDF (bukti transaksi)     |
| is_verified  | Bool   | Status verifikasi              |

### audits
| Field            | Tipe   | Keterangan                    |
|------------------|--------|-------------------------------|
| auditable_id     | UUID   | ID data yang diubah           |
| auditable_type   | String | Nama model yang diubah        |
| event            | String | Jenis perubahan (created/...) |
| old_values       | JSON   | Data sebelum                  |
| new_values       | JSON   | Data sesudah                  |

---

## 🔌 Package yang Digunakan

| Package                          | Fungsi                            |
|----------------------------------|-----------------------------------|
| `spatie/laravel-permission`      | Role & permission berbasis DB     |
| `tymon/jwt-auth`                 | Autentikasi berbasis token JWT    |
| `owen-it/laravel-auditing`       | Pencatatan log perubahan          |
| `maatwebsite/excel`              | Ekspor dan impor Excel            |

---

## ⚙️ Setup Project

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan jwt:secret
php artisan storage:link
