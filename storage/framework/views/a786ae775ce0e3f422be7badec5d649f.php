<?php $__env->startSection('title', 'Form Booking Laboratorium - Mahasiswa'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">📅 Form Booking Laboratorium</h1>
        <p class="text-gray-600">Ajukan permohonan penggunaan laboratorium untuk kegiatan akademik</p>
    </div>

    <!-- INFO: Alur Persetujuan untuk Mahasiswa -->
    <div class="mb-6 p-4 bg-blue-50 border border-blue-300 rounded-lg">
        <h4 class="font-semibold text-blue-800 mb-2">📋 Alur Persetujuan Booking Mahasiswa</h4>
        <div class="flex items-center gap-2 text-sm text-blue-700 flex-wrap">
            <span class="flex items-center gap-1">
                <span class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold">1</span>
                Dosen
            </span>
            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <span class="flex items-center gap-1">
                <span class="w-6 h-6 rounded-full bg-blue-200 text-blue-800 flex items-center justify-center text-xs font-bold">2</span>
                Teknisi
            </span>
            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <span class="flex items-center gap-1">
                <span class="w-6 h-6 rounded-full bg-blue-200 text-blue-800 flex items-center justify-center text-xs font-bold">3</span>
                Ka Lab
            </span>
            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <span class="flex items-center gap-1">
                <span class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold">✓</span>
                Dikonfirmasi
            </span>
        </div>
        <p class="mt-2 text-xs text-blue-600">
            ✅ Booking Anda akan melalui 3 tahap persetujuan (Dosen → Teknisi → Ka Lab) sebelum dikonfirmasi.
        </p>
    </div>

    <!-- Success/Error Messages -->
    <?php if(session('success')): ?>
    <div class="mb-6 p-4 bg-green-50 border border-green-300 rounded-lg text-green-800">
        <?php echo e(session('success')); ?>

    </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
    <div class="mb-6 p-4 bg-red-50 border border-red-300 rounded-lg">
        <ul class="list-disc list-inside text-red-700 text-sm">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
    <?php endif; ?>

    <form action="<?php echo e(route('booking.store')); ?>" method="POST" class="bg-white rounded-xl shadow-lg p-8">
        <?php echo csrf_field(); ?>

        <!-- SECTION 1: Data Mahasiswa Pemohon -->
        <div class="mb-8 pb-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">👤 Data Mahasiswa Pemohon</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                    <input type="text" value="<?php echo e($user->name); ?>" disabled
                           class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-600">
                </div>

                <!-- NIM -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">NIM</label>
                    <input type="text" value="<?php echo e($user->nim ?? '-'); ?>" disabled
                           class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-600">
                    <p class="mt-1 text-xs text-gray-500">Nomor Induk Mahasiswa</p>
                </div>

                <!-- Program Studi -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Program Studi</label>
                    <input type="text" name="prodi" value="<?php echo e($user->prodi ?? 'Teknik Informatika'); ?>" readonly
                           class="w-full px-4 py-3 bg-blue-50 border border-blue-300 rounded-lg text-blue-800 font-medium">
                    <p class="mt-1 text-xs text-gray-500">Otomatis dari data akun</p>
                </div>

                <!-- Golongan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Golongan <span class="text-red-500">*</span></label>
                    <select name="golongan" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['golongan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                        <option value="">-- Pilih Golongan --</option>
                        <option value="A" <?php echo e(old('golongan', $user->golongan ?? '') == 'A' ? 'selected' : ''); ?>>A</option>
                        <option value="B" <?php echo e(old('golongan', $user->golongan ?? '') == 'B' ? 'selected' : ''); ?>>B</option>
                        <option value="C" <?php echo e(old('golongan', $user->golongan ?? '') == 'C' ? 'selected' : ''); ?>>C</option>
                    </select>
                    <?php $__errorArgs = ['golongan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <p class="mt-1 text-xs text-gray-500">Golongan praktikum Anda</p>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Institusi</label>
                    <input type="email" value="<?php echo e($user->email); ?>" disabled
                           class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-600">
                </div>

                <!-- No. Telepon -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">No. Telepon / WhatsApp <span class="text-red-500">*</span></label>
                    <input type="tel" name="phone" required maxlength="20"
                           value="<?php echo e(old('phone', $user->phone ?? '')); ?>"
                           placeholder="Contoh: 081234567890"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>

        <!-- SECTION 2: Detail Booking -->
        <div class="mb-8 pb-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">🏢 Detail Booking Laboratorium</h3>

            <!-- Toggle: Individu / Kelompok -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_group" id="isGroupToggle" value="1"
                           class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500"
                           <?php echo e(old('is_group') ? 'checked' : ''); ?>>
                    <span class="font-medium text-gray-700">Booking Kelompok</span>
                </label>
                <p class="mt-2 text-sm text-gray-500 ml-8">
                    Centang jika booking untuk kelompok. Jika tidak, booking untuk individu.
                </p>
            </div>

            <!-- Anggota Kelompok -->
            <div id="groupSection" class="<?php echo e(old('is_group') ? '' : 'hidden'); ?> mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <h4 class="font-medium text-blue-800 mb-3">👥 Anggota Kelompok</h4>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cari Anggota (by NIM/Nama)</label>
                    <div class="relative">
                        <input type="text" id="memberSearch" placeholder="Ketik NIM atau nama..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <div id="searchResults" class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto hidden"></div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Minimal 2 karakter untuk mencari</p>
                </div>

                <div id="selectedMembers" class="space-y-2">
                    <?php if(old('members')): ?>
                        <?php $__currentLoopData = old('members'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $memberId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $member = $students->firstWhere('id', $memberId);
                            ?>
                            <?php if($member): ?>
                            <div class="flex items-center justify-between p-2 bg-white rounded border border-blue-200" id="member-<?php echo e($memberId); ?>">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center text-sm font-bold">
                                        <?php echo e(substr($member->name, 0, 1)); ?>

                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-800"><?php echo e($member->name); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo e($member->nim ?? 'N/A'); ?></div>
                                    </div>
                                </div>
                                <button type="button" onclick="removeMember(<?php echo e($member->id); ?>)" class="text-red-500 hover:text-red-700 p-1 hover:bg-red-50 rounded">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                <input type="hidden" name="members[]" value="<?php echo e($member->id); ?>">
                            </div>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Dosen Pembimbing -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Dosen Pembimbing (Opsional)</label>
                <select name="supervisor_id"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['supervisor_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <option value="">-- Pilih Dosen Pembimbing --</option>
                    <?php $__currentLoopData = $dosens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dosen): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($dosen->id); ?>" <?php echo e(old('supervisor_id') == $dosen->id ? 'selected' : ''); ?>>
                            <?php echo e($dosen->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['supervisor_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- Laboratorium -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Laboratorium <span class="text-red-500">*</span></label>
                <select name="lab_name" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['lab_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <option value="">-- Pilih Laboratorium --</option>
                    <?php $__currentLoopData = $labs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($lab); ?>" <?php echo e(old('lab_name', $prefilled['lab_name'] ?? '') == $lab ? 'selected' : ''); ?>>
                            <?php echo e($lab); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['lab_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                <?php if(!empty($prefilled['lab_name'])): ?>
                    <p class="mt-1 text-xs text-blue-600">✅ Diisi otomatis dari dashboard</p>
                <?php endif; ?>
            </div>

            <!-- Sesi & Tanggal -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Sesi -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sesi <span class="text-red-500">*</span></label>
                    <select name="session" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['session'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                        <option value="">-- Pilih Sesi --</option>
                        <?php
                            $sessionOptions = [
                                'Sesi 1 (07:00 - 08:00)',
                                'Sesi 2 (08:00 - 09:00)',
                                'Sesi 3 (09:00 - 10:00)',
                                'Sesi 4 (10:00 - 11:00)',
                                'Sesi 5 (13:00 - 14:00)',
                                'Sesi 6 (14:00 - 15:00)',
                                'Sesi 7 (15:00 - 16:00)',
                                'Sesi 8 (16:00 - 17:00)',
                            ];
                            $currentSession = old('session') ?? $prefilled['session'] ?? '';
                        ?>
                        <?php $__currentLoopData = $sessionOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sessionOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($sessionOption); ?>"
                                    <?php echo e($currentSession === $sessionOption ? 'selected' : ''); ?>>
                                <?php echo e($sessionOption); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['session'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Tanggal Booking -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="booking_date" required
                           value="<?php echo e(old('booking_date', $prefilled['booking_date'] ?? date('Y-m-d'))); ?>"
                           min="<?php echo e(date('Y-m-d')); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['booking_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <?php $__errorArgs = ['booking_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            <!-- Durasi & Tanggal Range -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Durasi -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Durasi (Hari) <span class="text-red-500">*</span></label>
                    <input type="number" name="duration_days" required min="1" max="30" value="<?php echo e(old('duration_days', 1)); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['duration_days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                </div>

                <!-- Start Date (Readonly) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                    <input type="date" id="startDate" readonly
                           class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-600">
                    <p class="mt-1 text-xs text-gray-500">Sama dengan tanggal booking</p>
                </div>

                <!-- End Date (Readonly) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai</label>
                    <input type="date" id="endDate" readonly
                           class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-600">
                    <p class="mt-1 text-xs text-gray-500">Otomatis dihitung dari durasi</p>
                </div>
            </div>
        </div>

        <!-- SECTION 3: Kegiatan & Keperluan -->
        <div class="mb-8 pb-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">📋 Kegiatan & Keperluan</h3>

            <!-- Activity -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kegiatan <span class="text-red-500">*</span></label>
                <select name="activity" id="activitySelect" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['activity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <option value="">-- Pilih Kegiatan --</option>
                    
                    <?php $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($activity); ?>" <?php echo e(old('activity') == $activity ? 'selected' : ''); ?>>
                            <?php echo e($activity); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['activity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- Activity Other -->
            <div id="activityOtherSection" class="mb-6 <?php echo e(old('activity') === 'Lainnya' ? '' : 'hidden'); ?>">
                <label class="block text-sm font-medium text-gray-700 mb-2">Sebutkan Kegiatan Lainnya <span class="text-red-500">*</span></label>
                <input type="text" name="activity_other" maxlength="255"
                       value="<?php echo e(old('activity_other')); ?>"
                       placeholder="Contoh: Workshop Machine Learning"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['activity_other'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                <?php $__errorArgs = ['activity_other'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- Purpose -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Keperluan / Deskripsi <span class="text-red-500">*</span></label>
                <textarea name="purpose" required rows="4" maxlength="1000"
                          placeholder="Jelaskan keperluan penggunaan laboratorium secara detail..."
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['purpose'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"><?php echo e(old('purpose')); ?></textarea>
                <?php $__errorArgs = ['purpose'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                <p class="mt-1 text-xs text-gray-500">Maksimal 1000 karakter</p>
            </div>
        </div>

        <!-- SECTION 4: Pernyataan Persetujuan -->
        <div class="mb-8 pb-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">📋 Pernyataan Persetujuan</h3>

            <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-6">
                <p class="text-sm text-gray-700 mb-4 font-medium">Dengan mengajukan permohonan ini, saya selaku mahasiswa menyatakan:</p>

                <div class="space-y-3 text-sm text-gray-700">
                    <div class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold">1</span>
                        <p><strong>BERTANGGUNG JAWAB DAN MEMATUHI ATURAN</strong> yang ditetapkan pihak kampus terkait dengan penggunaan ruangan.</p>
                    </div>

                    <div class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold">2</span>
                        <p><strong>BERSEDIA MENJAGA KETERATURAN, KEBERSIHAN, DAN INVENTARIS</strong> ruangan selama melaksanakan kegiatan di dalam ruangan.</p>
                    </div>

                    <div class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold">3</span>
                        <p><strong>BERSEDIA DIKENAKAN SANKSI</strong> apabila dalam pelaksanaannya dinilai dan terbukti melanggar poin 1 dan poin 2.</p>
                    </div>
                </div>

                <!-- Checkbox Persetujuan -->
                <div class="mt-6 p-4 bg-white border border-yellow-400 rounded-lg">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="agreement" id="agreement" required
                               class="w-5 h-5 mt-0.5 text-blue-600 rounded focus:ring-blue-500 border-gray-300">
                        <span class="text-sm text-gray-700">
                            <strong>Saya setuju dan menerima semua pernyataan di atas</strong> serta bersedia mematuhi seluruh peraturan yang berlaku.
                        </span>
                    </label>
                    <?php $__errorArgs = ['agreement'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
            <a href="<?php echo e(route('booking.index')); ?>"
               class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-400 transition-colors">
                Batal
            </a>
            <button type="submit" id="submitBtn" disabled
                    class="px-8 py-3 bg-gray-400 text-white rounded-lg font-medium transition-colors shadow-lg flex items-center gap-2 cursor-not-allowed">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Ajukan Booking
            </button>
        </div>
    </form>

</div>

<?php $__env->startPush('scripts'); ?>
<script>
// Toggle Group Section
document.getElementById('isGroupToggle')?.addEventListener('change', function() {
    document.getElementById('groupSection').classList.toggle('hidden', !this.checked);
});

// Activity "Lainnya" toggle
document.getElementById('activitySelect')?.addEventListener('change', function() {
    const otherSection = document.getElementById('activityOtherSection');
    if (this.value === 'Lainnya') {
        otherSection.classList.remove('hidden');
    } else {
        otherSection.classList.add('hidden');
        document.querySelector('[name="activity_other"]').value = '';
    }
});

// Auto-calculate date range
const bookingDateInput = document.querySelector('[name="booking_date"]');
const durationInput = document.querySelector('[name="duration_days"]');
const startDateInput = document.getElementById('startDate');
const endDateInput = document.getElementById('endDate');

function calculateDateRange() {
    const bookingDate = bookingDateInput?.value;
    const duration = parseInt(durationInput?.value) || 1;

    if (bookingDate && duration) {
        startDateInput.value = bookingDate;
        const start = new Date(bookingDate);
        start.setDate(start.getDate() + duration - 1);
        endDateInput.value = start.toISOString().split('T')[0];
    }
}

bookingDateInput?.addEventListener('change', calculateDateRange);
durationInput?.addEventListener('input', calculateDateRange);
calculateDateRange();

// Search Members
const memberSearchInput = document.getElementById('memberSearch');
const searchResults = document.getElementById('searchResults');
let searchTimeout;

memberSearchInput?.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value.trim();

    if (query.length < 2) {
        searchResults.classList.add('hidden');
        return;
    }

    searchTimeout = setTimeout(async () => {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const url = `<?php echo e(route('booking.search-users')); ?>?query=${encodeURIComponent(query)}&type=student`;

            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken })
                }
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const members = await response.json();

            if (members.length === 0) {
                searchResults.innerHTML = '<div class="p-3 text-sm text-gray-500">Tidak ditemukan</div>';
            } else {
                searchResults.innerHTML = members.map(member => `
                    <div class="p-3 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-0"
                         onclick="addMember(${member.id}, '${member.name.replace(/'/g, "\\'")}', '${member.nim || 'N/A'}')">
                        <div class="font-medium text-gray-800">${member.name}</div>
                        <div class="text-xs text-gray-500">${member.nim || 'N/A'}</div>
                    </div>
                `).join('');
            }
            searchResults.classList.remove('hidden');
        } catch (error) {
            searchResults.innerHTML = `<div class="p-3 text-sm text-red-500">Error: ${error.message}</div>`;
            searchResults.classList.remove('hidden');
        }
    }, 300);
});

function addMember(id, name, nim) {
    const selectedDiv = document.getElementById('selectedMembers');
    if (document.querySelector(`[name="members[]"][value="${id}"]`)) {
        alert('Anggota sudah ditambahkan');
        return;
    }

    const html = `
        <div class="flex items-center justify-between p-2 bg-white rounded border border-blue-200" id="member-${id}">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center text-sm font-bold">${name.charAt(0).toUpperCase()}</div>
                <div><div class="font-medium text-gray-800">${name}</div><div class="text-xs text-gray-500">${nim}</div></div>
            </div>
            <button type="button" onclick="removeMember(${id})" class="text-red-500 hover:text-red-700 p-1 hover:bg-red-50 rounded">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <input type="hidden" name="members[]" value="${id}">
        </div>
    `;

    selectedDiv.insertAdjacentHTML('beforeend', html);
    memberSearchInput.value = '';
    searchResults.classList.add('hidden');
}

function removeMember(id) {
    document.getElementById(`member-${id}`)?.remove();
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('#memberSearch') && !e.target.closest('#searchResults')) {
        searchResults?.classList.add('hidden');
    }
});

// Enable/disable submit button
const agreementCheckbox = document.getElementById('agreement');
const submitBtn = document.getElementById('submitBtn');

agreementCheckbox?.addEventListener('change', function() {
    if (this.checked) {
        submitBtn.disabled = false;
        submitBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
        submitBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
    } else {
        submitBtn.disabled = true;
        submitBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
        submitBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\project\laravel_project\sipinlab\resources\views/booking/create-mahasiswa.blade.php ENDPATH**/ ?>