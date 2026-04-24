<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treatments', function (Blueprint $table) {
            if (!Schema::hasColumn('treatments', 'treatment_type_id')) {
                $table->foreignId('treatment_type_id')
                    ->nullable()
                    ->after('branch_id')
                    ->constrained('treatment_types')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('treatments', function (Blueprint $table) {
            if (Schema::hasColumn('treatments', 'treatment_type_id')) {
                $table->dropConstrainedForeignId('treatment_type_id');
            }
        });
    }
};