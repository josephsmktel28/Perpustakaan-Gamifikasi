# Sistem Poin Peminjaman Buku

## Deskripsi
Sistem gamifikasi yang memberikan poin kepada pengguna berdasarkan kategori buku yang dipinjam. Poin berbeda diberikan untuk jenis buku yang berbeda untuk mendorong keberagaman membaca.

## Tabel Poin Peminjaman

| Kategori | Poin | Deskripsi |
|----------|------|-----------|
| **Fiksi** | 2 poin | Buku fiksi (novel, cerita pendek, dll) |
| **Non-Fiksi** | 5 poin | Buku non-fiksi (biografi, sains, sejarah, dll) |

## Tambahan Poin

| Aktivitas | Poin | Deskripsi |
|-----------|------|-----------|
| Pengembalian Buku + Review/Rating | +20 poin | Bonus poin saat mengembalikan buku dengan review atau rating |
| Challenge Completion | Bervariasi | Poin tambahan dari menyelesaikan tantangan |
| Streak Reward | Bervariasi | Poin dari streak harian membaca |

## Implementasi

### File yang Dimodifikasi
1. **user/pinjam.php** - Proses peminjaman dari halaman daftar buku
2. **user/detail_buku.php** - Proses peminjaman dari halaman detail buku

### Logika Perhitungan
Sistem akan otomatis:
1. Mengambil kategori buku saat pengguna meminjam
2. Menentukan poin berdasarkan kategori:
   - Jika kategori = "Fiksi" → +2 poin
   - Jika kategori = "Non-Fiksi" atau "Nonfiksi" → +5 poin
   - Kategori lain → +5 poin (default)
3. Menambahkan poin ke akun pengguna
4. Menampilkan notifikasi dengan jumlah poin dan kategori yang diterima

## Database Schema

Kolom yang digunakan:
- **buku.kategori** - Menyimpan kategori buku (VARCHAR 50)
- **users.points** - Menyimpan total poin pengguna (INT)
- **peminjaman** - Menyimpan data peminjaman

## Contoh Skenario

**Skenario 1: User meminjam buku Fiksi**
- Judul: "Harry Potter"
- Kategori: Fiksi
- Poin diterima: +2 poin
- Pesan: "Pinjam berhasil! +2 poin (Kategori: Fiksi)"

**Skenario 2: User meminjam buku Non-Fiksi**
- Judul: "Sapiens"
- Kategori: Non-Fiksi
- Poin diterima: +5 poin
- Pesan: "Pinjam berhasil! +5 poin (Kategori: Non-Fiksi)"

## Level Pengguna

Level pengguna bergantung pada total poin yang dikumpulkan:
- **🥉 Beginner** - 0-49 poin
- **🥈 Intermediate** - 50-99 poin
- **🥇 Book Lover** - 100-199 poin
- **🏆 Master Reader** - 200+ poin

## Catatan Teknis

- Sistem case-insensitive untuk kategori
- Kategori baru dapat ditambahkan di masa depan dengan menyesuaikan logika di kedua file
- Poin tidak dapat dikurangi melalui peminjaman, hanya bertambah
- Sistem bonus poin untuk pengembalian dan challenge tetap berlaku
