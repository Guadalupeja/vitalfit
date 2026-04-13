<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('treatments', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('category', 50);          // faciales|aparatologia|esteticos|laser|nutricion|valoracion
            $table->unsignedSmallInteger('duration_minutes'); // 30, 60, 90, 120...
            $table->string('color_hex', 7);          // #RRGGBB
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->index(['category', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatments');
    }
};
