<?php $__env->startSection('title', 'Edit Jadwal Tayang'); ?>
<?php $__env->startSection('page-title', 'Iklan > Jadwal Tayang Iklan > Edit Jadwal'); ?>

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
            <div class="icon-title-box"><i class="fas fa-edit" style="font-size: 16px;"></i></div>
            Edit Jadwal: <?php echo e($jadwal->nama_jadwal); ?>

        </h5>
    </div>
    
    <div class="vortex-body-form">
        <form method="POST" action="<?php echo e(route('jadwal.update', $jadwal->id)); ?>">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            
            <div style="margin-bottom: 24px; padding: 12px 16px; border-radius: 10px; background: rgba(243,156,18,0.12); border: 1px solid rgba(243,156,18,0.4); font-size: 13px; font-weight: 600; color: var(--text-main, #2c3e50);">
                <i class="fas fa-info-circle" style="color:#f39c12; margin-right:6px;"></i>
                Perubahan jadwal ini <b>tidak langsung tayang di TV</b>. Distribusikan ke TV lewat menu distribusi
            </div>

            <div style="margin-bottom: 24px;">
                <label class="vortex-label-locked">Nama Jadwal Tayang <span style="color: #e74c3c;">*</span></label>
                <input type="text" name="nama_jadwal" class="vortex-input-locked" value="<?php echo e(old('nama_jadwal', $jadwal->nama_jadwal)); ?>" required>
            </div>

            <div style="margin-bottom: 24px;">
                <label class="vortex-label-locked">Keterangan</label>
                <textarea name="keterangan" class="vortex-input-locked" rows="3"><?php echo e(old('keterangan', $jadwal->keterangan)); ?></textarea>
            </div>

            <div style="margin-bottom: 24px;">
                <label class="vortex-label-locked">Urutan Video Iklan <span style="color: #e74c3c;">*</span></label>
                <div id="selectedVideosContainer" style="display:none;"></div>
                
                <div style="display: flex; flex-direction: column; gap: 12px; width: 100%;">
                    <?php for($i = 1; $i <= 10; $i++): ?>
                    <div style="width: 100%;">
                        <div style="display: flex; align-items: center; width: 100%;">
                            <span style="background:#f8f9fa; padding: 13px 15px; border: 1px solid #cbd5e1; border-right: none; border-radius: 10px 0 0 10px; font-weight:800; font-size:12px; width: 85px; flex-shrink: 0; box-sizing: border-box; color: var(--text-main, #2c3e50);">Video <?php echo e($i); ?></span>
                            <select class="vortex-input-locked video-select" data-position="<?php echo e($i); ?>" onchange="updateHiddenInput(this)" style="border-radius: 0 10px 10px 0; flex: 1; min-width: 0;">
                                <option value="">-- Pilih Video --</option>
                                <?php $__currentLoopData = $fileIklan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $iklan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $position = array_search($iklan->id, $selectedFileIds ?? []);
                                        $isSelected = ($position !== false && $position + 1 == $i);
                                    ?>
                                    <option value="<?php echo e($iklan->id); ?>" <?php echo e($isSelected ? 'selected' : ''); ?>>
                                        <?php echo e($iklan->nama_iklan); ?> -- <?php echo e($iklan->pelanggan); ?> (<?php echo e($iklan->tipe); ?>)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 36px; padding-top: 24px; border-top: 1px solid var(--border-main, #e9ecef);">
                <button type="submit" class="vortex-btn-locked btn-save-v"><i class="fas fa-save"></i> Update Jadwal</button>
                <a href="<?php echo e(route('jadwal.index')); ?>" class="vortex-btn-locked btn-cancel-v"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
</div>

<script>
function updateHiddenInput(select) {
    var pos = select.dataset.position;
    var container = document.getElementById('selectedVideosContainer');
    container.querySelectorAll(`input[data-pos="${pos}"]`).forEach(el => el.remove());
    if (select.value) {
        var input = document.createElement('input');
        input.type = 'hidden'; input.name = 'file_iklan_ids[]';
        input.value = select.value; input.dataset.pos = pos;
        container.appendChild(input);
    }
}
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.video-select').forEach(s => { if(s.value) updateHiddenInput(s); });
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/novasgn/domains/vortexsign.web.id/signage/resources/views/jadwal/edit.blade.php ENDPATH**/ ?>