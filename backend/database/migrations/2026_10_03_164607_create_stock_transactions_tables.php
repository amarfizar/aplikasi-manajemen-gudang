<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->enum('type', ['in', 'out', 'adjustment']);
            $table->date('date');
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->string('destination')->nullable();
            $table->string('department')->nullable();
            $table->string('pic')->nullable();
            $table->string('reference')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('status')->default('posted');
            $table->timestamps();
        });

        Schema::create('stock_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 14, 2);
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('purchase_price', 14, 2)->nullable();
            $table->decimal('subtotal', 14, 2)->nullable();
            $table->decimal('stock_before', 14, 2)->nullable();
            $table->decimal('stock_after', 14, 2)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transaction_items');
        Schema::dropIfExists('stock_transactions');
    }
};
