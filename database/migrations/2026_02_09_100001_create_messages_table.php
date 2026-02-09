<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->foreignId('topic_id')->constrained()->onDelete('cascade');
            $table->longText('payload');
            $table->string('mqtt_topic');
            $table->integer('qos')->default(0);
            $table->boolean('retained')->default(false);
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['topic_id', 'created_at']);
            $table->index(['device_id', 'created_at']);
            $table->index(['project_id', 'created_at']);
            $table->index('mqtt_topic');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
