<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->text('purpose');
            $table->foreignId('borrower_id')->constrained();
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('interest_rate', 5, 2);
            $table->enum('interest_type', [
                'monthly_interest',
                'flat_interest'
            ]);
            $table->integer('term_months');
            $table->decimal('total_interest', 15, 2);
            $table->decimal('total_payable', 15, 2);
            $table->decimal('monthly_due', 15, 2);
            $table->date('release_date');
            $table->date('maturity_date');
            $table->string('proof_of_transfer')->nullable();
            $table->enum('status', [
                'active',
                'completed',
                'overdue',
                'cancelled'
            ]);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
