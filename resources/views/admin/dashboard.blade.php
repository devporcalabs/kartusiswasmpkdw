<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Admin') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Row -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <!-- Total Students -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-600">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 text-blue-600 rounded-full mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase">Total Siswa</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $totalStudents }}</p>
                        </div>
                    </div>
                </div>

                <!-- Complete Students -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-600">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 text-green-600 rounded-full mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase">Data & Foto Lengkap</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $totalComplete }}</p>
                        </div>
                    </div>
                </div>

                <!-- Incomplete Students -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-600">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 text-yellow-600 rounded-full mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase">Data Belum Lengkap</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $totalIncomplete }}</p>
                        </div>
                    </div>
                </div>

                <!-- Completeness Percentage -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-purple-600">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 text-purple-600 rounded-full mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.003 9.003 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase">Persentase Siap Cetak</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $totalStudents > 0 ? round(($totalComplete / $totalStudents) * 100) : 0 }}%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Incomplete Breakdown & Actions Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Breakdown Details -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 md:col-span-2">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Rincian Masalah Kelengkapan Data</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-amber-500 rounded-full mr-3"></span>
                                <span class="text-sm text-gray-600 font-medium">Foto Belum Ada, Data Lengkap</span>
                            </div>
                            <span class="px-3 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-full">{{ $c1 }} Siswa</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-orange-500 rounded-full mr-3"></span>
                                <span class="text-sm text-gray-600 font-medium">Foto Ada, Data Belum Lengkap</span>
                            </div>
                            <span class="px-3 py-1 bg-orange-100 text-orange-800 text-xs font-bold rounded-full">{{ $c2 }} Siswa</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-red-600 rounded-full mr-3"></span>
                                <span class="text-sm text-gray-600 font-medium">Foto Belum Ada, Data Belum Lengkap</span>
                            </div>
                            <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-bold rounded-full">{{ $c3 }} Siswa</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Navigasi Cepat</h3>
                    <div class="flex flex-col gap-3">
                        <a href="{{ route('admin.students.index') }}" class="flex items-center justify-between px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <span class="font-semibold text-sm">Kelola Data Siswa</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                        <a href="/" target="_blank" class="flex items-center justify-between px-4 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
                            <span class="font-semibold text-sm">Buka Halaman Cetak Kartu</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Class Completeness Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Statistik Kelengkapan Per Kelas</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kelas</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Siswa</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lengkap (Siap Cetak)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Belum Lengkap</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rasio Kesiapan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($classStats as $class)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $class['name'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $class['total'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-semibold">{{ $class['complete'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-amber-600 font-medium">{{ $class['incomplete'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="text-sm font-bold text-gray-900 mr-2">{{ $class['percentage'] }}%</span>
                                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $class['percentage'] }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
