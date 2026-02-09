<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('url');
            $table->string('event_type'); // message_published, rate_limit_exceeded, quota_warning, etc.
            $table->text('description')->nullable();
            $table->json('headers')->nullable(); // Custom headers to send
            $table->boolean('active')->default(true);
            $table->dateTime('last_triggered_at')->nullable();
            $table->integer('failure_count')->default(0);
            $table->dateTime('last_failure_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'active']);
            $table->index('event_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhooks');
    }
};
