<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ReservationsRelationManager extends RelationManager
{
    protected static string $relationship = 'reservations';

    protected static ?string $recordTitleAttribute = 'reference';

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
                Tables\Columns\TextColumn::make('reference')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Client')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vehicle.plate_number')
                    ->label('Véhicule'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'confirmed' => 'info',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total_price')
                    ->money('mad')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_at')
                    ->dateTime()
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
                        if ($data['from']) $query->where('start_at', '>=', $data['from']);
                        if ($data['to']) $query->where('end_at', '<=', $data['to']);
                        $records = $query->get();
                        
                        \App\Models\ActivityLog::log(
                            action: 'csv_export',
                            subject: $livewire->getOwnerRecord(),
                            description: "Export CSV des réservations - " . $records->count() . " enregistrements",
                            properties: ['tab' => 'Réservations', 'count' => $records->count()]
                        );
                        
                        return \App\Services\ExportService::streamCsv(
                            filename: 'reservations_' . $livewire->getOwnerRecord()->name,
                            headers: ['Référence', 'Client', 'Véhicule', 'Statut', 'Prix Total', 'Début', 'Fin'],
                            records: $records,
                            rowCallback: fn ($record) => [
                                $record->reference,
                                $record->customer->name,
                                $record->vehicle->plate_number,
                                $record->status,
                                $record->total_price,
                                $record->start_at->format('Y-m-d H:i'),
                                $record->end_at->format('Y-m-d H:i'),
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
