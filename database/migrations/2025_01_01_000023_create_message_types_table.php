<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pre-written SMS / e-mail templates ("modèles") picked on the intervention
        // sheet to prefill the message composer. Each row targets a single channel.
        Schema::create('message_types', function (Blueprint $table) {
            $table->id();
            $table->string('canal')->default('sms');   // 'sms' | 'email'
            $table->string('titre');                    // label shown in the picker
            $table->string('sujet')->nullable();        // e-mail subject (email only)
            $table->text('corps')->nullable();          // message body
            $table->timestamps();

            $table->index('canal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_types');
    }
};
