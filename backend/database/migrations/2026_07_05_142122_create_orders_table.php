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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            /*
            |--------------------------------------------------------------------------
            | Identification
            |--------------------------------------------------------------------------
            */

            $table->string('reg')->unique();
            $table->string('order_number')->unique();
            $table->string('slug')->unique();
            $table->date('date')->index();

            /*
            |--------------------------------------------------------------------------
            | Relationship
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')->constrained()->restrictOnDelete()->index();

            /*
            |--------------------------------------------------------------------------
            | Financial
            |--------------------------------------------------------------------------
            */

            $table->decimal('subtotal',14,2)->default(0);
            $table->decimal('discount',14,2)->default(0);
            $table->decimal('vat',14,2)->default(0);
            $table->decimal('tax',14,2)->default(0);
            $table->decimal('payable_amount',14,2)->default(0);

            $table->char('currency',3)->default('BDT');
            $table->integer('point')->default(0);

            /*
            |--------------------------------------------------------------------------
            | Payment
            |--------------------------------------------------------------------------
            */

            $table->enum('payment_method', [
                'cash',
                'card',
                'bank_transfer',
                'bkash',
                'nagad',
                'rocket',
                'wallet',
            ])->default('cash')->index();

            $table->timestamp('paid_at')->nullable()->index();

            /*
            |--------------------------------------------------------------------------
            | Order Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status',[
                'Pending',
                'Confirmed',
                'Processing',
                'Picked',
                'Shipped',
                'Out for Delivery',
                'Delivered',
                'Cancelled',
                'Failed',
                'Returned'
            ])->default('Pending')->index();


            /*
            |--------------------------------------------------------------------------
            | Shipping
            |--------------------------------------------------------------------------
            */

            $table->string('contact_name')->nullable();
            $table->string('contact_number', 20)->nullable();
            $table->string('contact_email')->nullable();
            $table->text('shipping_address')->nullable();

            $table->foreignId('division_id')
                ->nullable()
                ->constrained('divisions')
                ->restrictOnDelete();

            $table->foreignId('district_id')
                ->nullable()
                ->constrained('districts')
                ->restrictOnDelete();

            $table->foreignId('upazila_id')
                ->nullable()
                ->constrained('upazilas')
                ->restrictOnDelete();

            $table->foreignId('police_station_id')
                ->nullable()
                ->constrained('police_stations')
                ->restrictOnDelete();

            $table->string('postal_code',20)->nullable();

            $table->text('remarks')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Timeline
            |--------------------------------------------------------------------------
            */

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('picked_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('returned_at')->nullable();

            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();



            $table->softDeletes();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Composite Index
            |--------------------------------------------------------------------------
            */

            $table->index(['user_id', 'status']);
            $table->index(['date', 'status']);
            $table->index(['created_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
