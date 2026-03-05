<?php

namespace App\Filament\Resources\ActivityLogs\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;

class ActivityLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('action')->disabled(),
                DateTimePicker::make('created_at')->disabled(),
                TextInput::make('user.name')->label('Utilisateur')->disabled(),
                Textarea::make('description')->columnSpanFull()->disabled(),
                KeyValue::make('properties')->columnSpanFull()->disabled(),
            ]);
    }
}
