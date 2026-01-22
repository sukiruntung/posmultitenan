<?php

namespace App\Filament\Resources\Mitra;

use App\Filament\Resources\Mitra\MarketingResource\Pages;
use App\Filament\Resources\Mitra\MarketingResource\RelationManagers;
use App\Models\Mitra\Marketing;
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

class MarketingResource extends Resource
{
    use CheckPermissionAccess;
    protected static int $menuId = 14;
    protected static ?string $model = Marketing::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 9;
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return static::checkMasterDataAccess('can_view', static::$menuId);
    }

    public static function canCreate(): bool
    {
        return static::checkMasterDataAccess('can_create', static::$menuId);
    }

    public static function canEdit(Model $record): bool
    {
        return static::checkMasterDataAccess('can_edit', static::$menuId);
    }
    public static function canDelete(Model $record): bool
    {
        return static::checkMasterDataAccess('can_delete', static::$menuId);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('marketing_name')
                    ->label('Nama Staff / Marketing')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('marketing_team_id')
                    ->label('Team Marketing')
                    ->relationship('marketingTeam', 'marketing_team_name'),
                Forms\Components\Textarea::make('marketing_address')
                    ->label('Alamat')
                    ->autosize(),
                Forms\Components\TextInput::make('marketing_email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),

                Forms\Components\TextInput::make('marketing_phone1')
                    ->tel()
                    ->maxLength(15)
                    ->rule('regex:/^(?:\+62|62|0)[0-9]{9,14}$/')
                    ->placeholder('cth: 08123456789'),
                Forms\Components\TextInput::make('marketing_phone2')
                    ->maxLength(15)
                    ->tel()
                    ->rule('regex:/^(?:\+62|62|0)[0-9]{9,14}$/')
                    ->placeholder('cth: 08123456789'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->modifyQueryUsing(fn(Builder $query) => $query->where('outlet_id', Auth::user()->userOutlet->outlet_id))
            ->columns([
                Tables\Columns\TextColumn::make('marketing_name')
                    ->label('Nama Marketing / Staff')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('marketingTeam.marketing_team_name')
                    ->label('Tim Marketing')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('marketing_address')
                    ->label('Alamat')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('marketing_email')
                    ->label('Email')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('phones')
                    ->label('Telp/HP')
                    ->getStateUsing(fn($record) => collect([
                        $record->marketing_phone1,
                        $record->marketing_phone2,
                    ])->filter()->implode(' / ')),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->closeModalByClickingAway(false)
                    ->mutateFormDataUsing(function (array $data) {
                        $data['user_id'] = Auth::id();
                        $data['outlet_id'] = Auth::user()->userOutlet->outlet_id;
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->closeModalByClickingAway(false)
                    ->action(function (Marketing $record) {
                        $record->user_id = Auth::id();
                        $record->save();
                        $record->delete();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListMarketings::route('/'),

        ];
    }
}
