<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileProxyAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_s3_proxy(): void
    {
        $response = $this->get('/s3/test/file.txt');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_s3_proxy(): void
    {
        Storage::disk('public')->put('test-proxy-file.txt', 'hello');

        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/s3/test-proxy-file.txt');
        $response->assertStatus(200);

        Storage::disk('public')->delete('test-proxy-file.txt');
    }

    public function test_s3_proxy_with_invalid_path_returns_error(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/s3/nonexistent/path.txt');
        $response->assertStatus(404);
    }
}
