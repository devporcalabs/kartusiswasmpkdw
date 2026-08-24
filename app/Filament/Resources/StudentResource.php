<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\File;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Manajemen Siswa';

    protected static ?string $modelLabel = 'Siswa';

    protected static ?string $pluralModelLabel = 'Data Siswa';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identitas Utama')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nis')
                                    ->label('Nomor Induk Siswa (NIS)')
                                    ->placeholder('Contoh: 262707018'),
                                Forms\Components\TextInput::make('nisn')
                                    ->label('NISN')
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('Contoh: 3134122158'),
                            ]),
                        Forms\Components\TextInput::make('nama_lengkap')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: AILA AZZHARIEN RAMADHANI')
                            ->afterStateHydrated(function ($state, $set) {
                                if ($state) $set('nama_lengkap', strtoupper($state));
                            }),
                    ]),

                Forms\Components\Section::make('Detail Profil')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('jenis_kelamin')
                                    ->label('Jenis Kelamin')
                                    ->options([
                                        'Laki-Laki' => 'Laki-Laki',
                                        'Perempuan' => 'Perempuan',
                                    ]),
                                Forms\Components\TextInput::make('agama')
                                    ->label('Agama')
                                    ->placeholder('Contoh: Islam')
                                    ->default('Islam'),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('tempat_tanggal_lahir')
                                    ->label('Tempat Tanggal Lahir')
                                    ->placeholder('Contoh: BEKASI, 26 JUNI 2013'),
                                Forms\Components\TextInput::make('kelas')
                                    ->label('Kelas')
                                    ->placeholder('Contoh: KELAS 7.01'),
                            ]),
                        Forms\Components\TextInput::make('nomor_ortu')
                            ->label('Nomor HP Orang Tua')
                            ->placeholder('Contoh: 628951234567')
                            ->regex('/^62[0-9]+$/')
                            ->helperText('Wajib diawali dengan kode negara 62')
                            ->validationAttribute('nomor HP orang tua'),
                        Forms\Components\Textarea::make('alamat_lengkap')
                            ->label('Alamat Lengkap')
                            ->rows(3)
                            ->placeholder('Nama kampung, RT/RW, kelurahan, kecamatan...'),
                    ]),

                Forms\Components\Section::make('Foto Siswa')
                    ->schema([
                        Forms\Components\FileUpload::make('photo_path')
                            ->label('Foto')
                            ->disk('public_dir')
                            ->image()
                            ->maxSize(2048)
                            ->helperText('Format: JPG, JPEG, PNG, WEBP. Maks 2MB. Sistem akan otomatis mengonversi ke WebP dan merapikan penempatannya.')
                            ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, $get) {
                                $nama = $get('nama_lengkap');
                                $nisn = $get('nisn') ?: 'TANPA_NISN';
                                $kelas = $get('kelas');

                                $resolveClassFolder = function ($kelas) {
                                    if (!$kelas || $kelas === 'Lainnya') return 'SUSULAN';
                                    if (preg_match('/(\d+)\.(\d+)/', $kelas, $m)) {
                                        return $m[1] . '.' . (int)$m[2];
                                    }
                                    if (preg_match('/(\d+)/', $kelas, $m2)) {
                                        return $m2[1];
                                    }
                                    return 'SUSULAN';
                                };

                                $kelasFolder = $resolveClassFolder($kelas);
                                $uploadDir = public_path('PAS_FOTO_SMPN_1_KEDUNGWARINGIN/' . $kelasFolder);

                                if (!File::isDirectory($uploadDir)) {
                                    File::makeDirectory($uploadDir, 0755, true);
                                }

                                $cleanNama = preg_replace('/[^A-Z0-9]/i', '_', strtoupper($nama));
                                $newFileName = $cleanNama . '_' . $nisn . '.webp';
                                $destPath = $uploadDir . '/' . $newFileName;
                                $relativeDestPath = 'PAS_FOTO_SMPN_1_KEDUNGWARINGIN/' . $kelasFolder . '/' . $newFileName;

                                $extension = strtolower($file->getClientOriginalExtension());
                                if ($extension === 'webp') {
                                    copy($file->getRealPath(), $destPath);
                                } else {
                                    $tempPath = $file->getRealPath();
                                    $info = getimagesize($tempPath);
                                    if ($info !== false) {
                                        $mime = $info['mime'];
                                        switch ($mime) {
                                            case 'image/jpeg':
                                                $image = imagecreatefromjpeg($tempPath);
                                                break;
                                            case 'image/png':
                                                $image = imagecreatefrompng($tempPath);
                                                imagepalettetotruecolor($image);
                                                imagealphablending($image, true);
                                                imagesavealpha($image, true);
                                                break;
                                            default:
                                                $image = null;
                                        }
                                        if ($image && imagewebp($image, $destPath, 85)) {
                                            imagedestroy($image);
                                        } else {
                                            if ($image) imagedestroy($image);
                                            throw new \Exception('Gagal mengonversi gambar ke WebP.');
                                        }
                                    }
                                }

                                return $relativeDestPath;
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_path')
                    ->label('Foto')
                    ->disk('public_dir')
                    ->square(),
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => strtoupper($state)),
                Tables\Columns\TextColumn::make('nisn')
                    ->label('NISN')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kelas')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_kelengkapan')
                    ->label('Status')
                    ->badge()
                    ->state(function (Student $record) {
                        $comp = $record->isComplete();
                        $photo = $record->hasPhoto();
                        if ($comp && $photo) return 'Lengkap';
                        if ($comp && !$photo) return 'Foto (-)';
                        if (!$comp && $photo) return 'Data (-)';
                        return 'Tdk Lengkap';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Lengkap' => 'success',
                        'Foto (-)' => 'warning',
                        'Data (-)' => 'warning',
                        'Tdk Lengkap' => 'danger',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kelas')
                    ->label('Filter Kelas')
                    ->options(function () {
                        return Student::select('kelas')
                            ->whereNotNull('kelas')
                            ->where('kelas', '!=', '')
                            ->distinct()
                            ->orderBy('kelas', 'asc')
                            ->pluck('kelas', 'kelas')
                            ->toArray();
                    }),
                Tables\Filters\Filter::make('status')
                    ->label('Filter Kelengkapan')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Status Kelengkapan')
                            ->options([
                                'complete' => 'Lengkap (Siap Cetak)',
                                'no_photo_data_complete' => '⚠ Foto (-) , Data (+)',
                                'has_photo_data_incomplete' => '⚠ Foto (+) , Data (-)',
                                'no_photo_data_incomplete' => '⚠ Foto (-) , Data (-)',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $status = $data['status'] ?? null;
                        if (empty($status)) return $query;

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

                        return match ($status) {
                            'complete' => $query->where($condHasPhoto)->where($condDataComplete),
                            'no_photo_data_complete' => $query->where($condNoPhoto)->where($condDataComplete),
                            'has_photo_data_incomplete' => $query->where($condHasPhoto)->where($condDataIncomplete),
                            'no_photo_data_incomplete' => $query->where($condNoPhoto)->where($condDataIncomplete),
                            default => $query,
                        };
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
