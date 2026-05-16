<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();

            $table->morphs('attachable');

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('filename');
            $table->string('path');

            $table->unsignedBigInteger('size');
            $table->string('mime_type');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};