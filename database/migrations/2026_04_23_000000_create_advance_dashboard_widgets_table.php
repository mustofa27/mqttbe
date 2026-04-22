<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('advance_dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->string('title', 120)->nullable();
            $table->enum('data_type', ['number', 'text', 'json']);
            $table->enum('visualization_mode', ['time_series', 'bar']);
            $table->string('json_key', 120)->nullable();
            $table->enum('json_key_type', ['number', 'text'])->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'position']);
            $table->index(['project_id', 'topic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advance_dashboard_widgets');
    }
};
