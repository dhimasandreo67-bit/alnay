<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SessionInactivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_session_expires_after_inactivity(): void
    {
        $user = User::factory()->create([
            'role' => 'pelanggan',
            'status_aktif' => true,
        ]);

        // Request saat sesi masih baru / aktif
        $response = $this->actingAs($user, 'web')
            ->withSession(['last_activity_web' => now()->timestamp])
            ->get(route('akun.dashboard'));

        $response->assertStatus(200);
        $this->assertTrue(Auth::guard('web')->check());

        // Simulasi inaktivitas lebih dari 60 menit
        $responseExpired = $this->actingAs($user, 'web')
            ->withSession(['last_activity_web' => now()->subMinutes(61)->timestamp])
            ->get(route('akun.dashboard'));

        $responseExpired->assertRedirect(route('masuk'));
        $responseExpired->assertSessionHas('error', 'Sesi Anda telah berakhir karena tidak ada aktivitas. Silakan masuk kembali.');
        $this->assertFalse(Auth::guard('web')->check());
    }

    public function test_admin_session_expires_after_inactivity(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status_aktif' => true,
        ]);

        // Request saat sesi admin masih aktif
        $response = $this->actingAs($admin, 'admin')
            ->withSession(['last_activity_admin' => now()->timestamp])
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $this->assertTrue(Auth::guard('admin')->check());

        // Simulasi inaktivitas admin lebih dari 30 menit
        $responseExpired = $this->actingAs($admin, 'admin')
            ->withSession(['last_activity_admin' => now()->subMinutes(31)->timestamp])
            ->get(route('admin.dashboard'));

        $responseExpired->assertRedirect(route('admin.login'));
        $responseExpired->assertSessionHas('error', 'Sesi Anda telah berakhir karena tidak ada aktivitas. Silakan masuk kembali.');
        $this->assertFalse(Auth::guard('admin')->check());
    }
}
