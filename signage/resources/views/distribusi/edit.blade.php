@extends('layouts.app')
@section('title', 'Edit Distribusi Jadwal')
@section('page-title', 'Iklan > Distribusi Jadwal > Edit')

@section('content')

{{-- 💡 CSS DIKUNCI MATI AGAR 100% KONSISTEN --}}
<style>
    .vortex-card-form {
        background: var(--bg-card, #ffffff); border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        border: 1px solid var(--border-main, #e9ecef); overflow: hidden; width: 100%; max-width: 900px; margin-top: 10px;
    }
    .vortex-header-form { padding: 24px 24px 16px 24px; border-bottom: 1px solid var(--border-main, #e9ecef); display: flex; align-items: center; }
    .vortex-header-form h5 { margin: 0; font-weight: 800; color: var(--text-main, #2c3e50); font-size: 1.25rem; display: flex; align-items: center; }
    .icon-title-box { width: 42px; height: 42px; background: rgba(54, 185, 204, 0.15); color: #36b9cc; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 16px; }
    .vortex-body-form { padding: 30px 24px; }
    .vortex-label-locked { font-size: 12px; font-weight: 800; color: var(--text-muted, #7f8c8d); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 8px; display: block; }
    .vortex-input-locked { width: 100% !important; display: block !important; padding: 14px 16px; border: 1px solid var(--border-table, #cbd5e1); border-radius: 10px; font-size: 14px; font-weight: 600; background: var(--bg-input, #f8f9fa); color: var(--text-main, #2c3e50); transition: all 0.2s ease; box-sizing: border-box; }
    .vortex-input-locked:focus { outline: none; border-color: #36b9cc; box-shadow: 0 0 0 3px rgba(54, 185, 204, 0.15); background: var(--bg-card, #ffffff); }
    .vortex-btn-locked { padding: 12px 24px; border-radius: 8px; font-weight: 800; font-size: 13px; text-transform: uppercase; border: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s ease; cursor: pointer; text-decoration: none; }
    .btn-save-v { background: #2c3e50; color: #ffffff; box-shadow: 0 4px 10px rgba(44, 62, 80, 0.2); }
    .btn-cancel-v { background: #b2bec3; color: #ffffff; }
    .vortex-form-row { display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 24px; width: 100%; }
    .vortex-form-col { flex: 1; min-width: 250px; }
</style>

<div class="vortex-card-form">
    <div class="vortex-header-form">
        <h5>
            <div class="icon-title-box"><i class="fas fa-edit" style="font-size: 16px;"></i></div>
            Edit Distribusi: {{ $distribusi->digitalSignage?->nama_signage ?? 'Signage' }}
        </h5>
    </div>

    <div class="vortex-body-form">
        <form method="POST" action="{{ route('distribusi.update', $distribusi->id) }}">
            @csrf @method('PUT')

            @if($errors->any())
                <div style="background:#fdecea;color:#c62828;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-weight:600;font-size:13px;">
                    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                </div>
            @endif

            <div class="vortex-form-row">
                <div class="vortex-form-col">
                    <label class="vortex-label-locked">Signage / Layar <span style="color: #e74c3c;">*</span></label>
                    <select name="signage_id" class="vortex-input-locked" required>
                        @foreach($signage as $s)
                            <option value="{{ $s->id }}" {{ old('signage_id', $distribusi->signage_id) == $s->id ? 'selected' : '' }}>
                                {{ $s->nama_signage }} ({{ $s->toko?->nama_toko ?? 'Tanpa Toko' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="vortex-form-col">
                    <label class="vortex-label-locked">Frekuensi Hari</label>
                    <select name="frekuensi_hari" class="vortex-input-locked">
                        @foreach(['Setiap Hari','Senin-Jumat','Sabtu-Minggu','Custom'] as $f)
                            <option value="{{ $f }}" {{ old('frekuensi_hari', $distribusi->frekuensi_hari) == $f ? 'selected' : '' }}>{{ $f }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="vortex-form-row">
                <div class="vortex-form-col">
                    <label class="vortex-label-locked">Tanggal Mulai <span style="color: #e74c3c;">*</span></label>
                    <input type="date" name="tanggal_mulai" class="vortex-input-locked" value="{{ old('tanggal_mulai', $distribusi->tanggal_mulai?->format('Y-m-d')) }}" required>
                </div>
                <div class="vortex-form-col">
                    <label class="vortex-label-locked">Tanggal Selesai <span style="color: #e74c3c;">*</span></label>
                    <input type="date" name="tanggal_selesai" class="vortex-input-locked" value="{{ old('tanggal_selesai', $distribusi->tanggal_selesai?->format('Y-m-d')) }}" required>
                </div>
                <div class="vortex-form-col">
                    <label class="vortex-label-locked">Status</label>
                    <div style="margin-top: 10px;">
                        <input type="hidden" name="aktif" value="0">
                        <label style="cursor:pointer; font-weight:700;">
                            <input type="checkbox" name="aktif" value="1" {{ old('aktif', $distribusi->aktif) ? 'checked' : '' }} style="transform: scale(1.3); margin-right: 8px;"> Aktifkan Distribusi
                        </label>
                    </div>
                </div>
            </div>

            <div class="vortex-form-row">
                <div class="vortex-form-col">
                    <label class="vortex-label-locked">Waktu Mulai <span style="color: #e74c3c;">*</span></label>
                    <input type="text" name="waktu_mulai" class="vortex-input-locked flatpickr-time" value="{{ old('waktu_mulai', substr($distribusi->waktu_mulai, 0, 5)) }}" required>
                </div>
                <div class="vortex-form-col">
                    <label class="vortex-label-locked">Waktu Selesai <span style="color: #e74c3c;">*</span></label>
                    <input type="text" name="waktu_selesai" class="vortex-input-locked flatpickr-time" value="{{ old('waktu_selesai', substr($distribusi->waktu_selesai, 0, 5)) }}" required>
                </div>
            </div>

            @php
                $terpilih = old('items')
                    ? collect(old('items'))->values()
                    : $grup->map(fn ($g) => ['id' => $g->id, 'jadwal_tayang_id' => $g->jadwal_tayang_id])->values();
                $jumlahSlot = max(6, $terpilih->count());
            @endphp
            <div style="margin-bottom: 24px;">
                <label class="vortex-label-locked">Urutan Jadwal Tayang <span style="color: #e74c3c;">*</span></label>
                @for($i = 0; $i < $jumlahSlot; $i++)
                    @php $cur = $terpilih->get($i); @endphp
                    <div style="display: flex; align-items: center; margin-bottom: 12px;">
                        <span style="background:#f8f9fa; padding: 13px 15px; border: 1px solid #cbd5e1; border-radius: 10px 0 0 10px; font-weight:800; font-size:12px; width: 95px; white-space: nowrap;">Jadwal {{ $i + 1 }}</span>
                        <input type="hidden" name="items[{{ $i }}][id]" value="{{ $cur['id'] ?? '' }}">
                        <select name="items[{{ $i }}][jadwal_tayang_id]" class="vortex-input-locked" style="border-radius: 0 10px 10px 0;">
                            <option value="">-- Kosong --</option>
                            @foreach($jadwal as $j)
                                <option value="{{ $j->id }}" {{ ($cur['jadwal_tayang_id'] ?? null) == $j->id ? 'selected' : '' }}>{{ $j->nama_jadwal }}</option>
                            @endforeach
                        </select>
                    </div>
                @endfor
            </div>

            <div style="display: flex; gap: 12px; margin-top: 36px; padding-top: 24px; border-top: 1px solid var(--border-main, #e9ecef);">
                <button type="submit" class="vortex-btn-locked btn-save-v"><i class="fas fa-save"></i> Update Distribusi</button>
                <a href="{{ route('distribusi.index') }}" class="vortex-btn-locked btn-cancel-v"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        flatpickr(".flatpickr-time", { enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: true });
    });
</script>
@endsection