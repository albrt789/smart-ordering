# SUAP. — Smart Dine-In QR Ordering System (Laravel, Blade & MySQL) 🍽️📱

Projek Smart Ordering Restoran (Pemesanan Meja Pintar & Pembayaran QRIS) berbasis **Laravel 13**, **Blade Template**, **PHP 8.3**, dan database **MySQL** yang berjalan langsung di **Laragon**. Desain UI/UX dibuat 100% presisi dan identik sesuai dengan desain aplikasi **SUAP.** (Suap Cikini).

---

## 🚀 Akses Aplikasi di Browser (Laragon)

Pastikan server Laragon (**Apache** dan **MySQL**) aktif. Buka browser di:
- **Customer App (Dine-in Menu)**: [http://localhost/qr/](http://localhost/qr/) atau [http://localhost/qr/public/](http://localhost/qr/public/)
- **Berdasarkan Nomor Meja**: [http://localhost/qr/public/?meja=08](http://localhost/qr/public/?meja=08)
- **Layar Dapur (Kitchen Display System / KDS)**: [http://localhost/qr/public/kitchen](http://localhost/qr/public/kitchen)
- **Cetak Kode QR Meja (Tent Card)**: [http://localhost/qr/public/tables](http://localhost/qr/public/tables)

---

## 🗄️ Konfigurasi Database MySQL (Laragon)

Database MySQL telah dibuat dan termigrasi secara otomatis:
- **Connection**: `mysql`
- **Host**: `127.0.0.1`
- **Port**: `3306`
- **Database**: `qr`
- **Username**: `root`
- **Password**: *(kosong)*

### Tabel Database MySQL yang Tersedia:
1. `menu_items`: Data hidangan makanan, camilan, minuman, harga, foto, tag, info alergen, dan opsi modifier (pilih nasi, sambal, saus, dll).
2. `orders`: Riwayat pesanan meja, nomor invoice (`#SP-0248`), status pesanan (*paid, cooking, delivering, completed*), item yang dipesan, rincian pajak PBJT & service fee.
3. `ai_messages`: Riwayat chat konsultasi bahan & alergi dengan asisten Suap AI.

Anda dapat melihat atau mengelola tabel-tabel ini kapan saja melalui **HeidiSQL** atau **phpMyAdmin** bawaan Laragon (pilih database: `qr`).

---

## 🤖 Konfigurasi AI Chat (Groq)

Fitur **Suap AI** menggunakan [Groq API](https://console.groq.com) dengan model `openai/gpt-oss-120b`. Tambahkan konfigurasi berikut di file `.env`:

```env
AI_PROVIDER=groq
AI_MODEL=openai/gpt-oss-120b
GROQ_API_KEY=gsk_xxxxxxxxxxxxxxxxxxxxxxxx
AI_BASE_URL=https://api.groq.com/openai/v1
AI_TIMEOUT=20
AI_REASONING_EFFORT=low
```

Langkah singkat:
1. Buat API key di [Groq Console → API Keys](https://console.groq.com/keys).
2. Salin `.env.example` menjadi `.env` jika belum ada, lalu isi `GROQ_API_KEY`.
3. Jalankan `php artisan config:clear` setelah mengubah `.env`.

Catatan:
- Jika `GROQ_API_KEY` kosong atau request ke Groq gagal, chat otomatis memakai **fallback engine lokal** berbasis data menu di database, jadi fitur tetap bisa dipakai.
- Provider lain yang kompatibel dengan OpenAI (misalnya OpenRouter) masih bisa dipakai dengan mengubah `AI_PROVIDER`, `AI_BASE_URL`, dan `AI_MODEL`. Untuk OpenRouter, isi juga `AI_API_KEY`.

---

## 🎨 Tampilan Layar (Blade Views)

1. **`resources/views/menu/index.blade.php` (Layar 1: Menu Utama)**:
   - Header cerdas dengan nomor meja otomatis (`[ Meja 08 ]`), status koneksi (`● Terhubung`), dan tombol KDS dapur.
   - Hero banner gelap: *"RASA LOKAL. MOOD MAKSIMAL. Enaknya, langsung suap. Pilih, bayar, kami antar ke meja 08."*
   - Pencarian menu real-time dan filter kategori (*Semua, Makanan, Camilan, Minuman*).
   - Grid menu 2-kolom (*Ayam Sambal Matah, Jamur Crispy, Es Teh Leci, Mie Ayam Suap*), tag hidangan, foto resolusi tinggi, harga, dan kontrol stepper `1 +`.
   - Banner integrasi Suap AI.
   - Floating sticky cart bar: *"Lihat keranjang · 3 menu | Rp88.000"*.

2. **`resources/views/menu/show.blade.php` (Layar 2: Detail Makanan)**:
   - Header kembali `← Detail menu` & nomor meja.
   - Foto piring hidangan dengan badge `FAVORIT SUAP`.
   - Kategori `MAKANAN / SIGNATURE`, judul, harga, dan chips info (🌶️ *Pedas sedang*, 🍱 *1 porsi*, ⏱️ *15-20 menit*).
   - **Kustomisasi Wajib**:
     - *Pilih nasi (WAJIB · PILIH 1)*: Nasi putih (Termasuk) / Nasi merah (+Rp4.000).
     - *Sambalnya gimana? (WAJIB · PILIH 1)*: Sambal dicampur / Sambal dipisah.
   - Form catatan dapur (dengan penghitung karakter `27/150`).
   - Kotak peringatan alergen bahan makanan.
   - Stepper jumlah porsi & tombol oranye *"Simpan ke keranjang | Rp42.000"*.

3. **`resources/views/ai/chat.blade.php` (Layar 3: Chat Suap AI)**:
   - Header `← Suap AI` & nomor meja.
   - Card sambutan *"Tanya dulu, suap kemudian."* dan quick prompt (*Cek alergi, Status pesanan, Camilan nabati, Menu pedas*).
   - Form kirim pesan interaktif.
   - Peringatan alergen dan **Kartu Produk Tersemat** (*Jamur Crispy*) dengan stepper kuantitas langsung di dalam obrolan chat!
   - Card konfirmasi keranjang siap saji.

4. **`resources/views/cart/index.blade.php` (Layar 4: Keranjang Pesanan)**:
   - Headline *"Sudah pas seleramu? Cek pilihanmu sebelum lanjut bayar."*
   - Info restoran & meja: *Suap, Cikini · Meja 08*.
   - Daftar item di keranjang dengan ringkasan opsi, catatan, dan stepper kuantitas.
   - Tombol `+ Tambah menu lain`.
   - **Ringkasan Pembayaran**:
     - Subtotal (3 menu): Rp88.000
     - Biaya layanan (5%): Rp4.400
     - PBJT (10% dari subtotal): Rp8.800
     - **Total pembayaran: Rp101.200**
   - Tombol oranye: *"Lanjut ke pembayaran →"*.

5. **`resources/views/checkout/index.blade.php` (Layar 5: Checkout & Pembayaran QRIS)**:
   - Headline *"Bayar beres. Tinggal duduk manis."*
   - Input nama pemesan (*Dina*).
   - Accordion ringkasan pesanan & rincian biaya.
   - Pilihan metode pembayaran (QRIS, E-Wallet, VA, Card).
   - **Kotak Pembayaran QRIS Dinamis**:
     - Timer hitung mundur: `04:59`.
     - Merchant & invoice: `SUAP CIKINI - #SP-0248`.
     - Nominal: `Rp101.200`.
     - **Kode QRIS Real Scannable** + tombol *Unduh QR*.
   - Tombol *"Cek status pembayaran"* dengan efek selebrasi confetti 🎉!

6. **`resources/views/orders/status.blade.php` (Layar 6: Konfirmasi & Status Pesanan)**:
   - Hero status card gelap: Badge hijau *Pembayaran berhasil*, *"Dapur mulai beraksi."*, pesan *"Makasih, Dina!"*, `#SP-0248`, `Meja 08`.
   - ETA card: ⏱️ *PERKIRAAN TIBA DI MEJA: 12.50–12.55*.
   - **Timeline Status 4-Tahap**:
     1. ✅ Pembayaran diterima (12.31) — *via QRIS*
     2. 🔥 Sedang dimasak (12.33) — *Chef sedang memasak*
     3. 🚲 Siap diantar — *Menuju meja*
     4. 🍽️ Selamat menikmati! — *Tiba di meja*
   - Struk digital detail dan tombol *Simpan struk*.

7. **`resources/views/kitchen/index.blade.php` (Layar Dapur / KDS)**:
   - Layar khusus staf koki/dapur untuk memantau pesanan meja yang masuk secara langsung.
   - Tombol memajukan status pesanan (*Mulai Masak ➔ Siap Diantar ➔ Selesai*) yang **secara otomatis mengubah status di layar pelanggan**!

8. **`resources/views/tables/index.blade.php` (Generator & Cetak QR Meja)**:
   - Menghasilkan kartu meja tent card dengan kode QR unik (Meja 01 s/d Meja 12) yang siap dicetak untuk diletakkan di setiap meja restoran.

---

## 🛠️ Perintah Berguna Artisan
- **Migrasi Ulang & Isi Data Awal di MySQL**:
  ```bash
  php artisan migrate:fresh --seed
  ```
- **Cek Status Rute**:
  ```bash
  php artisan route:list
  ```
