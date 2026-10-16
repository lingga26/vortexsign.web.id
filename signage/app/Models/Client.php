<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'clients';
    protected $fillable = ['nama', 'kode', 'aktif'];
    protected $casts = ['aktif' => 'boolean'];

    public function users()
    {
        return $this->hasMany(User::class, 'client_id');
    }
}
