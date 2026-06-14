<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->string('email_domain')->nullable()->after('email');
            $table->string('imap_host')->nullable()->after('email_domain');
            $table->unsignedSmallInteger('imap_port')->default(993)->after('imap_host');
            $table->string('imap_encryption', 10)->default('ssl')->after('imap_port');
        });
    }

    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropColumn(['email_domain', 'imap_host', 'imap_port', 'imap_encryption']);
        });
    }
};
