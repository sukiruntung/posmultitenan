<?php

// namespace App\Filament\Resources;

// use Filament\Resources\Resource;
// use Illuminate\Database\Eloquent\Model;

// abstract class BaseResource extends Resource
// {
//     protected static function user()
//     {
//         return auth()->user();
//     }

//     protected static function getMasterId(): int
//     {
//         // Ambil dari session, default 0
//         return session('selected_master_id', 0);
//     }

//     public static function canAccess(): bool
//     {
//         return static::user()->hasAccess(
//             static::getMasterId(),
//             static::user()->user_group_id,
//             'can_view'
//         );
//     }

//     public static function canCreate(): bool
//     {
//         return static::user()->hasAccess(
//             static::getMasterId(),
//             static::user()->user_group_id,
//             'can_create'
//         );
//     }

//     public static function canEdit(Model $record): bool
//     {
//         return static::user()->hasAccess(
//             static::getMasterId(),
//             static::user()->user_group_id,
//             'can_edit'
//         );
//     }

//     public static function canDelete(Model $record): bool
//     {
//         return static::user()->hasAccess(
//             static::getMasterId(),
//             static::user()->user_group_id,
//             'can_delete'
//         );
//     }
// }
