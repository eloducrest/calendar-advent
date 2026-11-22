<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_days', function (Blueprint $table): void {
            $table->id();
            $table->unsignedTinyInteger('day')->unique();
            $table->date('available_at');
            $table->string('challenge_type'); // riddle, qcm, game
            $table->json('challenge_data')->nullable();
            $table->string('gift_title')->nullable();
            $table->text('gift_message')->nullable();
            $table->string('gift_image_path')->nullable();
            $table->string('gift_link_url')->nullable();
            $table->string('gift_link_label')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_days');
    }
};
