

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    
    <?php if(session('success')): ?>
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl shadow-sm" role="alert">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-sm font-bold"><?php echo e(session('success')); ?></p>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl shadow-sm" role="alert">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v3.75m0 3.75h.007M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-sm font-bold"><?php echo e(session('error')); ?></p>
        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl shadow-sm">
            <p class="text-sm font-bold mb-1">Periksa kembali isian kamu:</p>
            <ul class="list-disc list-inside text-sm">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-black text-gray-900">Kelola Admin</h1>
            <p class="text-sm text-gray-500 mt-0.5">Atur siapa saja yang bisa masuk ke panel admin dan halaman apa saja yang boleh mereka akses.</p>
        </div>
        <button type="button" onclick="openCreateModal()"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-gray-900 hover:bg-gray-800 active:scale-95 transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Tambah Admin
        </button>
    </div>

    
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Owner</p>
            <h3 class="text-xl font-black text-gray-900 mt-0.5"><?php echo e($totalOwner); ?> Akun</h3>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Admin</p>
            <h3 class="text-xl font-black text-gray-900 mt-0.5"><?php echo e($totalAdmin); ?> Akun</h3>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Akun Aktif</p>
            <h3 class="text-xl font-black text-gray-900 mt-0.5"><?php echo e($totalAktif); ?> Akun</h3>
        </div>
    </div>

    
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Hak Akses Menu</th>
                        <th class="px-6 py-3 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-[11px] font-bold text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php $__empty_1 = true; $__currentLoopData = $admins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $admin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                <?php echo e($admin->name); ?>

                                <?php if($admin->id === auth()->id()): ?>
                                    <span class="text-[10px] font-bold text-gray-400">(kamu)</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo e($admin->email); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($admin->role === 'owner'): ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-violet-50 text-violet-700 border border-violet-100">Owner</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-100">Admin</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if($admin->role === 'owner'): ?>
                                    <span class="text-xs font-semibold text-gray-400">Semua halaman (full access)</span>
                                <?php elseif(empty($admin->admin_permissions)): ?>
                                    <span class="text-xs font-semibold text-gray-400">Belum ada akses diberikan</span>
                                <?php else: ?>
                                    <div class="flex flex-wrap gap-1 max-w-sm">
                                        <?php $__currentLoopData = $admin->admin_permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-600"><?php echo e(\App\Support\AdminMenu::label($key)); ?></span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($admin->status_aktif): ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-500 border border-gray-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span> Nonaktif
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <?php
                                        $adminData = [
                                            'id' => $admin->id,
                                            'name' => $admin->name,
                                            'email' => $admin->email,
                                            'role' => $admin->role,
                                            'status_aktif' => (bool) $admin->status_aktif,
                                            'admin_permissions' => $admin->admin_permissions ?? [],
                                        ];
                                    ?>
                                    <button type="button"
                                        onclick="openEditModal(<?php echo e(\Illuminate\Support\Js::from($adminData)); ?>)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-gray-700 bg-gray-50 border border-gray-200 hover:bg-gray-100 active:scale-95 transition-all cursor-pointer shadow-sm"
                                        title="Edit Akun">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                        Edit
                                    </button>

                                    <?php if($admin->id === auth()->id() || ($admin->role === 'owner' && $totalOwner <= 1)): ?>
                                        <button type="button" disabled
                                            title="<?php echo e($admin->id === auth()->id() ? 'Tidak bisa menghapus akun sendiri' : 'Tidak bisa menghapus owner terakhir'); ?>"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-gray-400 bg-gray-50 border border-gray-200 cursor-not-allowed shadow-sm">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Hapus
                                        </button>
                                    <?php else: ?>
                                        <form action="<?php echo e(route('admin.kelola-admin.destroy', $admin->id)); ?>" method="POST" class="inline"
                                              onsubmit="return confirm('Yakin ingin menghapus akun <?php echo e($admin->name); ?>?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-rose-600 bg-rose-50/50 border border-rose-100 hover:bg-rose-100 active:scale-95 transition-all cursor-pointer shadow-sm" title="Hapus Akun">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                Hapus
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400">Belum ada akun admin.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div id="adminModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-900/40 p-4">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl max-h-[90vh] overflow-y-auto">
        <form id="adminForm" method="POST" action="<?php echo e(route('admin.kelola-admin.store')); ?>">
            <?php echo csrf_field(); ?>
            <div id="adminMethodField"></div>
            <input type="hidden" name="editing_id" id="field_editing_id" value="<?php echo e(old('editing_id')); ?>">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 id="adminModalTitle" class="text-base font-black text-gray-900">Tambah Admin</h2>
                <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1.5">Nama</label>
                    <input type="text" name="name" id="field_name" required value="<?php echo e(old('name')); ?>"
                        class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1.5">Email</label>
                    <input type="email" name="email" id="field_email" required value="<?php echo e(old('email')); ?>"
                        class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1.5">Password <span id="passwordHint" class="font-normal text-gray-400"></span></label>
                        <input type="password" name="password" id="field_password"
                            class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1.5">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" id="field_password_confirmation"
                            class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1.5">Role</label>
                    <select name="role" id="field_role" onchange="togglePermissionBlock()"
                        class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400">
                        <option value="admin" <?php if(old('role', 'admin') === 'admin'): echo 'selected'; endif; ?>>Admin (akses dibatasi menu di bawah)</option>
                        <?php if($canAssignOwner): ?>
                            <option value="owner" <?php if(old('role') === 'owner'): echo 'selected'; endif; ?>>Owner (akses penuh semua menu)</option>
                        <?php endif; ?>
                    </select>
                </div>

                <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                    <input type="checkbox" name="status_aktif" id="field_status_aktif" value="1" <?php if(old('status_aktif', true)): echo 'checked'; endif; ?>
                        class="rounded border-gray-300 text-gray-900 focus:ring-gray-900/20">
                    Akun aktif (bisa login)
                </label>

                <div id="permissionBlock">
                    <label class="block text-xs font-bold text-gray-500 mb-2">Halaman yang Boleh Diakses</label>
                    <div class="space-y-3 max-h-60 overflow-y-auto border border-gray-100 rounded-xl p-3">
                        <?php $__currentLoopData = $menuGrouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div>
                                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5"><?php echo e($group); ?></p>
                                <div class="grid grid-cols-2 gap-1.5">
                                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <label class="flex items-center gap-1.5 text-xs font-semibold text-gray-700">
                                            <input type="checkbox" name="admin_permissions[]" value="<?php echo e($key); ?>" <?php if(in_array($key, old('admin_permissions', []))): echo 'checked'; endif; ?> class="permission-checkbox rounded border-gray-300 text-gray-900 focus:ring-gray-900/20">
                                            <?php echo e($label); ?>

                                        </label>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-gray-100">
                <button type="button" onclick="closeModal()" class="px-4 py-2.5 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-100">Batal</button>
                <button type="submit" class="px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-gray-900 hover:bg-gray-800">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    const adminModal = document.getElementById('adminModal');
    const adminForm = document.getElementById('adminForm');

    function togglePermissionBlock() {
        const role = document.getElementById('field_role').value;
        document.getElementById('permissionBlock').classList.toggle('hidden', role === 'owner');
    }

    function resetForm() {
        adminForm.reset();
        document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('adminMethodField').innerHTML = '';
        document.getElementById('field_editing_id').value = '';
        document.getElementById('field_password').required = true;
        document.getElementById('field_password_confirmation').required = true;
        document.getElementById('passwordHint').textContent = '';
        togglePermissionBlock();
    }

    function openCreateModal() {
        resetForm();
        document.getElementById('adminModalTitle').textContent = 'Tambah Admin';
        adminForm.action = "<?php echo e(route('admin.kelola-admin.store')); ?>";
        adminModal.classList.remove('hidden');
    }

    function openEditModal(data) {
        resetForm();
        document.getElementById('adminModalTitle').textContent = 'Edit Admin';
        adminForm.action = "<?php echo e(url('admin/kelola-admin')); ?>/" + data.id;
        document.getElementById('adminMethodField').innerHTML = '<?php echo method_field('PUT'); ?>';
        document.getElementById('field_editing_id').value = data.id;

        document.getElementById('field_name').value = data.name;
        document.getElementById('field_email').value = data.email;
        document.getElementById('field_role').value = data.role;
        document.getElementById('field_status_aktif').checked = data.status_aktif;

        // Password opsional saat edit.
        document.getElementById('field_password').required = false;
        document.getElementById('field_password_confirmation').required = false;
        document.getElementById('passwordHint').textContent = '(kosongkan jika tidak diganti)';

        (data.admin_permissions || []).forEach(key => {
            const cb = document.querySelector(`.permission-checkbox[value="${key}"]`);
            if (cb) cb.checked = true;
        });

        togglePermissionBlock();
        adminModal.classList.remove('hidden');
    }

    function closeModal() {
        adminModal.classList.add('hidden');
    }

    <?php if($errors->any()): ?>
        // Kalau validasi gagal: buka lagi modalnya dalam mode yang sama (tambah/edit)
        // tanpa reset, karena field-field di atas sudah di-prefill lewat old().
        document.addEventListener('DOMContentLoaded', function () {
            togglePermissionBlock();
            <?php if(old('editing_id')): ?>
                document.getElementById('adminModalTitle').textContent = 'Edit Admin';
                adminForm.action = "<?php echo e(url('admin/kelola-admin')); ?>/<?php echo e(old('editing_id')); ?>";
                document.getElementById('adminMethodField').innerHTML = '<?php echo method_field('PUT'); ?>';
                document.getElementById('field_password').required = false;
                document.getElementById('field_password_confirmation').required = false;
                document.getElementById('passwordHint').textContent = '(kosongkan jika tidak diganti)';
            <?php else: ?>
                document.getElementById('adminModalTitle').textContent = 'Tambah Admin';
                adminForm.action = "<?php echo e(route('admin.kelola-admin.store')); ?>";
            <?php endif; ?>
            adminModal.classList.remove('hidden');
        });
    <?php endif; ?>
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\Project_kecil\alnay\resources\views/admin/kelola-admin/index.blade.php ENDPATH**/ ?>