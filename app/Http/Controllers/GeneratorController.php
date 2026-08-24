<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class GeneratorController extends Controller
{
    public function index()
    {
        $classes = Student::select('kelas')
            ->whereNotNull('kelas')
            ->where('kelas', '!=', '')
            ->distinct()
            ->orderBy('kelas', 'asc')
            ->pluck('kelas');

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

        return view('generator.index', compact('classes', 'c1', 'c2', 'c3', 'totalIncomplete'));
    }
}
