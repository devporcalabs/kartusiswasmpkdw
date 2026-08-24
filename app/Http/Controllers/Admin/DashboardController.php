<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
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

        // Hitung statistik per kelas
        $classes = Student::select('kelas')
            ->whereNotNull('kelas')
            ->where('kelas', '!=', '')
            ->distinct()
            ->orderBy('kelas', 'asc')
            ->pluck('kelas');

        $classStats = [];
        foreach ($classes as $cls) {
            $totalInClass = Student::where('kelas', $cls)->count();
            // LENGKAP = has photo AND has complete data
            $completeInClass = Student::where('kelas', $cls)
                ->where($condHasPhoto)
                ->where($condDataComplete)
                ->count();
            $percentage = $totalInClass > 0 ? round(($completeInClass / $totalInClass) * 100) : 0;

            $classStats[] = [
                'name' => $cls,
                'total' => $totalInClass,
                'complete' => $completeInClass,
                'incomplete' => $totalInClass - $completeInClass,
                'percentage' => $percentage
            ];
        }

        return view('admin.dashboard', compact(
            'totalStudents', 'c1', 'c2', 'c3', 'totalIncomplete', 'totalComplete', 'classStats'
        ));
    }
}
