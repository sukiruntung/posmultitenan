<?php

namespace App\Filament\Resources\Kasir;

use App\Filament\Resources\Kasir\BukaKasirResource\Pages;
use App\Filament\Resources\Kasir\BukaKasirResource\RelationManagers;
use App\Models\Accounting\KasHarian;
use App\Traits\CheckPermissionAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class BukaKasirResource extends Resource
{
    use CheckPermissionAccess;
    protected static ?string $model = KasHarian::class;

    protected static int $menuId = 10;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Admin';
    protected static ?string $navigationLabel = 'Kasir';
    protected static ?string $label = 'Buka / Tutup Kasir';
    protected static ?int $navigationSort = 19;

    public static function shouldRegisterNavigation(): bool
    {
        return static::checkMenuAccess('can_view', static::$menuId);
    }

    public static function canAccess(): bool
    {
        return static::checkMenuAccess('can_view', static::$menuId);
    }

    public static function canCreate(): bool
    {
        return static::checkMenuAccess('can_create', static::$menuId);
    }

    public static function canEdit(Model $record): bool
    {
        return static::checkMenuAccess('can_edit', static::$menuId);
    }
    public static function canDelete(Model $record): bool
    {
        return static::checkMenuAccess('can_delete', static::$menuId);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('outlet_id')
                    ->default(Auth::user()->userOutlet->outlet_id),
                Forms\Components\DatePicker::make('kas_harian_tanggalbuka')
                    ->label('Tanggal Buka')
                    ->required()
                    ->default(now()),
                Forms\Components\TextInput::make('kas_harian_saldoawal')
                    ->label('Saldo Awal')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\Hidden::make('kas_harian_status')
                    ->default('buka'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->header(function () {
                return view('filament.tables.date-range-filter');
            })
            ->recordAction(null)
            ->modifyQueryUsing(fn(Builder $query) => $query->where('outlet_id', Auth::user()->userOutlet->outlet_id))
            ->columns([
                Tables\Columns\TextColumn::make('kas_harian_tanggalbuka')
                    ->label('Tanggal Buka')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kas_harian_saldoawal')
                    ->label('Saldo Awal')
                    ->money('IDR'),
                Tables\Columns\BadgeColumn::make('kas_harian_status')
                    ->label('Status')
                    ->colors([
                        'success' => 'buka',
                        'danger' => 'tutup',
                    ]),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => $record->kas_harian_status === 'buka' && $record->kasir_id === Auth::id() && Auth::user()->is_kasir == true),
                Tables\Actions\Action::make('tutup_kasir')
                    ->label('Tutup Kasir')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->url(fn($record) => static::getUrl('tutup', ['record' => $record]))
                    ->visible(fn($record) => $record->kas_harian_status === 'buka' && $record->kasir_id === Auth::id() && Auth::user()->is_kasir == true),
                Tables\Actions\Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->url(fn($record) => static::getUrl('detail', ['record' => $record]))
                    ->visible(fn($record) => $record->kas_harian_status === 'tutup'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBukaKasirs::route('/'),
            // 'create' => Pages\CreateBukaKasir::route('/create'),
            // 'edit' => Pages\EditBukaKasir::route('/{record}/edit'),
            'tutup' => Pages\TutupBukaKasir::route('/{record}/tutup'),
            'detail' => Pages\DetailBukaKasir::route('/{record}/detail'),
        ];
    }
}
