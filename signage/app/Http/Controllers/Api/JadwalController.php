<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DistribusiJadwal;
use App\Models\JadwalTayang;
use Illuminate\Http\Request;
use Carbon\Carbon;

class JadwalController extends Controller
{
    public function getJadwal(Request $request, string $deviceId)
    {
        $device    = $request->get('_device');
        $today     = now()->toDateString();
        $dayOfWeek = now()->dayOfWeek;

        // Ambil list distribusi jadwal yang aktif untuk perangkat ini
        $distribusiList = DistribusiJadwal::where('signage_id', $device->id)
            ->where('aktif', true)
            ->where('tanggal_mulai', '<=', $today)
            ->where('tanggal_selesai', '>=', $today)
            ->with(['jadwalTayang.fileIklan'])
            ->get();

        $distribusiAktif = $distribusiList->filter(function ($dist) use ($dayOfWeek) {
            return match ($dist->frekuensi_hari) {
                'Setiap Hari'  => true,
                'Senin-Jumat'  => $dayOfWeek >= 1 && $dayOfWeek <= 5,
                'Sabtu-Minggu' => $dayOfWeek === 0 || $dayOfWeek === 6,
                default        => true,
            };
        });

        if ($distribusiAktif->isEmpty()) {
            return response()->json(['success' => true, 'jadwal' => [], 'versi' => null]);
        }

        $jadwalResponse = [];
        foreach ($distribusiAktif as $dist) {
            
            // 🛡️ PROTEKSI 1: Antisipasi kolom ganda (jadwal_tayang_id vs jadwal_id)
            $jadwal = $dist->jadwalTayang;
            if (!$jadwal) {
                $idJadwalAlternatif = $dist->jadwal_tayang_id ?? $dist->jadwal_id;
                if ($idJadwalAlternatif) {
                    $jadwal = JadwalTayang::with('fileIklan')->find($idJadwalAlternatif);
                }
            }

            // Jika setelah dicari lewat jalur alternatif tetap tidak ada, skip ke baris berikutnya (Anti-500)
            if (!$jadwal) {
                continue;
            }

            $videos = [];
            $fileIklanList = $jadwal->fileIklan ?? collect();

            foreach ($fileIklanList as $index => $file) {
                
                // 🛡️ PROTEKSI 2: Antisipasi jika properti 'urutan' tidak termuat di dalam object pivot Model
                $nomorUrutan = $index + 1; // Default fallback jika urutan kosong
                if ($file->pivot && isset($file->pivot->urutan)) {
                    $nomorUrutan = $file->pivot->urutan;
                }

                $videos[] = [
                    'id'           => $file->id,
                    'nama_iklan'   => $file->nama_iklan,
                    'durasi_detik' => $file->durasi_detik,
                    'ukuran_bytes' => $file->ukuran_bytes,
                    'urutan'       => $nomorUrutan,
                    // ✨ FIX: Mengubah path URL dari /storage/ menjadi /media/ agar video tidak 404
                    'video_url'    => $file->file_path ? config('app.url') . '/media/' . $file->file_path : null,
                    'has_video'    => $file->has_video,
                ];
            }
            
            // Urutkan video berdasarkan nomor urutan tayang
            usort($videos, fn($a, $b) => $a['urutan'] <=> $b['urutan']);

            // 🛡️ PROTEKSI 3: Cek validitas format tanggal sebelum di-render ke Android TV
            $tglMulai   = $dist->tanggal_mulai ? Carbon::parse($dist->tanggal_mulai)->format('Y-m-d') : $today;
            $tglSelesai = $dist->tanggal_selesai ? Carbon::parse($dist->tanggal_selesai)->format('Y-m-d') : $today;

            $jadwalResponse[] = [
                'distribusi_id'  => $dist->id,
                'nama_jadwal'    => $jadwal->nama_jadwal,
                'waktu_mulai'    => $dist->waktu_mulai ? substr($dist->waktu_mulai, 0, 5) : "00:00",
                'waktu_selesai'  => $dist->waktu_selesai ? substr($dist->waktu_selesai, 0, 5) : "23:59",
                'tanggal_mulai'  => $tglMulai,
                'tanggal_selesai'=> $tglSelesai,
                'videos'         => $videos,
            ];
        }

        // Generate Hash Versi Konten berdasarkan susunan ID video aktif
        $versi = md5(json_encode(
            collect($jadwalResponse)->pluck('videos')->flatten(1)->pluck('id')->sort()->values()
        ));

        return response()->json([
            'success'     => true,
            'device_id'   => $deviceId,
            'server_time' => now()->toIso8601String(),
            'versi'       => $versi,
            'orientasi'   => $device->orientasi,
            'jadwal'      => $jadwalResponse,
        ]);
    }
}