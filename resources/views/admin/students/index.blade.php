<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manajemen Data Siswa') }}
            </h2>
            <a href="{{ route('admin.students.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                + Tambah Siswa
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-md text-green-700 text-sm font-semibold shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Search and Filter Panel -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <form action="{{ route('admin.students.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search Input -->
                    <div>
                        <label for="search" class="block text-xs font-semibold text-gray-500 uppercase mb-1">Cari Nama/NISN/NIS</label>
                        <input type="text" name="search" id="search" value="{{ $search }}" placeholder="Ketik nama atau nomor..." class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    </div>

                    <!-- Class Filter -->
                    <div>
                        <label for="kelas" class="block text-xs font-semibold text-gray-500 uppercase mb-1">Kelas</label>
                        <select name="kelas" id="kelas" class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <option value="">Semua Kelas</option>
                            @foreach($classes as $cls)
                                <option value="{{ $cls }}" {{ $kelas === $cls ? 'selected' : '' }}>{{ $cls }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Completeness Status Filter -->
                    <div>
                        <label for="status" class="block text-xs font-semibold text-gray-500 uppercase mb-1">Kelengkapan Data</label>
                        <select name="status" id="status" class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <option value="">Semua Status</option>
                            <option value="complete" {{ $status === 'complete' ? 'selected' : '' }}>Lengkap (Siap Cetak)</option>
                            <option value="no_photo_data_complete" {{ $status === 'no_photo_data_complete' ? 'selected' : '' }}>⚠ Foto (-) , Data (+)</option>
                            <option value="has_photo_data_incomplete" {{ $status === 'has_photo_data_incomplete' ? 'selected' : '' }}>⚠ Foto (+) , Data (-)</option>
                            <option value="no_photo_data_incomplete" {{ $status === 'no_photo_data_incomplete' ? 'selected' : '' }}>⚠ Foto (-) , Data (-)</option>
                        </select>
                    </div>

                    <!-- Filter Actions -->
                    <div class="flex items-end gap-2">
                        <button type="submit" class="w-full py-2 bg-gray-800 text-white text-sm font-semibold rounded-md hover:bg-gray-700 transition">
                            Filter
                        </button>
                        <a href="{{ route('admin.students.index') }}" class="w-full py-2 bg-gray-200 text-gray-700 text-center text-sm font-semibold rounded-md hover:bg-gray-300 transition">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Student List Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto mb-4">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIS / NISN</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($students as $s)
                                    <tr>
                                        <!-- Photo cell -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="w-12 h-16 bg-gray-100 border border-gray-200 rounded-md overflow-hidden flex items-center justify-center">
                                                @if($s->hasPhoto())
                                                    <img src="/{{ $s->photo_path }}" alt="Foto {{ $s->nama_lengkap }}" class="w-full h-full object-cover">
                                                @else
                                                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                                                @endif
                                            </div>
                                        </td>
                                        <!-- Name cell -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-gray-900">{{ strtoupper($s->nama_lengkap) }}</div>
                                            <div class="text-xs text-gray-500">{{ $s->jenis_kelamin }} · {{ $s->agama ?: 'Islam' }}</div>
                                        </td>
                                        <!-- NIS/NISN cell -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <div>NIS: <span class="font-mono">{{ $s->nis ?: '–' }}</span></div>
                                            <div>NISN: <span class="font-mono font-semibold">{{ $s->nisn ?: '–' }}</span></div>
                                        </td>
                                        <!-- Class cell -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $s->kelas }}
                                        </td>
                                        <!-- Status cell -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $comp = $s->isComplete();
                                                $photo = $s->hasPhoto();
                                            @endphp
                                            @if($comp && $photo)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Lengkap
                                                </span>
                                            @elseif(!$photo && $comp)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-amber-100 text-amber-800" title="Foto tidak ditemukan">
                                                    Foto (-)
                                                </span>
                                            @elseif($photo && !$comp)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800" title="NISN/TTL/Alamat ada yang kosong">
                                                    Data (-)
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800" title="Foto & Data tidak lengkap">
                                                    Tdk Lengkap
                                                </span>
                                            @endif
                                        </td>
                                        <!-- Actions cell -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex gap-2">
                                                <a href="{{ route('admin.students.edit', $s->id) }}" class="text-blue-600 hover:text-blue-900 font-bold mr-2">Edit</a>
                                                <form action="{{ route('admin.students.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus siswa ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 font-bold">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-500 font-medium">
                                            Tidak ada data siswa ditemukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $students->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
