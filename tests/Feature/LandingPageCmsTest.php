<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\CmsSlider;
use App\Models\CmsFeature;
use App\Models\CmsSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LandingPageCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_dynamic_cms_content(): void
    {
        CmsSlider::create([
            'title' => 'Solusi HR Terbaik 2026',
            'subtitle' => 'Kelola absensi dan payroll otomatis',
            'order_index' => 1,
            'is_active' => true,
        ]);

        CmsFeature::create([
            'title' => 'Kalkulasi TER PPh 21',
            'description' => 'Sesuai regulasi terbaru pemerintah',
            'icon' => 'shield-check',
            'order_index' => 1,
            'is_active' => true,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Solusi HR Terbaik 2026');
        $response->assertSee('Kalkulasi TER PPh 21');
    }

    public function test_visitor_can_submit_contact_inquiry_on_landing_page(): void
    {
        $response = $this->post('/contact-inquiry', [
            'name' => 'John Doe',
            'email' => 'john@corporation.id',
            'phone' => '+6281299887766',
            'company' => 'PT Corporation Internasional',
            'message' => 'Kami ingin menjadwalkan demo sistem untuk 300 karyawan kami.',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cms_contact_messages', [
            'name' => 'John Doe',
            'email' => 'john@corporation.id',
            'company' => 'PT Corporation Internasional',
        ]);
    }
}

