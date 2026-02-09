<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('type'); // rate_limit_warning, quota_warning, high_volume, etc.
            $table->integer('threshold')->nullable(); // For numeric thresholds
            $table->string('condition')->nullable(); // exceeds, approaches, drops_below, etc.
            $table->json('recipients'); // Email addresses
            $table->boolean('active')->default(true);
            $table->dateTime('last_triggered_at')->nullable();
            $table->integer('trigger_count')->default(0);
            $table->timestamps();

            $table->index(['project_id', 'active']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
