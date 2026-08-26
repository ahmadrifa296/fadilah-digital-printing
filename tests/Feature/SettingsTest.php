<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic settings row required by layout/controller if any
        Setting::setVal('web_name', 'Fadilah Printing');
        Setting::setVal('web_phone', '081234567890');
        Setting::setVal('web_email', 'official@fadilah.com');
        Setting::setVal('web_address', 'Address 1');
        Setting::setVal('web_about', 'About info');
        Setting::setVal('seo_title', 'Fadilah SEO');
        Setting::setVal('seo_description', 'Fadilah Description');
        Setting::setVal('seo_keywords', 'key1, key2');
    }

    public function test_guest_cannot_access_settings()
    {
        $response = $this->get('/settings');
        $response->assertRedirect('/login');
    }

    public function test_customer_cannot_access_settings()
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->get('/settings');
        $response->assertRedirect('/');
    }

    public function test_admin_can_access_settings_page()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/settings');
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Website');
    }

    public function test_admin_can_update_settings_without_logo()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->put('/settings', [
            'web_name' => 'New Store Name',
            'web_phone' => '0811111111',
            'web_email' => 'new@toko.com',
            'web_address' => 'New Address',
            'web_about' => 'New About Us info content',
            'web_faq' => '[]',
            'seo_title' => 'New SEO Title',
            'seo_description' => 'New SEO Description',
            'seo_keywords' => 'keyword1, keyword2',
            'warehouse_name' => 'Gudang Utama',
            'warehouse_address' => 'Jl. Kebon Jeruk No. 12',
            'warehouse_province' => 'DKI Jakarta',
            'warehouse_city' => 'Jakarta Barat',
            'warehouse_district' => 'Kebon Jeruk',
            'warehouse_subdistrict' => 'Kebon Jeruk',
            'warehouse_postal_code' => '11530',
            'warehouse_latitude' => '-6.1234',
            'warehouse_longitude' => '106.1234',
            'warehouse_biteship_origin_id' => '123456',
        ]);

        $response->assertRedirect();
        $this->assertEquals('New Store Name', Setting::getVal('web_name'));
    }

    public function test_admin_can_upload_company_logo()
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $file = UploadedFile::fake()->create('company_logo.png', 100);

        $response = $this->actingAs($admin)->put('/settings', [
            'web_name' => 'Store Name',
            'web_phone' => '081234567890',
            'web_email' => 'official@fadilah.com',
            'web_address' => 'Address 1',
            'web_about' => 'About info',
            'seo_title' => 'SEO Title',
            'seo_description' => 'SEO Description',
            'seo_keywords' => 'keywords',
            'company_logo' => $file,
            'warehouse_name' => 'Gudang Utama',
            'warehouse_address' => 'Jl. Kebon Jeruk No. 12',
            'warehouse_province' => 'DKI Jakarta',
            'warehouse_city' => 'Jakarta Barat',
            'warehouse_district' => 'Kebon Jeruk',
            'warehouse_subdistrict' => 'Kebon Jeruk',
            'warehouse_postal_code' => '11530',
            'warehouse_latitude' => '-6.1234',
            'warehouse_longitude' => '106.1234',
            'warehouse_biteship_origin_id' => '123456',
        ]);

        $response->assertRedirect();
        
        $logoUrl = Setting::getVal('company_logo');
        $this->assertNotNull($logoUrl);
        $this->assertStringContainsString('settings/logo_', $logoUrl);

        // Verify storage file exists
        $filePath = str_replace('/storage/', '', $logoUrl);
        Storage::disk('public')->assertExists($filePath);
    }

    public function test_admin_can_delete_company_logo()
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        
        // Upload initial logo
        $file = UploadedFile::fake()->create('logo_old.png', 100);
        $path = $file->store('settings', 'public');
        $logoUrl = '/storage/' . $path;
        Setting::updateOrCreate(
            ['key' => 'company_logo'],
            [
                'value' => $logoUrl,
                'company_logo' => $logoUrl,
                'description' => 'Logo Perusahaan'
            ]
        );

        Storage::disk('public')->assertExists($path);

        // Delete logo request
        $response = $this->actingAs($admin)->delete('/settings/logo');

        $response->assertRedirect();
        
        // Check database is null
        $this->assertNull(Setting::getVal('company_logo'));
        
        // Check database column is null
        $settingRow = Setting::where('key', 'company_logo')->first();
        $this->assertNull($settingRow->company_logo);

        // Check storage file is deleted
        Storage::disk('public')->assertMissing($path);
    }
}
