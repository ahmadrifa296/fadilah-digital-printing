<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        \App\Models\Setting::setVal('web_faq', json_encode([
            ['q' => 'FAQ Bantuan', 'a' => 'Jawaban Bantuan']
        ]));
    }

    /**
     * Test help page can be rendered.
     */
    public function test_help_page_can_be_rendered(): void
    {
        $response = $this->get('/bantuan');

        $response->assertRedirect('/#faq');
    }
}
