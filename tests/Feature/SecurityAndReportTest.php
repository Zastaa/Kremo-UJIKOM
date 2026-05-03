<?php

namespace Tests\Feature;

use App\Http\Requests\StorePengajuanKreditRequest;
use App\Models\Angsuran;
use App\Models\JenisCicilan;
use App\Models\JenisMotor;
use App\Models\Kredit;
use App\Models\MetodeBayar;
use App\Models\Motor;
use App\Models\Pelanggan;
use App\Models\Pengiriman;
use App\Models\PengajuanKredit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityAndReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_access_another_customers_financing_records(): void
    {
        $customer = $this->user('customer', 'customer-a@example.test');
        [$pengajuan, $kredit, $angsuran] = $this->financingFor('customer-b@example.test');

        $this->actingAs($customer);

        $this->get(route('pengajuan.show', $pengajuan))->assertForbidden();
        $this->get(route('kredit.show', $kredit))->assertForbidden();
        $this->get(route('angsuran.show', $angsuran))->assertForbidden();
        $this->get(route('payment.pay', $angsuran))->assertForbidden();
        $this->get(route('payment.sync', $angsuran))->assertForbidden();
    }

    public function test_customer_can_access_their_own_financing_records(): void
    {
        $customer = $this->user('customer', 'owner@example.test');
        [$pengajuan, $kredit, $angsuran] = $this->financingFor($customer->email);

        $this->actingAs($customer);

        $this->get(route('pengajuan.show', $pengajuan))->assertOk();
        $this->get(route('kredit.show', $kredit))->assertOk();
        $this->get(route('angsuran.show', $angsuran))->assertOk();
    }

    public function test_customer_installment_payment_is_locked_until_dp_paid_and_h15_window(): void
    {
        $customer = $this->user('customer', 'installment-lock@example.test');
        [$pengajuan, $kredit, $angsuran] = $this->financingFor($customer->email);

        $this->actingAs($customer)
            ->get(route('payment.pay', $angsuran))
            ->assertRedirect(route('angsuran.show', $angsuran))
            ->assertSessionHas('error', 'Selesaikan pembayaran DP dan ongkir terlebih dahulu.');

        $pengajuan->update(['dp_payment_status' => 'Lunas']);
        $angsuran->update(['tgl_jatuh_tempo' => now()->addDays(20)]);

        $this->actingAs($customer)
            ->get(route('payment.pay', $angsuran->fresh()))
            ->assertRedirect(route('angsuran.show', $angsuran))
            ->assertSessionHas('error', 'Bisa dibayar mulai ' . now()->addDays(5)->format('d/m/Y') . '.');

        $angsuran->update(['tgl_jatuh_tempo' => now()->addDays(15)]);
        $this->assertTrue($angsuran->fresh()->is_customer_payable);
    }

    public function test_shipping_create_only_lists_approved_pengajuan_after_dp_and_ongkir_paid(): void
    {
        $admin = $this->user('admin', 'shipping-admin@example.test');
        [$pengajuan] = $this->financingFor('shipping-customer@example.test');

        $this->actingAs($admin)
            ->get(route('pengiriman.create'))
            ->assertOk()
            ->assertDontSee('#' . $pengajuan->id . ' - Customer Test', false);

        $pengajuan->update([
            'dp_payment_status' => 'Lunas',
            'shipping_origin_destination_id' => 1,
            'shipping_origin_label' => 'Dealer Kremo',
            'shipping_destination_destination_id' => 2,
            'shipping_destination_label' => 'Jakarta Selatan',
            'shipping_package_weight' => 125000,
            'shipping_courier_code' => 'jne',
            'shipping_courier_service' => 'REG',
            'shipping_cost' => 300000,
            'shipping_etd' => '2-4 hari',
        ]);

        $this->actingAs($admin)
            ->get(route('pengiriman.create'))
            ->assertOk()
            ->assertSee('#' . $pengajuan->id . ' - Customer Test', false)
            ->assertSee('Ongkir lunas');
    }

    public function test_customer_confirms_delivery_with_photo_and_admin_verifies_it(): void
    {
        Storage::fake('public');

        $customer = $this->user('customer', 'delivery-customer@example.test');
        $admin = $this->user('admin', 'delivery-admin@example.test');
        [$pengajuan] = $this->financingFor($customer->email);
        $pengajuan->update([
            'status_pengajuan' => 'Disetujui',
            'dp_payment_status' => 'Lunas',
        ]);

        $pengiriman = Pengiriman::create([
            'id_pengajuan_kredit' => $pengajuan->id,
            'no_invoice' => 'INV-TEST-001',
            'tgl_kirim' => now(),
            'status_kirim' => 'Sedang Dikirim',
            'receiver_name' => 'Customer Test',
            'receiver_phone' => '081234567890',
            'receiver_address' => 'Alamat test',
            'courier_code' => 'jne',
            'courier_service' => 'REG',
            'shipping_cost' => 300000,
        ]);

        $this->actingAs($customer)
            ->post(route('pengiriman.confirmReceived', $pengiriman), [
                'customer_received_photo' => UploadedFile::fake()->image('bukti.jpg')->size(300),
                'customer_received_note' => 'Motor sudah diterima.',
            ])
            ->assertRedirect();

        $pengiriman->refresh();
        $this->assertSame(Pengiriman::VERIFICATION_PENDING, $pengiriman->delivery_verification_status);
        $this->assertSame('Sedang Dikirim', $pengiriman->status_kirim);
        $this->assertNotNull($pengiriman->customer_received_at);
        Storage::disk('public')->assertExists($pengiriman->customer_received_photo);

        $this->actingAs($admin)
            ->post(route('pengiriman.verifyReceived', $pengiriman), [
                'delivery_verification_note' => 'Bukti valid.',
            ])
            ->assertRedirect(route('pengiriman.show', $pengiriman));

        $pengiriman->refresh();
        $pengajuan->refresh();

        $this->assertSame(Pengiriman::VERIFICATION_ACCEPTED, $pengiriman->delivery_verification_status);
        $this->assertSame('Tiba Di Tujuan', $pengiriman->status_kirim);
        $this->assertSame($admin->id, $pengiriman->delivery_verified_by);
        $this->assertSame('Diterima', $pengajuan->status_pengajuan);
    }

    public function test_report_pdf_export_returns_a_real_pdf(): void
    {
        $admin = $this->user('admin', 'admin@example.test');
        $this->financingFor('customer@example.test');

        $response = $this->actingAs($admin)->post(route('reports.export'), [
            'type' => 'order',
            'format' => 'pdf',
        ]);

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_user_performance_report_can_be_exported(): void
    {
        $admin = $this->user('admin', 'admin-performance@example.test');
        $this->user('marketing', 'marketing-performance@example.test');
        $this->user('surveyor', 'surveyor-performance@example.test');
        $this->user('approver', 'approver-performance@example.test');

        $this->actingAs($admin)
            ->get(route('reports.user-performance'))
            ->assertOk()
            ->assertSee('Panel Kinerja User Operasional')
            ->assertSee('Respons Per Role');

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Kinerja User Operasional')
            ->assertSee('Lihat Panel Detail');

        $pdf = $this->actingAs($admin)->post(route('reports.export'), [
            'type' => 'kinerja_user',
            'format' => 'pdf',
        ]);

        $pdf->assertOk();
        $this->assertStringContainsString('application/pdf', $pdf->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        $csv = $this->actingAs($admin)->post(route('reports.export'), [
            'type' => 'kinerja_user',
            'format' => 'csv',
        ]);

        $csv->assertOk();
        $this->assertStringContainsString('text/csv', $csv->headers->get('content-type'));

        $xlsx = $this->actingAs($admin)->post(route('reports.export'), [
            'type' => 'kinerja_user',
            'format' => 'xlsx',
        ]);

        $xlsx->assertOk();
    }

    public function test_pengajuan_document_size_validation_uses_clear_message(): void
    {
        $customer = $this->user('customer', 'upload-check@example.test');
        $this->profileFor($customer);

        $jenisMotor = JenisMotor::create([
            'merk' => 'Honda',
            'jenis' => 'Skuter',
            'deskripsi_jenis' => 'Motor matic',
        ]);

        $motor = Motor::create([
            'nama_motor' => 'Honda Upload Test',
            'id_jenis' => $jenisMotor->id,
            'harga_jual' => 18000000,
            'stok' => 3,
        ]);

        $tenor = JenisCicilan::create([
            'lama_cicilan' => 12,
            'margin_kredit' => 5,
        ]);
        $metodeBayar = $this->paymentMethod();

        $response = $this->actingAs($customer)
            ->from(route('pengajuan.create'))
            ->post(route('pengajuan.store'), [
                'nama_pelanggan' => 'Customer Upload',
                'no_ktp' => '1234567890123456',
                'no_telp' => '081234567890',
                'alamat' => 'Alamat upload',
                'id_motor' => $motor->id,
                'harga_cash' => 18000000,
                'dp' => 3600000,
                'id_jenis_cicilan' => $tenor->id,
                'id_metode_bayar' => $metodeBayar->id,
                'url_kk' => UploadedFile::fake()->create(
                    'kk.pdf',
                    StorePengajuanKreditRequest::DOCUMENT_MAX_KB + 1,
                    'application/pdf'
                ),
            ]);

        $response->assertRedirect(route('pengajuan.create'));
        $response->assertSessionHasErrors([
            'url_kk' => 'File KK maksimal 2 MB.',
        ]);
    }

    public function test_pengajuan_invoice_email_is_logged_after_customer_submission(): void
    {
        $customer = $this->user('customer', 'invoice-customer@example.test');
        $this->profileFor($customer, 'Customer Invoice');

        $jenisMotor = JenisMotor::create([
            'merk' => 'Honda',
            'jenis' => 'Skuter',
            'deskripsi_jenis' => 'Motor skuter',
        ]);

        $motor = Motor::create([
            'nama_motor' => 'Honda Invoice Test',
            'id_jenis' => $jenisMotor->id,
            'harga_jual' => 18000000,
            'stok' => 3,
        ]);

        $tenor = JenisCicilan::create([
            'lama_cicilan' => 12,
            'margin_kredit' => 5,
        ]);
        $metodeBayar = $this->paymentMethod();

        $response = $this->actingAs($customer)->post(route('pengajuan.store'), [
            'nama_pelanggan' => 'Customer Invoice',
            'no_ktp' => '1234567890123456',
            'no_telp' => '081234567890',
            'alamat' => 'Alamat invoice',
            'id_motor' => $motor->id,
            'harga_cash' => 18000000,
            'dp' => 3600000,
            'id_jenis_cicilan' => $tenor->id,
            'id_metode_bayar' => $metodeBayar->id,
        ]);

        $response->assertRedirect(route('pengajuan.index'));

        $pengajuan = PengajuanKredit::whereHas('pelanggan', fn ($query) => $query->where('email', $customer->email))->firstOrFail();

        $this->assertDatabaseHas('email_logs', [
            'to_email' => $customer->email,
            'subject' => "Detail Pengajuan Kredit #{$pengajuan->id} - Kremo",
            'template' => 'pengajuan-invoice',
            'status' => 'sent',
            'related_type' => PengajuanKredit::class,
            'related_id' => $pengajuan->id,
        ]);
    }

    public function test_approver_uses_customer_selected_payment_method_when_approving(): void
    {
        $approver = $this->user('approver', 'approver-payment@example.test');
        $pengajuan = $this->pendingPengajuanFor('payment-method@example.test');
        $pengajuan->update(['status_pengajuan' => 'Survey']);

        $this->actingAs($approver)
            ->post(route('pengajuan.approve', $pengajuan))
            ->assertRedirect(route('pengajuan.show', $pengajuan));

        $this->assertDatabaseHas('kredit', [
            'id_pengajuan_kredit' => $pengajuan->id,
            'id_metode_bayar' => $pengajuan->id_metode_bayar,
        ]);
    }

    public function test_surveyor_can_claim_open_pengajuan_and_other_surveyors_cannot_access_it(): void
    {
        $surveyorA = $this->user('surveyor', 'surveyor-a@example.test');
        $surveyorB = $this->user('surveyor', 'surveyor-b@example.test');
        $pengajuan = $this->pendingPengajuanFor('survey-customer@example.test');

        $this->actingAs($surveyorA)
            ->post(route('pengajuan.claim', $pengajuan))
            ->assertRedirect(route('pengajuan.show', $pengajuan));

        $pengajuan->refresh();

        $this->assertSame($surveyorA->id, $pengajuan->surveyor_id);
        $this->assertSame('Diproses', $pengajuan->status_pengajuan);

        $this->actingAs($surveyorB)
            ->from(route('dashboard'))
            ->post(route('pengajuan.claim', $pengajuan))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');

        $this->actingAs($surveyorB)
            ->get(route('pengajuan.show', $pengajuan))
            ->assertForbidden();

        $this->actingAs($surveyorB)
            ->post(route('pengajuan.survey', $pengajuan), ['catatan_survey' => 'Survey tidak valid'])
            ->assertForbidden();
    }

    private function user(string $role, string $email): User
    {
        return User::create([
            'name' => ucfirst($role),
            'email' => $email,
            'password' => 'password',
            'role' => $role,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * @return array{0: PengajuanKredit, 1: Kredit, 2: Angsuran}
     */
    private function financingFor(string $email): array
    {
        $jenisMotor = JenisMotor::create([
            'merk' => 'Honda',
            'jenis' => 'Bebek',
            'deskripsi_jenis' => 'Motor bebek',
        ]);

        $motor = Motor::create([
            'nama_motor' => 'Honda Test',
            'id_jenis' => $jenisMotor->id,
            'harga_jual' => 18000000,
            'stok' => 3,
        ]);

        $tenor = JenisCicilan::create([
            'lama_cicilan' => 12,
            'margin_kredit' => 5,
        ]);

        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Customer Test',
            'email' => $email,
            'no_ktp' => (string) random_int(1000000000000000, 9999999999999999),
            'no_telp' => '081234567890',
            'alamat' => 'Alamat test',
        ]);

        $pengajuan = PengajuanKredit::create([
            'tgl_pengajuan_kredit' => now(),
            'id_pelanggan' => $pelanggan->id,
            'id_motor' => $motor->id,
            'harga_cash' => 18000000,
            'dp' => 4000000,
            'id_jenis_cicilan' => $tenor->id,
            'harga_kredit' => 14700000,
            'cicilan_perbulan' => 1225000,
            'status_pengajuan' => 'Disetujui',
        ]);

        $kredit = Kredit::create([
            'id_pengajuan_kredit' => $pengajuan->id,
            'tgl_mulai_kredit' => now(),
            'tgl_selesai_kredit' => now()->addMonths(12),
            'sisa_kredit' => 14700000,
            'status_kredit' => 'Dicicil',
        ]);

        $angsuran = Angsuran::create([
            'id_kredit' => $kredit->id,
            'angsuran_ke' => 1,
            'tgl_jatuh_tempo' => now()->addMonth(),
            'total_bayar' => 1225000,
            'status' => 'Belum Bayar',
        ]);

        return [$pengajuan, $kredit, $angsuran];
    }

    private function pendingPengajuanFor(string $email): PengajuanKredit
    {
        $jenisMotor = JenisMotor::create([
            'merk' => 'Honda',
            'jenis' => 'Bebek',
            'deskripsi_jenis' => 'Motor bebek',
        ]);

        $motor = Motor::create([
            'nama_motor' => 'Honda Survey Test',
            'id_jenis' => $jenisMotor->id,
            'harga_jual' => 18000000,
            'stok' => 3,
        ]);

        $tenor = JenisCicilan::create([
            'lama_cicilan' => 12,
            'margin_kredit' => 5,
        ]);

        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Customer Survey',
            'email' => $email,
            'no_ktp' => (string) random_int(1000000000000000, 9999999999999999),
            'no_telp' => '081234567890',
            'alamat' => 'Alamat survey',
        ]);

        return PengajuanKredit::create([
            'tgl_pengajuan_kredit' => now(),
            'id_pelanggan' => $pelanggan->id,
            'id_motor' => $motor->id,
            'harga_cash' => 18000000,
            'dp' => 4000000,
            'id_jenis_cicilan' => $tenor->id,
            'id_metode_bayar' => $this->paymentMethod()->id,
            'harga_kredit' => 14700000,
            'cicilan_perbulan' => 1225000,
            'status_pengajuan' => 'Menunggu Konfirmasi',
        ]);
    }

    private function paymentMethod(): MetodeBayar
    {
        return MetodeBayar::create([
            'metode_pembayaran' => 'Transfer Bank',
            'tempat_bayar' => 'BCA',
            'no_rekening' => '1234567890',
        ]);
    }

    private function profileFor(User $user, string $name = 'Customer Profile'): Pelanggan
    {
        return Pelanggan::create([
            'nama_pelanggan' => $name,
            'email' => $user->email,
            'no_ktp' => (string) random_int(1000000000000000, 9999999999999999),
            'no_telp' => '081234567890',
            'alamat' => 'Alamat profile',
            'created_by' => $user->id,
        ]);
    }
}
