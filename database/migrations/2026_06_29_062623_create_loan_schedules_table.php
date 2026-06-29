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
    { {
            Schema::create('loan_schedules', function (Blueprint $table) {

                $table->id();

                $table->foreignId('loan_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->integer('installment_no');

                $table->date('due_date');

                $table->decimal('principal_due', 15, 2);

                $table->decimal('interest_due', 15, 2);

                $table->decimal('total_due', 15, 2);

                $table->decimal('amount_paid', 15, 2)->default(0);

                $table->decimal('remaining_balance', 15, 2);

                $table->enum('status', [
                    'pending',
                    'partial',
                    'paid',
                    'overdue'
                ])->default('pending');

                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_schedules');
    }
};
