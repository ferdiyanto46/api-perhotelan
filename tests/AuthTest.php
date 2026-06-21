<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Testing\DatabaseMigrations;

class AuthTest extends TestCase
{
    // Trait ini akan menjalankan migrasi database sebelum setiap tes
    // dan mengembalikannya setelah tes selesai. Ini memastikan
    // setiap tes berjalan pada database yang bersih.
    use DatabaseMigrations;

    protected $superAdmin;
    protected $admin;
    protected $customer;

    /**
     * Menyiapkan environment sebelum setiap tes dijalankan.
     */
    public function setUp(): void
    {
        parent::setUp();

        // Buat user untuk setiap role dengan atribut 'role' langsung
        $this->superAdmin = User::create([
            'name' => 'Test Super Admin',
            'email' => 'super@test.com',
            'password' => Hash::make('password'),
            'role' => 'super-admin',
        ]);

        $this->admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $this->customer = User::create([
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);
    }

    /**
     * Tes skenario sukses: Super Admin berhasil mendaftarkan admin baru.
     *
     * @return void
     */
    public function testSuperAdminCanRegisterNewAdmin()
    {
        $newAdminData = [
            'name' => 'New Admin User',
            'email' => 'newadmin@test.com',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ];

        // Bertindak sebagai superAdmin dan mengirim request
        $this->actingAs($this->superAdmin)
             ->post('/auth/register-admin', $newAdminData);

        // Assert (Memastikan hasil sesuai harapan)
        $this->seeStatusCode(201);
        $this->seeJsonContains([
            'message' => 'Registrasi admin berhasil!',
            'user' => [
                'name' => 'New Admin User',
                'email' => 'newadmin@test.com',
            ]
        ]);

        // Pastikan user baru ada di database dengan role 'admin'
        $this->seeInDatabase('users', ['email' => 'newadmin@test.com', 'role' => 'admin']);
        $this->assertNotNull(User::where('email', 'newadmin@test.com')->where('role', 'admin')->first());
    }

    /**
     * Tes skenario gagal: Admin biasa tidak bisa mendaftarkan admin baru.
     *
     * @return void
     */
    public function testRegularAdminCannotRegisterNewAdmin()
    {
        $newAdminData = [
            'name' => 'Another Admin',
            'email' => 'another@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        // Bertindak sebagai admin biasa
        $this->actingAs($this->admin)
             ->post('/auth/register-admin', $newAdminData);

        // RoleMiddleware harus mengembalikan 403 Forbidden
        $this->assertResponseStatus(403); // Menggunakan assertResponseStatus untuk Lumen
        $this->seeJsonContains(['message' => 'Forbidden. You do not have the required role.']);
    }

    /**
     * Tes skenario gagal: Pengguna tanpa autentikasi tidak bisa mendaftar.
     *
     * @return void
     */
    public function testUnauthenticatedUserCannotRegisterAdmin()
    {
        $this->post('/auth/register-admin', []);

        // Middleware 'auth' harus mengembalikan 401 Unauthorized
        $this->assertResponseStatus(401); // Menggunakan assertResponseStatus untuk Lumen
    }
}

// ```

// ### 2. Menjalankan Pengujian

// Setelah file `tests/AuthTest.php` dibuat, Anda dapat menjalankan pengujian dari terminal di direktori root proyek Anda (`c:\laragon\www\perhotelan\`) menggunakan perintah:

// ```bash
// vendor/bin/phpunit  
