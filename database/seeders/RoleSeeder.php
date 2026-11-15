<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Две роли приложения: модератор и читатель.
     * updateOrCreate — чтобы сидер можно было запускать повторно.
     */
    public function run(): void
    {
        Role::updateOrCreate(
            ['code' => Role::MODERATOR],
            ['title' => 'Модератор']
        );

        Role::updateOrCreate(
            ['code' => Role::READER],
            ['title' => 'Читатель']
        );
    }
}
