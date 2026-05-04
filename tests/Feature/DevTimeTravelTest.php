<?php

namespace Tests\Feature;

use App\Http\Middleware\ApplySimulatedTime;
use App\Models\Angsuran;
use App\Models\JenisCicilan;
use App\Models\JenisMotor;
use App\Models\Kredit;
use App\Models\MetodeBayar;
use App\Models\Motor;
use App\Models\Pelanggan;
use App\Models\PengajuanKredit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevTimeTravelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        ApplySimulatedTime::clear();
    }

    protected function tearDown(): void
    {
        ApplySimulatedTime::clear();

        parent::tearDown();
    }

    public function test_simulated_web_time_unlocks_h15_installment_button(): void
    {
        [$customer, $angsuran] = $this->customerAngsuran(now()->addDays(30));
        $admin = $this->admin();

        $this->actingAs($customer)
            ->get(route('angsuran.show', $angsuran))
            ->assertOk()
            ->assertSee('Bisa dibayar mulai');

        $this->actingAs($admin)
            ->post(route('dev.time-travel.store'), [
                'simulated_at' => now()->addDays(15)->startOfDay()->format('Y-m-d H:i:s'),
            ])
            ->assertRedirect(route('dev.time-travel.show'));

        $this->actingAs($customer)
            ->get(route('angsuran.show', $angsuran))
            ->assertOk()
            ->assertSee('Bayar via Midtrans');
    }

    public function test_only_admin_can_jump_to_next_payable_date_from_web(): void
    {
        [$customer, $angsuran] = $this->customerAngsuran(now()->addDays(30));
        $admin = $this->admin();

        $this->actingAs($customer)
            ->post(route('dev.time-travel.store'), ['preset' => 'next_payable'])
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('dev.time-travel.store'), ['preset' => 'next_payable'])
            ->assertRedirect(route('dev.time-travel.show'))
            ->assertSessionHas('success');

        $this->assertEquals(
            $angsuran->fresh()->payable_from->format('Y-m-d H:i:s'),
            ApplySimulatedTime::simulatedAt()?->format('Y-m-d H:i:s')
        );
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin Simulasi',
            'email' => 'admin-simulasi@example.test',
            'password' => 'password',
            'role' => 'admin',
            'no_telp' => '081234567891',
            'email_verified_at' => now(),
        ]);
    }

    private function customerAngsuran($dueDate): array
    {
        $customer = User::create([
            'name' => 'Customer Simulasi',
            'email' => 'simulasi@example.test',
            'password' => 'password',
            'role' => 'customer',
            'no_telp' => '081234567890',
            'email_verified_at' => now(),
        ]);

        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Customer Simulasi',
            'email' => $customer->email,
            'no_ktp' => '3175010101010001',
            'no_telp' => '081234567890',
            'alamat' => 'Alamat test',
        ]);

        $jenisMotor = JenisMotor::create([
            'merk' => 'Honda',
            'jenis' => 'Skuter',
        ]);

        $motor = Motor::create([
            'nama_motor' => 'Honda Beat',
            'id_jenis' => $jenisMotor->id,
            'harga_jual' => 18000000,
            'stok' => 1,
        ]);

        $jenisCicilan = JenisCicilan::create([
            'lama_cicilan' => 12,
            'margin_kredit' => 5,
        ]);

        $metodeBayar = MetodeBayar::create([
            'metode_pembayaran' => 'Transfer Bank',
            'tempat_bayar' => 'BCA',
            'no_rekening' => '1234567890',
        ]);

        $pengajuan = PengajuanKredit::create([
            'tgl_pengajuan_kredit' => now(),
            'id_pelanggan' => $pelanggan->id,
            'created_by' => $customer->id,
            'id_motor' => $motor->id,
            'harga_cash' => 18000000,
            'dp' => 3000000,
            'id_jenis_cicilan' => $jenisCicilan->id,
            'id_metode_bayar' => $metodeBayar->id,
            'harga_kredit' => 15750000,
            'cicilan_perbulan' => 1312500,
            'status_pengajuan' => 'Disetujui',
            'dp_payment_status' => 'Lunas',
            'dp_paid_at' => now(),
        ]);

        $kredit = Kredit::create([
            'id_pengajuan_kredit' => $pengajuan->id,
            'id_metode_bayar' => $metodeBayar->id,
            'tgl_mulai_kredit' => now(),
            'tgl_selesai_kredit' => now()->addYear(),
            'sisa_kredit' => 15750000,
            'status_kredit' => 'Dicicil',
        ]);

        $angsuran = Angsuran::create([
            'id_kredit' => $kredit->id,
            'angsuran_ke' => 1,
            'tgl_jatuh_tempo' => $dueDate,
            'total_bayar' => 1312500,
            'status' => 'Belum Bayar',
        ]);

        return [$customer, $angsuran];
    }
}
