# Aplikasi To-Do List

## Deskripsi
Aplikasi sederhana berbasis PHP untuk mencatat tugas harian, khususnya kegiatan rumah dan ibadah. Tampilan dibuat dengan Bootstrap 5 bertema pink, dan data disimpan menggunakan session PHP sehingga tidak memerlukan database.

## Fitur
- Tambah tugas
- Tandai tugas selesai (dan batalkan kembali)
- Hapus tugas (dengan konfirmasi)
- Filter tugas: Semua, Proses, Selesai
- Progress bar persentase penyelesaian

## Struktur Folder
- `index.php` – halaman utama (logika PHP, HTML, dan CSS dalam satu file)
- `README.md` – dokumentasi proyek
- `assets/` – tidak digunakan, karena CSS ditulis langsung di dalam `index.php` dan Bootstrap dimuat lewat CDN

## Cara Menjalankan
1. Salin folder `todolist` ke dalam `htdocs/` (folder instalasi XAMPP)
2. Jalankan XAMPP (aktifkan modul **Apache**), lalu buka di `http://localhost/todolist`

> Koneksi internet diperlukan agar Bootstrap dan font Nunito dapat dimuat dari CDN.

## Catatan
Data tugas disimpan di `$_SESSION`, sehingga akan kembali ke daftar awal jika session berakhir atau browser ditutup. Pengembangan selanjutnya dapat menggunakan database MySQL dan fitur login.

## Kontributor
- [Nama Anda](https://github.com/username)