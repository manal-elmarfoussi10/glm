<?php

namespace App\Filament\Resources\ActivityLogs\Schemas;

use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\KeyValue;

class ActivityLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Détails de l\'activité')
                    ->schema([
                        TextEntry::make('created_at')->label('Date/Heure')->dateTime(),
                        TextEntry::make('user.name')->label('Utilisateur'),
                        TextEntry::make('action')->label('Action')->badge(),
                        TextEntry::make('description')->label('Description')->columnSpanFull(),
                        KeyValue::make('properties')->label('Données additionnelles')->columnSpanFull(),
                    ])->columns(3),
            ]);
    }
}
