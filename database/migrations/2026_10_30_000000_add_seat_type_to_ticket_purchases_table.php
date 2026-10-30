<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_purchases', function (Blueprint $table): void {
            $table->string('seat_type')->default('regular')->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_purchases', function (Blueprint $table): void {
            $table->dropColumn('seat_type');
        });
    }
};
