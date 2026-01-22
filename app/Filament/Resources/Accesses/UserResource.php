<?php

namespace App\Filament\Resources\Accesses;

use App\Filament\Resources\Accesses\UserResource\Pages;
use App\Filament\Resources\Accesses\UserResource\RelationManagers;
use App\Models\Accesses\User;
use App\Traits\CheckPermissionAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\FormsComponent;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserResource extends Resource
{
    use CheckPermissionAccess;
    protected static int $menuId = 6;
    protected static ?string $model = user::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'User';
    protected static ?int $navigationSort = 2;

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
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(100)
                    ->label('Nama User'),
                Forms\Components\TextInput::make('email')
                    ->required()
                    ->email()
                    ->maxLength(100)
                    ->label('Email'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->minLength(8)
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn($state) => filled($state))
                    ->label('Password')
                    ->required(fn(Get $get) => is_null($get('id'))),
                Forms\Components\Select::make('user_group_id')
                    ->relationship('userGroup', 'user_groupname')
                    ->required()
                    ->label('Group User'),
                Forms\Components\Toggle::make('is_kasir')
                    ->label('is Kasir')
                    ->default(false),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('userOutlet', fn($q) => $q->where('outlet_id', Auth::user()->userOutlet->outlet_id)))
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('userGroup.user_groupname')
                    ->label('Group')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('userOutlet.outlet.outlet_name')
                    ->label('Outlet')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_kasir')
                    ->toggleable()
                    ->label('is Kasir')
                    ->boolean(),
                //
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
                    ->action(function (User $record) {
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
            'index' => Pages\ListUsers::route('/'),
        ];
    }
}
