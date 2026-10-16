<?php

namespace App\Http\Controllers;

use App\Models\DistribusiJadwal;
use App\Models\DigitalSignage;
use App\Models\JadwalTayang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class DistribusiJadwalController extends Controller
{
    public function index(Request $request)
    {
        $query = DistribusiJadwal::with(['digitalSignage.toko', 'jadwalTayang']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($w) use ($search) {
                $w->whereHas('digitalSignage', function ($q) use ($search) {
                    $q->where('nama_signage', 'like', "%$search%")
                      ->orWhere('device_id', 'like', "%$search%");
                })
                ->orWhereHas('digitalSignage.toko', function ($q) use ($search) {
                    $q->where('nama_toko', 'like', "%$search%")
                      ->orWhere('area', 'like', "%$search%");
                })
                ->orWhereHas('jadwalTayang', function ($q) use ($search) {
                    $q->where('nama_jadwal', 'like', "%$search%");
                });
            });
        }

        // Satu baris = satu signage + periode + frekuensi + jam tayang yang sama,
        // di dalamnya bisa berisi banyak jadwal tayang.
        $grouped = $query->orderBy('created_at', 'desc')->get()
            ->groupBy(fn ($d) => implode('|', [
                $d->signage_id,
                $d->tanggal_mulai?->format('Y-m-d'),
                $d->tanggal_selesai?->format('Y-m-d'),
                $d->frekuensi_hari, $d->frekuensi_tanggal, $d->frekuensi_bulan,
                $d->waktu_mulai, $d->waktu_selesai,
            ]))
            ->values();

        $perPage = 10;
        $page    = LengthAwarePaginator::resolveCurrentPage();
        $distribusi = new LengthAwarePaginator(
            $grouped->forPage($page, $perPage)->values(),
            $grouped->count(), $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('distribusi.index', compact('distribusi'));
    }

    // STEP 1: Form jadwal (tanpa pilih signage)
    public function create()
    {
        $jadwal = JadwalTayang::all();
        $draft  = session('distribusi_draft', []);
        return view('distribusi.create', compact('jadwal', 'draft'));
    }

    // STEP 1 -> STEP 2: simpan draft ke session, lanjut pilih lokasi
    public function lanjut(Request $request)
    {
        $validated = $request->validate([
            'jadwal_tayang_id' => ['required', \App\Support\Tenant::exists('jadwal_tayang')],
            'tanggal_mulai'    => 'required|date',
            'tanggal_selesai'  => 'required|date|after_or_equal:tanggal_mulai',
            'waktu_mulai'      => 'required',
            'waktu_selesai'    => 'required',
            'frekuensi_hari'   => 'required',
            'aktif'            => 'required',
        ]);

        session(['distribusi_draft' => $validated]);
        return redirect()->route('distribusi.lokasi');
    }

    // STEP 2: tabel pilih lokasi tayang / signage
    public function lokasi()
    {
        $draft = session('distribusi_draft');
        if (!$draft) {
            return redirect()->route('distribusi.create');
        }

        $jadwal = JadwalTayang::with('fileIklan')->findOrFail($draft['jadwal_tayang_id']);

        $signage = DigitalSignage::with(['toko', 'distribusiJadwal.jadwalTayang'])
            ->get()
            ->sortBy(fn ($s) => sprintf('%s|%s|%s|%s',
                $s->toko?->provinsi ?? 'ZZZ',
                $s->toko?->kota ?? '',
                $s->toko?->nama_toko ?? '',
                $s->nama_signage))
            ->values();

        return view('distribusi.lokasi', compact('draft', 'jadwal', 'signage'));
    }

    // STEP 2 -> simpan: buat distribusi untuk semua signage terpilih & push ke TV
    public function simpanLokasi(Request $request)
    {
        $draft = session('distribusi_draft');
        if (!$draft) {
            return redirect()->route('distribusi.create');
        }

        $request->validate([
            'signage_ids'   => 'required|array|min:1',
            'signage_ids.*' => [\App\Support\Tenant::exists('digital_signage')],
        ], [
            'signage_ids.required' => 'Pilih minimal satu lokasi tayang.',
            'signage_ids.min'      => 'Pilih minimal satu lokasi tayang.',
        ]);

        $ids = array_unique($request->signage_ids);

        DB::transaction(function () use ($ids, $draft) {
            // Jadwal tayang yang sama pada signage terpilih diganti; jadwal tayang lain tetap
            DistribusiJadwal::whereIn('signage_id', $ids)
                ->where('jadwal_tayang_id', $draft['jadwal_tayang_id'])
                ->delete();

            foreach ($ids as $id) {
                DistribusiJadwal::create($draft + ['signage_id' => $id]);
            }

            // Tandai TV agar menarik (push) konten terbaru
            DigitalSignage::whereIn('id', $ids)->update([
                'needs_update'  => 1,
                'status_konten' => 'Belum Terbaru',
            ]);
        });

        session()->forget('distribusi_draft');

        return redirect()->route('distribusi.index')
            ->with('success', count($ids) . ' signage berhasil didistribusikan dan konten akan dikirim ke TV.');
    }

    public function batal()
    {
        session()->forget('distribusi_draft');
        return redirect()->route('distribusi.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'signage_id'        => 'required',
            'jadwal_tayang_id'  => 'required',
            'tanggal_mulai'     => 'required|date',
            'tanggal_selesai'   => 'required|date|after_or_equal:tanggal_mulai',
            'waktu_mulai'       => 'required',
            'waktu_selesai'     => 'required',
            'frekuensi_hari'    => 'required',
            'aktif'             => 'required'
        ]);

        DistribusiJadwal::create($validated);
        return redirect()->route('distribusi.index')->with('success', 'Berhasil disimpan!');
    }

    // Baris-baris distribusi yang satu grup (signage + periode + frekuensi + jam tayang sama)
    private function grupDistribusi(DistribusiJadwal $d)
    {
        return DistribusiJadwal::where('signage_id', $d->signage_id)
            ->whereDate('tanggal_mulai', $d->tanggal_mulai)
            ->whereDate('tanggal_selesai', $d->tanggal_selesai)
            ->where('frekuensi_hari', $d->frekuensi_hari)
            ->where('frekuensi_tanggal', $d->frekuensi_tanggal)
            ->where('frekuensi_bulan', $d->frekuensi_bulan)
            ->where('waktu_mulai', $d->waktu_mulai)
            ->where('waktu_selesai', $d->waktu_selesai)
            ->orderBy('waktu_mulai')->orderBy('id');
    }

    public function edit(DistribusiJadwal $distribusi)
    {
        $signage = DigitalSignage::with('toko')->get();
        $jadwal  = JadwalTayang::all();
        $grup    = $this->grupDistribusi($distribusi)->get();
        return view('distribusi.edit', compact('distribusi', 'signage', 'jadwal', 'grup'));
    }

    public function update(Request $request, DistribusiJadwal $distribusi)
    {
        $validated = $request->validate([
            'signage_id'                 => ['required', \App\Support\Tenant::exists('digital_signage')],
            'tanggal_mulai'              => 'required|date',
            'tanggal_selesai'            => 'required|date|after_or_equal:tanggal_mulai',
            'waktu_mulai'                => 'required',
            'waktu_selesai'              => 'required',
            'frekuensi_hari'             => 'required',
            'aktif'                      => 'required',
            'items'                      => 'nullable|array',
            'items.*.id'                 => 'nullable|integer',
            'items.*.jadwal_tayang_id'   => ['nullable', \App\Support\Tenant::exists('jadwal_tayang')],
        ]);

        // Slot kosong diabaikan
        $items = collect($validated['items'] ?? [])->filter(fn ($i) => !empty($i['jadwal_tayang_id']))->values()->all();
        if (empty($items)) {
            return back()->withInput()->withErrors(['items' => 'Pilih minimal satu jadwal tayang.']);
        }
        $validated['items'] = $items;

        $common = collect($validated)->only(['signage_id', 'tanggal_mulai', 'tanggal_selesai', 'waktu_mulai', 'waktu_selesai', 'frekuensi_hari', 'aktif'])->all();
        $signageLama = $distribusi->signage_id;

        DB::transaction(function () use ($validated, $common, $distribusi) {
            $grup    = $this->grupDistribusi($distribusi)->get()->keyBy('id');
            $dipakai = [];

            foreach ($validated['items'] as $item) {
                $data = $common + ['jadwal_tayang_id' => $item['jadwal_tayang_id']];

                if (!empty($item['id']) && $grup->has($item['id'])) {
                    $grup[$item['id']]->update($data);   // jadwal lama diubah
                    $dipakai[] = (int) $item['id'];
                } else {
                    $data['frekuensi_tanggal'] = $distribusi->frekuensi_tanggal;
                    $data['frekuensi_bulan']   = $distribusi->frekuensi_bulan;
                    $dipakai[] = DistribusiJadwal::create($data)->id;   // jadwal baru
                }
            }

            // Jadwal yang dihapus dari form ikut dihapus
            DistribusiJadwal::whereIn('id', $grup->keys())->whereNotIn('id', $dipakai)->delete();
        });

        // Push ke TV (signage lama & baru jika berpindah)
        DigitalSignage::whereIn('id', array_unique([$signageLama, (int) $validated['signage_id']]))
            ->update(['needs_update' => 1, 'status_konten' => 'Belum Terbaru']);

        return redirect()->route('distribusi.index')->with('success', 'Distribusi berhasil diperbarui.');
    }

    public function destroy(DistribusiJadwal $distribusi)
    {
        // Hapus seluruh jadwal tayang dalam baris (grup) yang sama, lalu push ke TV
        $signageId = $distribusi->signage_id;
        $this->grupDistribusi($distribusi)->delete();

        DigitalSignage::where('id', $signageId)->update(['needs_update' => 1, 'status_konten' => 'Belum Terbaru']);

        return redirect()->route('distribusi.index')->with('success', 'Berhasil dihapus!');
    }
}