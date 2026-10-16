<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalSignage extends Model
{
    use \App\Models\Concerns\BelongsToClient;

    protected $table = 'digital_signage';

    protected $fillable = [
        'client_id',
        'nama_signage',
        'device_id',
        'serial_number',
        'device_token',
        'toko_id',
        'model_signage_id',
        'resolusi_width',
        'resolusi_height',
        'orientasi',
        'status',
        'status_konten',
        'koneksi_status',
        'last_ping',
        'tanggal_aktivasi',
        'versi_konten',
        'unduh_terakhir',
        'capture_terakhir',
        'needs_update',
    ];

    protected $casts = [
        'last_ping' => 'datetime',
        'tanggal_aktivasi' => 'date',
        'unduh_terakhir' => 'datetime',
        'capture_terakhir' => 'datetime',
        'needs_update' => 'boolean',
        'resolusi_width' => 'integer',
        'resolusi_height' => 'integer',
    ];

  // ... (kode casts yang lama) ...

    // ✨ TAMBAHKAN FUNGSI INI: Untuk menentukan Online/Offline berdasarkan last_ping
    public function getStatusKoneksiAttribute()
    {
        if (!$this->last_ping) {
            return 'Offline';
        }

        // Jika ping terakhir kurang dari atau sama dengan 3 menit, anggap Online
        return now()->diffInMinutes($this->last_ping) <= 3 ? 'Online' : 'Offline';
    }

    // ✅ RELASI KE TOKO
    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }
    // ...

    // ✅ RELASI KE MODEL SIGNAGE
    public function modelSignage(): BelongsTo
    {
        return $this->belongsTo(ModelSignage::class, 'model_signage_id');
    }

    // ✅ RELASI KE DISTRIBUSI JADWAL (pakai 'signage_id' sebagai foreign key)
    public function distribusiJadwal(): HasMany
    {
        return $this->hasMany(DistribusiJadwal::class, 'signage_id');
    }

    /**
     * Aktivasi manual oleh admin client: jika perangkat sudah mendaftar sendiri lewat API
     * (client_id masih kosong), perangkat itu "diklaim" oleh client yang sedang login.
     */
    public static function claimOrCreate(string $deviceId, array $data): self
    {
        $pending = static::withoutGlobalScopes()
            ->where('device_id', $deviceId)->whereNull('client_id')->first();

        if ($pending) {
            $pending->fill($data);
            $pending->client_id = \App\Support\Tenant::id();
            $pending->save();
            return $pending;
        }

        return static::updateOrCreate(['device_id' => $deviceId], $data);
    }
}
