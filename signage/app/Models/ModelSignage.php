<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModelSignage extends Model
{
    protected $table = 'model_signage';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    // Menjaga agar field ini dapat diisi saat pemanggilan create()
    protected $fillable = [
        'nama_model',
        'merk',
        'resolusi',
        'orientasi',
        'keterangan',
    ];

    // Relasi ke DigitalSignage (Satu model TV dipakai banyak perangkat signage)
    public function digitalSignages(): HasMany
    {
        return $this->hasMany(DigitalSignage::class, 'model_signage_id', 'id');
    }
}