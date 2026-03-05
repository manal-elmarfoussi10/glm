<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Filament\Resources\ActivityLogs\Tables\ActivityLogsTable;

class ActivityLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'activityLogs';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return ActivityLogsTable::configure($table)
            ->headerActions([])
            ->recordActions([\Filament\Tables\Actions\ViewAction::make()])
            ->toolbarActions([]);
    }
}
