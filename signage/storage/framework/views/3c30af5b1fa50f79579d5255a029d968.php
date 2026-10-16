<?php $__env->startSection('title', 'Tambah Distribusi Jadwal'); ?>
<?php $__env->startSection('page-title', 'Iklan > Distribusi > Tambah'); ?>

<?php $__env->startSection('content'); ?>


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
            <div class="icon-title-box"><i class="fas fa-plus-circle" style="font-size: 16px;"></i></div>
            Tambah Distribusi Jadwal Tayang
        </h5>
    </div>
    
    <div class="vortex-body-form">
        <form method="POST" action="<?php echo e(route('distribusi.lanjut')); ?>">
            <?php echo csrf_field(); ?>

            <?php if($errors->any()): ?>
                <div style="background:#fdecea;color:#c62828;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-weight:600;font-size:13px;">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div><?php echo e($e); ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>

            <div class="vortex-form-row">
                <div class="vortex-form-col">
                    <label class="vortex-label-locked">Jadwal Tayang <span style="color: #e74c3c;">*</span></label>
                    <select name="jadwal_tayang_id" class="vortex-input-locked" required>
                        <option value="">-- Pilih Jadwal Tayang --</option>
                        <?php $__currentLoopData = $jadwal; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($j->id); ?>" <?php echo e(old('jadwal_tayang_id', $draft['jadwal_tayang_id'] ?? null) == $j->id ? 'selected' : ''); ?>><?php echo e($j->nama_jadwal); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>

            <div class="vortex-form-row">
                <div class="vortex-form-col">
                    <label class="vortex-label-locked">Tanggal Mulai <span style="color: #e74c3c;">*</span></label>
                    <input type="date" name="tanggal_mulai" class="vortex-input-locked" value="<?php echo e(old('tanggal_mulai', $draft['tanggal_mulai'] ?? date('Y-m-d'))); ?>" required>
                </div>
                <div class="vortex-form-col">
                    <label class="vortex-label-locked">Tanggal Selesai <span style="color: #e74c3c;">*</span></label>
                    <input type="date" name="tanggal_selesai" class="vortex-input-locked" value="<?php echo e(old('tanggal_selesai', $draft['tanggal_selesai'] ?? date('Y-m-d', strtotime('+1 month')))); ?>" required>
                </div>
            </div>

            <div class="vortex-form-row">
                <div class="vortex-form-col">
                    <label class="vortex-label-locked">Waktu Mulai <span style="color: #e74c3c;">*</span></label>
                    <input type="text" name="waktu_mulai" class="vortex-input-locked flatpickr-time" value="<?php echo e(old('waktu_mulai', $draft['waktu_mulai'] ?? '08:00')); ?>" required>
                </div>
                <div class="vortex-form-col">
                    <label class="vortex-label-locked">Waktu Selesai <span style="color: #e74c3c;">*</span></label>
                    <input type="text" name="waktu_selesai" class="vortex-input-locked flatpickr-time" value="<?php echo e(old('waktu_selesai', $draft['waktu_selesai'] ?? '22:00')); ?>" required>
                </div>
            </div>

            <div class="vortex-form-row">
                <div class="vortex-form-col">
                    <label class="vortex-label-locked">Frekuensi Hari</label>
                    <select name="frekuensi_hari" class="vortex-input-locked">
                        <?php $__currentLoopData = ['Setiap Hari','Senin-Jumat','Sabtu-Minggu','Custom']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($f); ?>" <?php echo e(old('frekuensi_hari', $draft['frekuensi_hari'] ?? null) == $f ? 'selected' : ''); ?>><?php echo e($f); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="vortex-form-col">
                    <label class="vortex-label-locked">Status</label>
                    <div style="margin-top: 10px;">
                        <input type="hidden" name="aktif" value="0">
                        <label style="cursor:pointer; font-weight:700;">
                            <input type="checkbox" name="aktif" value="1" <?php echo e(($draft['aktif'] ?? 1) ? 'checked' : ''); ?> style="transform: scale(1.3); margin-right: 8px;"> Aktifkan Distribusi
                        </label>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 36px; padding-top: 24px; border-top: 1px solid var(--border-main, #e9ecef);">
                <button type="submit" class="vortex-btn-locked btn-save-v"><i class="fas fa-arrow-right"></i> Lanjut Pilih Lokasi</button>
                <a href="<?php echo e(route('distribusi.batal')); ?>" class="vortex-btn-locked btn-cancel-v"><i class="fas fa-times"></i> Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr(".flatpickr-time", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/novasgn/domains/vortexsign.web.id/signage/resources/views/distribusi/create.blade.php ENDPATH**/ ?>