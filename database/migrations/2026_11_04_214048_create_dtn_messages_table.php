<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dtn_messages', function (Blueprint $table) {
            $table->id();

            $table->string('message_id', 64)->unique();
            $table->string('origin_device_id', 64);
            $table->string('code', 10);

            $table->timestamp('message_created_at');
            $table->timestamp('expires_at')->index();
            $table->timestamp('received_at');

            $table->timestamps();

            $table->index(['code', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtn_messages');
    }
};
