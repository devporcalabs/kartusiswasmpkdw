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
