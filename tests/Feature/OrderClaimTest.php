<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderClaim;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderClaimTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $admin;
    protected Product $product;
    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Users
        $this->customer = User::factory()->create(['role' => 'customer']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        // Create Category and Product
        $category = Category::create([
            'category_name' => 'Spanduk',
            'slug' => 'spanduk',
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'product_name' => 'Banner Flexy',
            'product_type' => 'custom',
            'price' => 50000,
            'stock' => 10,
            'weight' => 500,
            'length' => 10,
            'width' => 10,
            'height' => 10,
            'requires_design_file' => false,
        ]);

        // Create Order
        $this->order = Order::create([
            'user_id' => $this->customer->id,
            'invoice_number' => 'INV-TEST-0001',
            'order_status' => 'selesai',
            'payment_status' => 'paid',
            'total_price' => 50000,
            'status' => 'paid',
        ]);

        // Detail
        OrderDetail::create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'qty' => 1,
            'subtotal' => 50000,
        ]);
    }

    /**
     * Test customer cannot claim if order status is not selesai.
     */
    public function test_customer_cannot_claim_unfinished_order()
    {
        $this->order->order_status = 'dikirim';
        $this->order->save();

        $response = $this->actingAs($this->customer)
            ->get(route('pesanan.claim.create', $this->order->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /**
     * Test customer can submit claim with video unboxing.
     */
    public function test_customer_can_submit_claim()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('unboxing.mp4', 500, 'video/mp4');

        $response = $this->actingAs($this->customer)
            ->post(route('pesanan.claim.store', $this->order->id), [
                'reason' => 'Produk Rusak',
                'description' => 'Bagian pojok cetakan sobek dan gambarnya buram.',
                'proof_video' => $file,
                'bank_name' => 'BCA',
                'bank_account_number' => '1234567890',
                'bank_account_name' => 'Ahmad Rifa',
            ]);

        $response->assertRedirect(route('dashboard', ['tab' => 'pesanan']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('order_claims', [
            'order_id' => $this->order->id,
            'reason' => 'Produk Rusak',
            'status' => 'pending',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'Ahmad Rifa',
        ]);

        $claim = OrderClaim::where('order_id', $this->order->id)->first();
        Storage::disk('public')->assertExists($claim->proof_video);
    }

    /**
     * Test admin can view and approve claim.
     */
    public function test_admin_can_approve_claim()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('unboxing.mp4', 500, 'video/mp4');
        $path = $file->store('claims', 'public');

        $claim = OrderClaim::create([
            'order_id' => $this->order->id,
            'reason' => 'Produk Rusak',
            'description' => 'Kerusakan fisik produk.',
            'proof_video' => $path,
            'status' => 'pending',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'Ahmad Rifa',
        ]);

        // Admin checks list and show
        $responseIndex = $this->actingAs($this->admin)
            ->get(route('admin.claims.index'));
        $responseIndex->assertOk();

        $responseShow = $this->actingAs($this->admin)
            ->get(route('admin.claims.show', $claim->id));
        $responseShow->assertOk();

        // Admin approves
        $responseStatus = $this->actingAs($this->admin)
            ->post(route('admin.claims.status', $claim->id), [
                'status' => 'approved',
                'admin_notes' => 'Kerusakan disetujui, refund akan ditransfer.',
            ]);

        $responseStatus->assertRedirect(route('admin.claims.index'));
        $responseStatus->assertSessionHas('success');

        $claim->refresh();
        $this->assertEquals('approved', $claim->status);
        $this->assertEquals('Kerusakan disetujui, refund akan ditransfer.', $claim->admin_notes);
    }

    /**
     * Test admin can view report with correct calculations.
     */
    public function test_admin_can_view_report_calculations()
    {
        // Create an approved claim for $this->order
        OrderClaim::create([
            'order_id'            => $this->order->id,
            'reason'              => 'Produk Rusak',
            'description'         => 'Kerusakan cetakan banner.',
            'proof_video'         => 'claims/unboxing.mp4',
            'status'              => 'approved',
            'bank_name'           => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name'   => 'Ahmad Rifa',
        ]);

        // Create another order which does NOT have a claim (adds to gross and net revenue)
        $order2 = Order::create([
            'user_id'         => $this->customer->id,
            'invoice_number'  => 'INV-TEST-0002',
            'order_status'    => 'selesai',
            'payment_status'  => 'paid',
            'total_price'     => 150000,
            'status'          => 'paid',
        ]);

        // Request reports page
        $response = $this->actingAs($this->admin)
            ->get(route('laporan.index', ['period' => 'all']));

        $response->assertOk();
        $response->assertViewHas('totalPendapatanKotor', 200000.0); // 50000 + 150000
        $response->assertViewHas('totalRefund', 50000.0);           // claim order 1 is approved
        $response->assertViewHas('totalPendapatanBersih', 150000.0); // 200000 - 50000
    }

    /**
     * Test admin can export reports with period filters.
     */
    public function test_admin_can_export_reports()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('laporan.export', ['period' => '1_month']));

        $response->assertOk();
        $response->assertHeader('Content-type', 'application/vnd.ms-excel; charset=utf-8');
    }

    /**
     * Test admin can download report PDF.
     */
    public function test_admin_can_download_report_pdf()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('laporan.pdf', ['period' => '1_month']));

        $response->assertOk();
        $response->assertHeader('Content-type', 'application/pdf');
    }
}
