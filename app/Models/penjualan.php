<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class penjualan extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'pelanggan_id',
        'nomor_transaksi',
        'tanggal',
        'total',
        'tunai',
        'kembalian',
        'status',
        'subtotal',
        'pajak',
        'diskon'
    ];
}