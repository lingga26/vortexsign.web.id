<?php $__env->startSection('title', 'Pilih Lokasi Tayang'); ?>
<?php $__env->startSection('page-title', 'Iklan > Distribusi > Pilih Lokasi Tayang'); ?>

<?php $__env->startSection('content'); ?>

<style>
    .vortex-card-index { background: var(--bg-card, #ffffff); border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); border: 1px solid var(--border-main, #e9ecef); overflow: hidden; width: 100%; margin-top: 10px; }
    .lokasi-info { padding: 20px 24px; border-bottom: 1px solid var(--border-main, #e9ecef); display: flex; gap: 40px; flex-wrap: wrap; }
    .lokasi-info .lbl { font-size: 12px; font-weight: 800; color: var(--text-muted, #7f8c8d); text-transform: uppercase; letter-spacing: .8px; margin-bottom: 4px; }
    .lokasi-info .val { font-weight: 700; color: var(--text-main, #2c3e50); font-size: 14px; }
    .lokasi-toolbar { padding: 16px 24px; display: flex; gap: 12px; align-items: center; justify-content: space-between; flex-wrap: wrap; }
    .table-vortex { width: 100% !important; border-collapse: collapse !important; margin: 0 !important; }
    .table-vortex th { background-color: var(--bg-table-th, #f8f9fa) !important; color: var(--text-main, #2c3e50) !important; font-weight: 800 !important; border-bottom: 2px solid var(--border-main, #e9ecef) !important; text-transform: uppercase !important; font-size: 0.75rem !important; letter-spacing: 0.8px !important; padding: 14px 16px !important; vertical-align: middle !important; }
    .table-vortex td { padding: 12px 16px !important; border-bottom: 1px solid var(--border-table, #cbd5e1) !important; color: var(--text-main, #2c3e50) !important; font-weight: 600 !important; font-size: 0.85rem !important; }
    .table-vortex tbody tr:hover td { background-color: rgba(114, 200, 101, 0.05) !important; cursor: pointer; }
    .table-vortex input[type=checkbox] { transform: scale(1.25); cursor: pointer; }
    .vortex-btn-locked { padding: 12px 24px; border-radius: 8px; font-weight: 800; font-size: 13px; text-transform: uppercase; border: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; text-decoration: none; }
    .btn-save-v { background: #2c3e50; color: #fff; box-shadow: 0 4px 10px rgba(44,62,80,.2); }
    .btn-back-v { background: #b2bec3; color: #fff; }
    .btn-cancel-v { background: #e74c3c; color: #fff; }
    .badge-ganti { background: rgba(243,156,18,.15); color: #b9770e; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: .72rem; }
</style>

<div class="vortex-card-index">
    <div class="lokasi-info">
        <div><div class="lbl">Jadwal Tayang</div><div class="val"><?php echo e($jadwal->nama_jadwal); ?></div></div>
        <div><div class="lbl">Waktu</div><div class="val"><?php echo e(substr($draft['waktu_mulai'], 0, 5)); ?> - <?php echo e(substr($draft['waktu_selesai'], 0, 5)); ?> (<?php echo e($draft['frekuensi_hari']); ?>)</div></div>
        <div><div class="lbl">Periode</div><div class="val"><?php echo e(\Carbon\Carbon::parse($draft['tanggal_mulai'])->format('d-m-Y')); ?> s/d <?php echo e(\Carbon\Carbon::parse($draft['tanggal_selesai'])->format('d-m-Y')); ?></div></div>
        <div><div class="lbl">Konten</div><div class="val"><?php echo e($jadwal->fileIklan->count()); ?> File</div></div>
    </div>

    <form method="POST" action="<?php echo e(route('distribusi.lokasi.simpan')); ?>" id="formLokasi">
        <?php echo csrf_field(); ?>

        <?php if($errors->any()): ?>
            <div style="background:#fdecea;color:#c62828;padding:12px 16px;margin:16px 24px 0;border-radius:8px;font-weight:600;font-size:13px;">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div><?php echo e($e); ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <div class="lokasi-toolbar">
            <div style="font-weight:800;color:var(--text-main,#2c3e50);">Lokasi Tayang
                <span id="counter" style="font-weight:600;color:var(--text-muted,#7f8c8d);margin-left:8px;">0 dipilih</span>
            </div>
            <input type="text" id="filter" class="form-control" placeholder="Cari toko / signage / kota..." style="max-width:320px;">
        </div>

        <div class="table-responsive">
            <table class="table-vortex">
                <thead>
                    <tr>
                        <th style="width:4%;text-align:center;"><input type="checkbox" id="checkAll" title="Pilih semua"></th>
                        <th>Kode Toko</th>
                        <th>Nama Toko</th>
                        <th>Provinsi / Kota</th>
                        <th>Label / Nama Signage</th>
                        <th>Kode Signage</th>
                        <th>Jadwal Saat Ini</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $signage; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $sekarang = $s->distribusiJadwal; ?>
                    <tr>
                        <td style="text-align:center;"><input type="checkbox" class="chk" name="signage_ids[]" value="<?php echo e($s->id); ?>" <?php echo e(in_array($s->id, old('signage_ids', [])) ? 'checked' : ''); ?>></td>
                        <td><?php echo e($s->toko?->kode_toko ?? '-'); ?></td>
                        <td><?php echo e(strtoupper($s->toko?->nama_toko ?? 'Tanpa Toko')); ?></td>
                        <td><?php echo e(strtoupper(($s->toko?->provinsi ?? '-') . ' > ' . ($s->toko?->kota ?? '-'))); ?></td>
                        <td><?php echo e(strtoupper($s->nama_signage)); ?></td>
                        <td><?php echo e($s->device_id); ?></td>
                        <td>
                            <?php $__empty_2 = true; $__currentLoopData = $sekarang; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                <span class="badge-ganti" style="<?php echo e($dj->jadwal_tayang_id == $jadwal->id ? 'background:rgba(231,76,60,.15);color:#c62828;' : ''); ?>" title="<?php echo e($dj->jadwal_tayang_id == $jadwal->id ? 'Akan diganti' : 'Tetap'); ?>"><?php echo e($dj->jadwalTayang?->nama_jadwal ?? '-'); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                <span style="color:var(--text-muted,#7f8c8d);">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted,#7f8c8d);">Belum ada signage terdaftar.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="display:flex;gap:12px;padding:24px;border-top:1px solid var(--border-main,#e9ecef);">
            <a href="<?php echo e(route('distribusi.create')); ?>" class="vortex-btn-locked btn-back-v"><i class="fas fa-arrow-left"></i> Kembali</a>
            <button type="submit" class="vortex-btn-locked btn-save-v"><i class="fas fa-paper-plane"></i> Lanjut</button>
            <a href="<?php echo e(route('distribusi.batal')); ?>" class="vortex-btn-locked btn-cancel-v"><i class="fas fa-times"></i> Batal</a>
        </div>
        <div style="padding:0 24px 20px;font-style:italic;font-size:13px;color:var(--text-muted,#7f8c8d);">
            Satu signage dapat memiliki banyak jadwal tayang. Jika lokasi yang dipilih sudah memiliki jadwal tayang yang sama (warna merah), jadwal tersebut akan diganti dengan pengaturan ini; jadwal tayang lainnya tetap.
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkAll = document.getElementById('checkAll');
    const rows     = Array.from(document.querySelectorAll('.table-vortex tbody tr'));
    const counter  = document.getElementById('counter');
    const visible  = () => rows.filter(r => r.style.display !== 'none').map(r => r.querySelector('.chk')).filter(Boolean);

    function update() {
        const all = document.querySelectorAll('.chk');
        counter.textContent = Array.from(all).filter(c => c.checked).length + ' dipilih';
        const v = visible();
        checkAll.checked = v.length > 0 && v.every(c => c.checked);
    }
    checkAll.addEventListener('change', () => { visible().forEach(c => c.checked = checkAll.checked); update(); });
    document.querySelectorAll('.chk').forEach(c => c.addEventListener('change', update));
    rows.forEach(r => r.addEventListener('click', e => {
        const c = r.querySelector('.chk');
        if (c && e.target !== c) { c.checked = !c.checked; update(); }
    }));
    document.getElementById('filter').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        rows.forEach(r => r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none');
        update();
    });
    update();
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/novasgn/domains/vortexsign.web.id/signage/resources/views/distribusi/lokasi.blade.php ENDPATH**/ ?>