<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

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
                Tables\Columns\TextColumn::make('reservation.reference')
                    ->label('Réservation')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('mad')
                    ->sortable(),
                Tables\Columns\TextColumn::make('method')
                    ->badge(),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\TextColumn::make('paid_at')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('method')
                    ->options([
                        'cash' => 'Cash',
                        'virement' => 'Virement',
                        'TPE' => 'TPE',
                        'cheque' => 'Cheque',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'deposit' => 'Caution',
                        'rental' => 'Location',
                        'fee' => 'Frais',
                        'refund' => 'Remboursement',
                    ]),
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
                        if ($data['from']) $query->where('paid_at', '>=', $data['from']);
                        if ($data['to']) $query->where('paid_at', '<=', $data['to']);
                        $records = $query->get();
                        
                        \App\Models\ActivityLog::log(
                            action: 'csv_export',
                            subject: $livewire->getOwnerRecord(),
                            description: "Export CSV des paiements - " . $records->count() . " enregistrements",
                            properties: ['tab' => 'Paiements', 'count' => $records->count()]
                        );
                        
                        return \App\Services\ExportService::streamCsv(
                            filename: 'paiements_' . $livewire->getOwnerRecord()->name,
                            headers: ['Référence', 'Réservation', 'Montant', 'Méthode', 'Type', 'Date'],
                            records: $records,
                            rowCallback: fn ($record) => [
                                $record->reference,
                                $record->reservation->reference,
                                $record->amount,
                                $record->method,
                                $record->type,
                                $record->paid_at->format('Y-m-d'),
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
