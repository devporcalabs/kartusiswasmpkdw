<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use Filament\Widgets\ChartWidget;

class ClassCompletenessChart extends ChartWidget
{
    protected static ?string $heading = 'Rasio Kelengkapan Data Siswa Per Kelas';

    protected static ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        // Hubungkan kondisi kelengkapan data
        $condDataComplete = function ($q) {
            $q->whereNotNull('tempat_tanggal_lahir')->where('tempat_tanggal_lahir', '!=', '')
              ->whereNotNull('alamat_lengkap')->where('alamat_lengkap', '!=', '')
              ->whereNotNull('nisn')->where('nisn', '!=', '');
        };

        $condDataIncomplete = function ($q) {
            $q->whereNull('tempat_tanggal_lahir')->orWhere('tempat_tanggal_lahir', '')
              ->orWhereNull('alamat_lengkap')->orWhere('alamat_lengkap', '')
              ->orWhereNull('nisn')->orWhere('nisn', '');
        };

        $condHasPhoto = function ($q) {
            $q->whereNotNull('photo_path')->where('photo_path', '!=', '');
        };

        $condNoPhoto = function ($q) {
            $q->whereNull('photo_path')->orWhere('photo_path', '');
        };

        // Ambil kelas unik
        $classes = Student::select('kelas')
            ->whereNotNull('kelas')
            ->where('kelas', '!=', '')
            ->distinct()
            ->orderBy('kelas', 'asc')
            ->pluck('kelas')
            ->toArray();

        $completeCounts = [];
        $incompleteCounts = [];

        foreach ($classes as $cls) {
            // LENGKAP = has photo AND has complete data
            $complete = Student::where('kelas', $cls)
                ->where($condHasPhoto)
                ->where($condDataComplete)
                ->count();

            // BELUM LENGKAP
            $c1 = Student::where('kelas', $cls)->where($condNoPhoto)->where($condDataComplete)->count();
            $c2 = Student::where('kelas', $cls)->where($condHasPhoto)->where($condDataIncomplete)->count();
            $c3 = Student::where('kelas', $cls)->where($condNoPhoto)->where($condDataIncomplete)->count();
            $incomplete = $c1 + $c2 + $c3;

            $completeCounts[] = $complete;
            $incompleteCounts[] = $incomplete;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Lengkap (Siap Cetak)',
                    'data' => $completeCounts,
                    'backgroundColor' => '#22c55e', // Green-500
                    'borderColor' => '#16a34a',
                ],
                [
                    'label' => 'Belum Lengkap',
                    'data' => $incompleteCounts,
                    'backgroundColor' => '#f97316', // Orange-500
                    'borderColor' => '#ea580c',
                ],
            ],
            'labels' => $classes,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
