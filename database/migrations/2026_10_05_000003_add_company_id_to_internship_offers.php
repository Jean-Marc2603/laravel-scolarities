<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('companies')) {
            Schema::create('companies', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('address')->nullable();
                $table->string('phone', 50)->nullable();
                $table->string('email')->nullable();
                $table->string('sector')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('internship_offers', 'company_id')) {
            Schema::table('internship_offers', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('company')->constrained('companies')->nullOnDelete();
            });
        }

        if (Schema::hasTable('internship_offers')) {
            $legacyNames = DB::table('internship_offers')
                ->whereNotNull('company')
                ->where('company', '<>', '')
                ->distinct()
                ->pluck('company');

            foreach ($legacyNames as $legacyName) {
                $companyName = trim((string) $legacyName);
                if ($companyName === '') {
                    continue;
                }

                $companyId = DB::table('companies')->where('name', $companyName)->value('id');
                if (! $companyId) {
                    $companyId = DB::table('companies')->insertGetId([
                        'name' => $companyName,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('internship_offers')
                    ->whereNull('company_id')
                    ->where('company', $legacyName)
                    ->update(['company_id' => $companyId]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('internship_offers', 'company_id')) {
            Schema::table('internship_offers', function (Blueprint $table) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            });
        }
    }
};
