<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StudentOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $totalStudents = Student::count();

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

        $c1 = Student::where($condNoPhoto)->where($condDataComplete)->count();
        $c2 = Student::where($condHasPhoto)->where($condDataIncomplete)->count();
        $c3 = Student::where($condNoPhoto)->where($condDataIncomplete)->count();
        $totalIncomplete = $c1 + $c2 + $c3;
        $totalComplete = $totalStudents - $totalIncomplete;

        $completenessRate = $totalStudents > 0 ? round(($totalComplete / $totalStudents) * 100) : 0;

        return [
            Stat::make('Total Siswa', $totalStudents)
                ->description('Jumlah siswa terdaftar di database')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            Stat::make('Data & Foto Lengkap', $totalComplete)
                ->description("Rasio kesiapan cetak: {$completenessRate}%")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Belum Lengkap', $totalIncomplete)
                ->description("Foto (-): " . ($c1 + $c3) . " | Data (-): " . ($c2 + $c3))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($totalIncomplete > 0 ? 'danger' : 'success'),
        ];
    }
}
