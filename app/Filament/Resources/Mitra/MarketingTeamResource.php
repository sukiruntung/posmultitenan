<?php

namespace App\Filament\Resources\Mitra;

use App\Filament\Resources\Mitra\MarketingTeamResource\Pages;
use App\Filament\Resources\Mitra\MarketingTeamResource\RelationManagers;
use App\Models\Mitra\Marketing;
use App\Models\Mitra\MarketingTeam;
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

class MarketingTeamResource extends Resource
{
    use CheckPermissionAccess;
    protected static int $menuId = 15;
    protected static ?string $model = MarketingTeam::class;

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
                Forms\Components\TextInput::make('marketing_team_name')
                    ->label('Nama Kepala/Tim Marketing')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('marketing_id')
                    ->options(Marketing::where('outlet_id', Auth::user()->userOutlet->outlet_id)->pluck('marketing_name', 'id'))
                    ->label('Marketing')
                    ->searchable(),
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('marketing_team_name')
                    ->label('Nama Kepala/Tim Marketing')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('marketing.marketing_name')
                    ->label('Marketing')
                    ->sortable()
                    ->searchable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->closeModalByClickingAway(false)
                    ->mutateFormDataUsing(function (array $data) {
                        $data['user_id'] = Auth::id();
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->closeModalByClickingAway(false)
                    ->action(function (MarketingTeam $record) {
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
            'index' => Pages\ListMarketingTeams::route('/'),
        ];
    }
}
