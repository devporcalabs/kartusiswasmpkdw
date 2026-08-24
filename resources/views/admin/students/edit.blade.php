<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Ubah Data Siswa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.students.update', $student->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- NIS -->
                            <div>
                                <label for="nis" class="block text-sm font-semibold text-gray-700 mb-1">Nomor Induk Siswa (NIS)</label>
                                <input type="text" name="nis" id="nis" value="{{ old('nis', $student->nis) }}" class="w-full text-sm border-gray-300 rounded-md shadow-sm @error('nis') border-red-500 @enderror">
                                @error('nis') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- NISN -->
                            <div>
                                <label for="nisn" class="block text-sm font-semibold text-gray-700 mb-1">NISN</label>
                                <input type="text" name="nisn" id="nisn" value="{{ old('nisn', $student->nisn) }}" class="w-full text-sm border-gray-300 rounded-md shadow-sm @error('nisn') border-red-500 @enderror">
                                @error('nisn') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Nama Lengkap -->
                        <div class="mb-6">
                            <label for="nama_lengkap" class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_lengkap" id="nama_lengkap" value="{{ old('nama_lengkap', $student->nama_lengkap) }}" required class="w-full text-sm border-gray-300 rounded-md shadow-sm @error('nama_lengkap') border-red-500 @enderror">
                            @error('nama_lengkap') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Jenis Kelamin -->
                            <div>
                                <label for="jenis_kelamin" class="block text-sm font-semibold text-gray-700 mb-1">Jenis Kelamin</label>
                                <select name="jenis_kelamin" id="jenis_kelamin" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">Pilih...</option>
                                    <option value="Laki-Laki" {{ old('jenis_kelamin', $student->jenis_kelamin) === 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki</option>
                                    <option value="Perempuan" {{ old('jenis_kelamin', $student->jenis_kelamin) === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>

                            <!-- Agama -->
                            <div>
                                <label for="agama" class="block text-sm font-semibold text-gray-700 mb-1">Agama</label>
                                <input type="text" name="agama" id="agama" value="{{ old('agama', $student->agama) }}" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Tempat Tanggal Lahir -->
                            <div>
                                <label for="tempat_tanggal_lahir" class="block text-sm font-semibold text-gray-700 mb-1">Tempat Tanggal Lahir</label>
                                <input type="text" name="tempat_tanggal_lahir" id="tempat_tanggal_lahir" value="{{ old('tempat_tanggal_lahir', $student->tempat_tanggal_lahir) }}" placeholder="Contoh: BEKASI, 26 JUNI 2013" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                            </div>

                            <!-- Kelas -->
                            <div>
                                <label for="kelas" class="block text-sm font-semibold text-gray-700 mb-1">Kelas</label>
                                <input type="text" name="kelas" id="kelas" value="{{ old('kelas', $student->kelas) }}" placeholder="Contoh: KELAS 7.01" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                            </div>
                        </div>

                        <!-- No Ortu -->
                        <div class="mb-6">
                            <label for="nomor_ortu" class="block text-sm font-semibold text-gray-700 mb-1">Nomor HP Orang Tua (wajib diawali 62)</label>
                            <input type="text" name="nomor_ortu" id="nomor_ortu" value="{{ old('nomor_ortu', $student->nomor_ortu) }}" placeholder="Contoh: 628951234567" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                        </div>

                        <!-- Alamat Lengkap -->
                        <div class="mb-6">
                            <label for="alamat_lengkap" class="block text-sm font-semibold text-gray-700 mb-1">Alamat Lengkap</label>
                            <textarea name="alamat_lengkap" id="alamat_lengkap" rows="3" class="w-full text-sm border-gray-300 rounded-md shadow-sm">{{ old('alamat_lengkap', $student->alamat_lengkap) }}</textarea>
                        </div>

                        <!-- Current Photo Preview -->
                        <div class="mb-6 flex gap-4 items-center">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Foto Saat Ini</label>
                                <div class="w-24 h-32 bg-gray-100 border border-gray-200 rounded-md overflow-hidden flex items-center justify-center">
                                    @if($student->hasPhoto())
                                        <img src="/{{ $student->photo_path }}" alt="Foto {{ $student->nama_lengkap }}" class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Ganti Foto Siswa</label>
                                <input type="file" name="photo" id="photo" accept="image/*" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                <p class="text-xs text-gray-500 mt-1">Format: JPG, JPEG, PNG, WEBP. Maks 2MB. Sistem akan otomatis mengubah berkas menjadi WebP.</p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 border-t pt-4">
                            <a href="{{ route('admin.students.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-semibold rounded-md hover:bg-gray-50 transition">
                                Batal
                            </a>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700 transition">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
