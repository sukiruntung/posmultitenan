<?php

namespace App\Filament\Resources\Mitra;

use App\Filament\Resources\Mitra\CustomerResource\Pages;
use App\Filament\Resources\Mitra\CustomerResource\RelationManagers;
use App\Models\Mitra\Customer;
use App\Models\Mitra\CustomerMarketing;
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
use Illuminate\Support\Facades\DB;

class CustomerResource extends Resource
{
    use CheckPermissionAccess;
    protected static int $menuId = 12;
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 8;
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
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')
                            ->label('Nama Customer')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('customer_alamat')
                            ->autosize()
                            ->label('Alamat'),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->label('Email')
                            ->maxLength(70),
                        Forms\Components\TextInput::make('customer_phone1')
                            ->maxLength(15)
                            ->rule('regex:/^(?:\+62|62|0)[0-9]{9,13}$/')
                            ->placeholder('cth: 08123456789'),
                        Forms\Components\TextInput::make('customer_phone2')
                            ->maxLength(15)
                            ->rule('regex:/^(?:\+62|62|0)[0-9]{9,13}$/')
                            ->placeholder('cth: 08123456789'),
                        Forms\Components\Select::make('customer_harga')
                            ->label('Tipe Harga')
                            ->options([
                                'hargagrosir' => 'Harga Grosir',
                                'harga1' => 'Harga 1',
                                'harga2' => 'Harga 2',
                                'harga3' => 'Harga 3',
                            ])
                            ->default('harga1'),
                    ]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('customer_picname1')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('customer_picphone1')
                            ->maxLength(15)
                            ->rule('regex:/^(?:\+62|62|0)[0-9]{9,13}$/')
                            ->placeholder('cth: 08123456789'),
                        Forms\Components\TextInput::make('customer_picname2')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('customer_picphone2')
                            ->maxLength(15)
                            ->rule('regex:/^(?:\+62|62|0)[0-9]{9,13}$/')
                            ->placeholder('cth: 08123456789'),
                        Forms\Components\Select::make('marketing_id')
                            ->label('Pilih Marketing')
                            ->options(Marketing::where('outlet_id', Auth::user()->userOutlet->outlet_id)->pluck('marketing_name', 'id'))
                            ->searchable()
                            // isi otomatis saat edit
                            ->afterStateHydrated(function ($set, $state, $record) {
                                if ($record && $record->customerMarketing) {
                                    $set('marketing_id', $record->customerMarketing->marketing_id);
                                }
                            }),
                        //
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->modifyQueryUsing(fn(Builder $query) => $query->where('outlet_id', Auth::user()->userOutlet->outlet_id))
            ->columns([
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Nama Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_alamat')
                    ->label('Alamat')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_email')
                    ->label('Email'),
                Tables\Columns\TextColumn::make('phones')
                    ->label('Telp/HP')
                    ->getStateUsing(fn($record) => collect([
                        $record->customer_phone1,
                        $record->customer_phone2,
                    ])->filter()->implode(' / ')),
                Tables\Columns\TextColumn::make('pic')
                    ->label('PIC')
                    ->html()
                    ->getStateUsing(fn($record) => collect([
                        $record->customer_picname1
                            ? $record->customer_picname1 .
                            (!empty($record->customer_picphone1)
                                ? ' (' . $record->customer_picphone1 . ')'
                                : '')
                            : null,
                        $record->customer_picname2
                            ? $record->customer_picname2 .
                            (!empty($record->customer_picphone2)
                                ? ' (' . $record->customer_picphone2 . ')'
                                : '')
                            : null,
                    ])->filter()->implode('</br>'))
                    ->wrap(),
                // Tables\Columns\TextColumn::make('pic 1')
                //     ->label('PIC 1')
                //     ->getStateUsing(fn($record) => collect([
                //         $record->customer_picname1,
                //         (empty($record->customer_picphone1)) ? "" : '(' . $record->customer_picphone1 . ')',
                //     ])->filter()->implode(' ')),
                // Tables\Columns\TextColumn::make('pic 2')
                //     ->label('PIC 2')
                //     ->getStateUsing(fn($record) => collect([
                //         $record->customer_picname2,
                //         (empty($record->customer_picphone2)) ? "" : '(' . $record->customer_picphone2 . ')',
                //     ])->filter()->implode(' ')),
                Tables\Columns\TextColumn::make('customerMarketing.marketing.marketing_name')
                    ->label('Marketing')
                    ->searchable()
                    ->sortable(),


            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->closeModalByClickingAway(false)
                    ->mutateFormDataUsing(function (array $data): array {
                        // inject user_id ke data form
                        $data['user_id'] = Auth::id();
                        $data['outlet_id'] = Auth::user()->userOutlet->outlet_id;
                        if (empty($data['marketing_id'])) {
                            unset($data['marketing_id']);
                        }

                        return $data;
                    })
                    ->after(function ($record, array $data) {
                        if (!empty($data['marketing_id'])) {
                            CustomerMarketing::updateOrCreate(
                                [
                                    'customer_id'  => $record->id,
                                ],
                                [
                                    'marketing_id' => $data['marketing_id'],
                                    'user_id'      => Auth::id(),
                                ]
                            );
                        }
                    }),
                Tables\Actions\DeleteAction::make()
                    ->closeModalByClickingAway(false)
                    ->action(function (Customer $record) {
                        $record->user_id = Auth::id();
                        $record->save();
                        $record->delete();
                        if ($record->customerMarketing) {
                            $record->customerMarketing->update([
                                'user_id' => $record->user_id
                            ]);
                            $record->customerMarketing->delete();
                        }
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
            'index' => Pages\ListCustomer::route('/'),
        ];
    }
}
