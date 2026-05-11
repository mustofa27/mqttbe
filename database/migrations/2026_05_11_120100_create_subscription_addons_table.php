<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_addons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('unit_type');
            $table->decimal('price', 12, 2);
            $table->integer('included_units')->default(0);
            $table->boolean('is_recurring')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['active', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_addons');
    }
};
