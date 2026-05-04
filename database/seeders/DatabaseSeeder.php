<?php

namespace Database\Seeders;

use App\Models\Angsuran;
use App\Models\Asuransi;
use App\Models\JenisCicilan;
use App\Models\JenisMotor;
use App\Models\Kredit;
use App\Models\MetodeBayar;
use App\Models\Motor;
use App\Models\Pelanggan;
use App\Models\PengajuanKredit;
use App\Models\Pengiriman;
use App\Models\ShippingDestination;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin Kremo',
            'email' => 'admin@kremo.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'no_telp' => '081234567890',
            'email_verified_at' => Carbon::now(),
        ]);

        $marketing = User::create([
            'name' => 'Budi Marketing',
            'email' => 'marketing@kremo.test',
            'password' => Hash::make('password'),
            'role' => 'marketing',
            'no_telp' => '081234567891',
            'email_verified_at' => Carbon::now(),
        ]);

        $surveyor = User::create([
            'name' => 'Adi Surveyor',
            'email' => 'surveyor@kremo.test',
            'password' => Hash::make('password'),
            'role' => 'surveyor',
            'no_telp' => '081234567892',
            'email_verified_at' => Carbon::now(),
        ]);

        $approver = User::create([
            'name' => 'Siti Approver',
            'email' => 'approver@kremo.test',
            'password' => Hash::make('password'),
            'role' => 'approver',
            'no_telp' => '081234567893',
            'email_verified_at' => Carbon::now(),
        ]);

        $customer = User::create([
            'name' => 'Rudi Customer',
            'email' => 'customer@kremo.test',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'no_telp' => '081234567894',
            'email_verified_at' => Carbon::now(),
        ]);

        // === JENIS MOTOR ===
        $jenisBebek = JenisMotor::create([
            'merk' => 'Honda', 'jenis' => 'Bebek',
            'deskripsi_jenis' => 'Motor bebek hemat bahan bakar',
        ]);
        $jenisSkuter = JenisMotor::create([
            'merk' => 'Yamaha', 'jenis' => 'Skuter',
            'deskripsi_jenis' => 'Motor matic untuk perkotaan',
        ]);
        $jenisSport = JenisMotor::create([
            'merk' => 'Kawasaki', 'jenis' => 'Sport Bike',
            'deskripsi_jenis' => 'Motor sport bertenaga tinggi',
        ]);
        $jenisAdventure = JenisMotor::create([
            'merk' => 'Honda', 'jenis' => 'Motor Adventure',
            'deskripsi_jenis' => 'Motor adventure untuk segala medan',
        ]);

        // === MOTOR ===
        $motor1 = Motor::create([
            'nama_motor' => 'Honda Supra X 125',
            'id_jenis' => $jenisBebek->id,
            'harga_jual' => 18500000,
            'deskripsi_motor' => 'Motor bebek harian yang irit, ringan, dan mudah dirawat untuk mobilitas keluarga maupun kerja.',
            'warna' => 'Merah',
            'kapasitas_mesin' => '125cc',
            'tahun_produksi' => 2024,
            'berat_gram' => 105000,
            'stok' => 15,
        ]);
        $motor2 = Motor::create([
            'nama_motor' => 'Yamaha NMAX 155',
            'id_jenis' => $jenisSkuter->id,
            'harga_jual' => 32000000,
            'deskripsi_motor' => 'Skutik premium dengan posisi berkendara nyaman, bagasi lega, dan performa stabil untuk komuter jarak jauh.',
            'warna' => 'Hitam',
            'kapasitas_mesin' => '155cc',
            'tahun_produksi' => 2024,
            'berat_gram' => 132000,
            'stok' => 10,
        ]);
        $motor3 = Motor::create([
            'nama_motor' => 'Kawasaki Ninja ZX-25R',
            'id_jenis' => $jenisSport->id,
            'harga_jual' => 96000000,
            'deskripsi_motor' => 'Sport bike 250cc untuk pengguna yang menginginkan karakter mesin responsif dan tampilan agresif.',
            'warna' => 'Hijau',
            'kapasitas_mesin' => '250cc',
            'tahun_produksi' => 2024,
            'berat_gram' => 182000,
            'stok' => 5,
        ]);
        Motor::create([
            'nama_motor' => 'Honda CRF250 Rally',
            'id_jenis' => $jenisAdventure->id,
            'harga_jual' => 74000000,
            'deskripsi_motor' => 'Adventure bike tangguh dengan ground clearance tinggi untuk perjalanan touring dan lintasan campuran.',
            'warna' => 'Putih Merah',
            'kapasitas_mesin' => '250cc',
            'tahun_produksi' => 2024,
            'berat_gram' => 152000,
            'stok' => 8,
        ]);
        Motor::create([
            'nama_motor' => 'Honda Beat',
            'id_jenis' => $jenisSkuter->id,
            'harga_jual' => 17500000,
            'deskripsi_motor' => 'Motor matic compact yang hemat bahan bakar, gesit di jalan kota, dan cocok untuk penggunaan harian.',
            'warna' => 'Biru',
            'kapasitas_mesin' => '110cc',
            'tahun_produksi' => 2024,
            'berat_gram' => 90000,
            'stok' => 20,
        ]);
        Motor::create([
            'nama_motor' => 'Yamaha Aerox 155',
            'id_jenis' => $jenisSkuter->id,
            'harga_jual' => 31000000,
            'deskripsi_motor' => 'Skutik sporty dengan bodi agresif, performa 155cc, dan karakter berkendara lincah untuk harian maupun touring ringan.',
            'warna' => 'Abu-abu Kuning',
            'kapasitas_mesin' => '155cc',
            'tahun_produksi' => 2024,
            'berat_gram' => 125000,
            'stok' => 12,
        ]);

        // === JENIS CICILAN ===
        $cicilan12 = JenisCicilan::create(['lama_cicilan' => 12, 'margin_kredit' => 5.00]);
        $cicilan24 = JenisCicilan::create(['lama_cicilan' => 24, 'margin_kredit' => 8.00]);
        JenisCicilan::create(['lama_cicilan' => 36, 'margin_kredit' => 12.00]);
        JenisCicilan::create(['lama_cicilan' => 48, 'margin_kredit' => 16.00]);

        // === ASURANSI ===
        $asuransi1 = Asuransi::create([
            'nama_perusahaan_asuransi' => 'Adira Insurance',
            'nama_asuransi' => 'Adira Comprehensive',
            'margin_asuransi' => 2.50,
            'no_rekening' => '1234567890',
        ]);
        Asuransi::create([
            'nama_perusahaan_asuransi' => 'ACA Asuransi',
            'nama_asuransi' => 'ACA Motor Plus',
            'margin_asuransi' => 2.00,
            'no_rekening' => '0987654321',
        ]);
        Asuransi::create([
            'nama_perusahaan_asuransi' => 'Sinarmas MSIG',
            'nama_asuransi' => 'Simas Motor',
            'margin_asuransi' => 3.00,
            'no_rekening' => '1122334455',
        ]);

        // === METODE BAYAR ===
        $metodeBCA = MetodeBayar::create([
            'metode_pembayaran' => 'Transfer Bank',
            'tempat_bayar' => 'BCA',
            'no_rekening' => '123-456-7890',
        ]);
        MetodeBayar::create([
            'metode_pembayaran' => 'Transfer Bank',
            'tempat_bayar' => 'BRI',
            'no_rekening' => '098-765-4321',
        ]);
        MetodeBayar::create([
            'metode_pembayaran' => 'Transfer Bank',
            'tempat_bayar' => 'Mandiri',
            'no_rekening' => '112-233-4455',
        ]);
        MetodeBayar::create([
            'metode_pembayaran' => 'Cash',
            'tempat_bayar' => 'Kantor Dealer',
            'no_rekening' => null,
        ]);

        // === SAMPLE DESTINATION CACHE UNTUK FALLBACK RAJAONGKIR ===
        collect([
            [900001, 'Kebayoran Baru, Jakarta Selatan, DKI Jakarta 12110', 'DKI Jakarta', 'Jakarta Selatan', 'Kebayoran Baru', 'Gandaria Utara', '12110'],
            [900002, 'Cicendo, Kota Bandung, Jawa Barat 40171', 'Jawa Barat', 'Kota Bandung', 'Cicendo', 'Pajajaran', '40171'],
            [900003, 'Wonokromo, Surabaya, Jawa Timur 60243', 'Jawa Timur', 'Kota Surabaya', 'Wonokromo', 'Darmo', '60243'],
            [900004, 'Depok, Sleman, DI Yogyakarta 55281', 'DI Yogyakarta', 'Kabupaten Sleman', 'Depok', 'Caturtunggal', '55281'],
            [900005, 'Denpasar Selatan, Denpasar, Bali 80222', 'Bali', 'Kota Denpasar', 'Denpasar Selatan', 'Sanur', '80222'],
        ])->each(function (array $destination) {
            ShippingDestination::create([
                'id' => $destination[0],
                'label' => $destination[1],
                'province_name' => $destination[2],
                'city_name' => $destination[3],
                'district_name' => $destination[4],
                'subdistrict_name' => $destination[5],
                'zip_code' => $destination[6],
                'last_seen_at' => now(),
            ]);
        });

        // === PELANGGAN ===
        // Pelanggan akan terbuat otomatis saat customer melakukan pengajuan pertama kali.


        // === PENGAJUAN KREDIT (Dikosongkan agar customer belum pesan apapun) ===
        // Silakan buat pengajuan baru melalui aplikasi sebagai customer.
    }
}
