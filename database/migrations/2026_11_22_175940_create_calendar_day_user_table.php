<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_day_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('calendar_day_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('solved_at');
            $table->unique(['calendar_day_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_day_user');
    }
};
