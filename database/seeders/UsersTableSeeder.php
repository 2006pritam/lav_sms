<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Helpers\Qs;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        if (app()->environment('production')) {
            if ($this->command) {
                $this->command->warn('Demo user seeding is disabled in production.');
            }
            return;
        }

        $this->createNewUsers();
        $this->createManyUsers(3);
    }

    protected function createNewUsers()
    {
        $password = Hash::make('cj');

        $d = [
            [
                'name' => 'Teacher Chike',
                'email' => 'teacher@teacher.com',
                'user_type' => 'teacher',
                'username' => 'teacher',
                'password' => $password,
                'code' => strtoupper(Str::random(10)),
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Parent Kaba',
                'email' => 'parent@parent.com',
                'user_type' => 'parent',
                'username' => 'parent',
                'password' => $password,
                'code' => strtoupper(Str::random(10)),
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Accountant Jeff',
                'email' => 'accountant@accountant.com',
                'user_type' => 'accountant',
                'username' => 'accountant',
                'password' => $password,
                'code' => strtoupper(Str::random(10)),
                'remember_token' => Str::random(10),
            ],
        ];
        DB::table('users')->insertOrIgnore($d);
    }

    protected function createManyUsers(int $count)
    {
        $data = [];
        $user_type = Qs::getAllUserTypes(['super_admin', 'librarian', 'student']);

        for ($i = 1; $i <= $count; $i++) {
            foreach ($user_type as $k => $ut) {
                $data[] = [
                    'name' => ucfirst($user_type[$k]) . ' ' . $i,
                    'email' => $user_type[$k] . $i . '@' . $user_type[$k] . '.com',
                    'user_type' => $user_type[$k],
                    'username' => $user_type[$k] . $i,
                    'password' => Hash::make($user_type[$k]),
                    'code' => strtoupper(Str::random(10)),
                    'remember_token' => Str::random(10),
                ];
            }
        }

        DB::table('users')->insertOrIgnore($data);
    }
}
