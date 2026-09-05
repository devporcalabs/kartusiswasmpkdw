<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('export_csv')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->action(function () {
                    return response()->streamDownload(function () {
                        $handle = fopen('php://output', 'w');
                        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                        fputcsv($handle, ['Nama', 'NISN', 'Jenis Kelamin', 'Tanggal Lahir', 'Agama', 'Nama Ayah', 'Nama Ibu', 'No HP', 'Kelas', 'Alamat']);
                        
                        \App\Models\Student::orderBy('kelas')->orderBy('nama_lengkap')->chunk(200, function ($students) use ($handle) {
                            foreach ($students as $s) {
                                fputcsv($handle, [
                                    $s->nama_lengkap ?? '',
                                    $s->nisn ? "'" . $s->nisn : '',
                                    $s->jenis_kelamin ?? '',
                                    $s->tempat_tanggal_lahir ?? '',
                                    $s->agama ?? '',
                                    '', // Nama Ayah
                                    '', // Nama Ibu
                                    $s->nomor_ortu ? "'" . $s->nomor_ortu : '',
                                    $s->kelas ?? '',
                                    $s->alamat_lengkap ?? ''
                                ]);
                            }
                        });
                        fclose($handle);
                    }, 'data_siswa_smpn1kdw_' . date('Ymd_His') . '.csv', [
                        'Content-Type' => 'text/csv; charset=UTF-8',
                    ]);
                }),
            Actions\Action::make('cetak_kartu')
                ->label('Cetak Kartu')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\Select::make('kelas')
                        ->label('Pilih Kelas')
                        ->options(
                            \App\Models\Student::select('kelas')
                                ->whereNotNull('kelas')
                                ->where('kelas', '!=', '')
                                ->distinct()
                                ->orderBy('kelas', 'asc')
                                ->pluck('kelas', 'kelas')
                                ->toArray()
                        )
                        ->required(),
                ])
                ->action(function (array $data) {
                    $kelas = $data['kelas'];
                    return redirect()->to(url('/?kelas=' . urlencode($kelas)));
                }),
        ];
    }

    public function getMaxContentWidth(): string
    {
        return 'full';
    }
}
