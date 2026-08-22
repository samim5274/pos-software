<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reg',
        'order_number',
        'slug',
        'order_date',
        'user_id',
        'supplier_id',
        'supplier_name',
        'supplier_phone',
        'subtotal',
        'discount',
        'vat_percentage',
        'vat',
        'due_amount',
        'payable_amount',
        'payment_method',
        'currency',
        'point',
        'status',
        'completed_at',
        'returned_at',
        'returned_by',
        'remarks',
        'ip_address',
        'user_agent',
        'paid_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'vat_percentage' => 'decimal:2',
        'vat' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'payable_amount' => 'decimal:2',
        'point' => 'integer',
        'completed_at' => 'datetime',
        'returned_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    protected $appends = [
        'paid_amount',
        'is_paid',
        'is_due',
    ];

    // Created by user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // User who returned the purchase order
    public function returnedBy()
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    // Purchase order items
    public function items()
    {
        return $this->hasMany(PurchaseCart::class, 'reg', 'reg');
    }

}
