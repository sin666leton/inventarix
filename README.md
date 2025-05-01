# Inventarix

**Inventarix** adalah aplikasi manajemen inventaris berbasis RESTful API yang dibangun dengan Laravel. Aplikasi ini membantu pengguna untuk mencatat, melacak, dan mengelola barang/aset secara efisien dan terstruktur, serta dirancang dengan arsitektur bersih dan maintainable.

## Fitur Utama

- Manajemen **Kategori** barang
- Manajemen **Item**/aset
- Sistem **Autentikasi** (Login, Register)
- Manajemen **Staff**
- Transaksi **masuk/keluar** barang
- API **RESTful** siap integrasi

## Arsitektur & Pendekatan

- **Repository Pattern** untuk pemisahan antara logic dan data access
- **Service Layer** untuk menjaga logika bisnis tetap modular
- Struktur kode **clean**, mudah diuji dan dikembangkan
- Siap untuk implementasi unit test dan integrasi test

## Teknologi

- **Laravel** 12
- **PHP** 8.1+
- **MySQL** / **PostgreSQL**
- **Sanctum**

## Instalasi

```bash
git clone https://github.com/username/inventarix.git
cd inventarix
composer install
cp .env.example .env
php artisan key:generate
# WARNING: Ini akan menghapus seluruh data di database!
php artisan migrate:fresh --seed
php artisan serve
