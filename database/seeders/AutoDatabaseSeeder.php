<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AutoDatabaseSeeder extends Seeder
{
    /**
     * Jalankan database seeds.
     */
    public function run(): void
    {
        // 1. Matikan pengecekan Foreign Key
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 2. Buat USER ADMIN MANUAL
        DB::table('users')->truncate();
        
        $this->command->info("👤 Membuat Akun Admin: admin@example.com / password");
        
        DB::table('users')->insert([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role' => 'admin', 
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. DAFTAR PRIORITAS TABEL
        $priorityTables = [
            'users', 
            'locations',
            'categories',
            'asset_statuses',
            'manufacturers',
            'suppliers',
            'employees',
            'asset_models',
            'assets',
            'maintenances',
            'maintenance_images', // Child dari maintenances
            'maintenance_technician', // Pivot table
            'scan_logs',
        ];

        $allTables = $this->getTableList();
        $tablesToSeed = array_unique(array_merge($priorityTables, $allTables));

        // Tabel System Laravel yang WAJIB DILEWATI
        $skipTables = [
            'migrations', 
            'password_resets', 
            'password_reset_tokens', 
            'failed_jobs', 
            'personal_access_tokens', 
            'sessions',
            'cache', 
            'cache_locks', 
            'jobs',
            'job_batches', 
        ];

        foreach ($tablesToSeed as $table) {
            // Skip tabel sistem & skip users
            if (in_array($table, $skipTables) || !in_array($table, $allTables)) {
                continue;
            }

            if ($table !== 'users') {
                DB::table($table)->truncate();
            }

            $this->command->info("♻️  Seeding tabel: {$table} (30 Data)...");

            // --- PERUBAHAN DI SINI: SET TOTAL 30 DATA ---
            $jumlahData = 30; 
            
            $this->seedTable($table, $jumlahData);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->command->info("✅ Sukses! Database terisi penuh (30 data per tabel).");
    }

    private function getTableList()
    {
        if (method_exists(Schema::class, 'getTables')) {
            return collect(Schema::getTables())->pluck('name')->toArray();
        }
        try {
            return array_map('current', DB::select('SHOW TABLES'));
        } catch (\Exception $e) {
            return [];
        }
    }

    private function seedTable($table, $rows)
    {
        $columns = Schema::getColumnListing($table);
        $data = [];

        for ($i = 0; $i < $rows; $i++) {
            $row = [];
            foreach ($columns as $column) {
                if ($column === 'id') continue;
                
                if ($table === 'users' && $column === 'email') {
                    $row[$column] = fake()->unique()->userName() . '@example.com'; 
                    continue;
                }

                $row[$column] = $this->generateSmartData($table, $column);
            }

            // Timestamp handling
            if (in_array('created_at', $columns) && !isset($row['created_at'])) {
                $row['created_at'] = now();
            }
            if (in_array('updated_at', $columns) && !isset($row['updated_at'])) {
                $row['updated_at'] = now();
            }

            $data[] = $row;
        }

        if (!empty($data)) {
            // Menggunakan insertOrIgnore agar aman dari duplikat di tabel Pivot
            DB::table($table)->insertOrIgnore($data);
        }
    }

    private function generateSmartData($table, $column)
    {
        $col = strtolower($column);

        // --- 1. Fix Kolom Path/Image ---
        if (str_contains($col, 'path') || str_contains($col, 'file') || str_contains($col, 'photo') || str_contains($col, 'image')) {
            return 'images/placeholder.jpg';
        }

        // --- 2. Fix Date columns ---
        if (str_contains($col, 'date') || str_contains($col, 'tanggal') || str_contains($col, 'eol') || str_contains($col, 'expired') || str_contains($col, 'finished_at') || str_contains($col, 'cancelled_at')) {
            return fake()->date();
        }

        // --- 3. Fix Numeric columns ---
        if (str_contains($col, 'cost') || str_contains($col, 'price') || str_contains($col, 'biaya') || str_contains($col, 'harga') || str_contains($col, 'amount')) {
            return fake()->numberBetween(100000, 10000000);
        }
        if (str_contains($col, 'warranty') || str_contains($col, 'garansi') || str_contains($col, 'months') || str_contains($col, 'bulan')) {
            return fake()->numberBetween(6, 36); 
        }
        if (str_contains($col, 'number') || str_contains($col, 'nomor') || str_contains($col, 'order') || str_contains($col, 'qty') || str_contains($col, 'quantity') || str_contains($col, 'total')) {
            return fake()->numberBetween(1, 1000);
        }

        // --- 4. Fix Polymorphic Relations ---
        if ($col === 'assigned_to_type') return 'App\\Models\\User';
        if ($col === 'assigned_to_id') return 1;

        // --- 5. Logic Foreign Key ---
        if (Str::endsWith($col, '_id')) {
            $relationName = str_replace('_id', '', $col);
            $targetTable = Str::plural($relationName); 
            
            if (!Schema::hasTable($targetTable) && $relationName == 'category') $targetTable = 'categories';
            if (!Schema::hasTable($targetTable) && $relationName == 'manufacturer') $targetTable = 'manufacturers';

            if (Schema::hasTable($targetTable)) {
                $randomId = DB::table($targetTable)->inRandomOrder()->value('id');
                if ($randomId) return $randomId;
            }
            return 1; 
        }

        // --- 6. Data Umum ---
        if (in_array($col, ['created_at', 'updated_at', 'deleted_at', 'email_verified_at'])) return now();
        if ($col === 'role') return 'admin';
        if ($col === 'remember_token') return Str::random(10);
        
        if ($col === 'email') return fake()->unique()->safeEmail();
        if (str_contains($col, 'password')) return bcrypt('password'); 
        
        if (str_contains($col, 'code') || str_contains($col, 'kode') || str_contains($col, 'tag')) return strtoupper(fake()->bothify('??-####'));
        if (str_contains($col, 'serial')) return fake()->uuid();
        
        if (str_contains($col, 'name') || str_contains($col, 'nama')) return fake()->name();
        if (str_contains($col, 'phone') || str_contains($col, 'telp')) return fake()->phoneNumber();
        if (str_contains($col, 'address') || str_contains($col, 'alamat')) return fake()->address();
        
        if (str_contains($col, 'description') || str_contains($col, 'keterangan') || str_contains($col, 'notes') || str_contains($col, 'options')) return fake()->sentence();
        
        if (str_contains($col, 'is_active')) return true;
        if ($col === 'status') return 'active'; 

        // Default Terakhir
        return fake()->word();
    }
}