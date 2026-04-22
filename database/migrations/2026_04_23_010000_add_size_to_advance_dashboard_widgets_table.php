<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('advance_dashboard_widgets', function (Blueprint $table) {
            $table->string('size', 16)->default('medium')->after('json_key_type');
        });
    }

    public function down(): void
    {
        Schema::table('advance_dashboard_widgets', function (Blueprint $table) {
            $table->dropColumn('size');
        });
    }
};
