<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('message_count')->default(0);
            $table->dateTime('period_start');
            $table->dateTime('period_end');
            $table->string('period_type')->default('hour'); // hour, day, month
            $table->timestamps();
            
            $table->index(['project_id', 'period_start']);
            $table->index(['user_id', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_logs');
    }
};
