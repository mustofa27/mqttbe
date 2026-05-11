<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->integer('max_webhooks_per_project')->default(0)->after('webhooks_enabled');
            $table->integer('max_api_keys')->default(0)->after('api_access');
            $table->integer('max_advance_dashboard_widgets')->default(0)->after('advanced_analytics_enabled');
            $table->bigInteger('max_monthly_messages')->default(0)->after('rate_limit_per_hour');
            $table->integer('api_rpm')->default(0)->after('max_api_keys');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn([
                'max_webhooks_per_project',
                'max_api_keys',
                'max_advance_dashboard_widgets',
                'max_monthly_messages',
                'api_rpm',
            ]);
        });
    }
};
