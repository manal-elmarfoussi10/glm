<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VehiclesRelationManager extends RelationManager
{
    protected static string $relationship = 'vehicles';

    protected static ?string $recordTitleAttribute = 'brand';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function canCreate(): bool
    {
        return false;
    }

    public function canEdit($record): bool
    {
        return false;
    }

    public function canDelete($record): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('brand')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('model')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('plate_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'rented' => 'warning',
                        'maintenance' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('daily_rate')
                    ->money('mad')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Exporter CSV')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('De'),
                        \Filament\Forms\Components\DatePicker::make('to')->label('À'),
                    ])
                    ->action(function (array $data, RelationManager $livewire) {
                        $query = $livewire->getRelationship()->getQuery();
                        if ($data['from']) $query->where('created_at', '>=', $data['from']);
                        if ($data['to']) $query->where('created_at', '<=', $data['to']);
                        $records = $query->get();
                        
                        \App\Models\ActivityLog::log(
                            action: 'csv_export',
                            subject: $livewire->getOwnerRecord(),
                            description: "Export CSV des véhicules - " . $records->count() . " enregistrements",
                            properties: ['tab' => 'Véhicules', 'count' => $records->count()]
                        );
                        
                        return \App\Services\ExportService::streamCsv(
                            filename: 'vehicules_' . $livewire->getOwnerRecord()->name,
                            headers: ['Marque', 'Modèle', 'Matricule', 'Statut', 'Prix/Jour'],
                            records: $records,
                            rowCallback: fn ($record) => [
                                $record->brand,
                                $record->model,
                                $record->plate_number,
                                $record->status,
                                $record->daily_rate,
                            ]
                        );
                    })
                    ->visible(fn () => auth()->user()->isSuperAdmin()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Read-only
            ]);
    }
}
