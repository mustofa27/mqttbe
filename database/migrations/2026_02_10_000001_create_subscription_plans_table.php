<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tier');
            $table->decimal('price', 10, 2);
            $table->integer('max_projects')->default(0);
            $table->integer('max_devices_per_project')->default(0);
            $table->integer('max_topics_per_project')->default(0);
            $table->integer('rate_limit_per_hour')->default(0);
            $table->boolean('analytics_enabled')->default(false);
            $table->boolean('advanced_analytics_enabled')->default(false);
            $table->boolean('webhooks_enabled')->default(false);
            $table->boolean('api_access')->default(false);
            $table->boolean('priority_support')->default(false);
            $table->integer('data_retention_days')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
