<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mailbox_emails', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('message_id')->nullable()->index();
            $table->string('subject')->nullable();
            $table->longText('html_body')->nullable();
            $table->longText('text_body')->nullable();
            $table->json('from')->nullable();
            $table->json('to')->nullable();
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
            $table->json('reply_to')->nullable();
            $table->json('headers')->nullable();
            $table->json('attachments')->nullable();
            $table->string('mailer')->nullable();
            $table->string('queue')->nullable();
            $table->json('tags')->nullable();
            $table->longText('raw_source')->nullable();
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamps();

            $table->index('created_at');
            $table->index('subject');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mailbox_emails');
    }
};
