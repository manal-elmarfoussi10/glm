<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ContractsRelationManager extends RelationManager
{
    /**
     * Requirement: Contrats: list + preview (read-only).
     * These are ReservationContract records linked to the company's reservations.
     */
    protected static string $relationship = 'reservations'; // We'll show signed contracts via reservations.

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Réservation')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reservationContract.contractTemplate.name')
                    ->label('Modèle'),
                Tables\Columns\TextColumn::make('reservationContract.status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'generated' => 'info',
                        'signed' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('reservationContract.generated_at')
                    ->label('Généré le')
                    ->dateTime(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Exporter CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->form([
                        Tables\Forms\Components\DatePicker::make('from')->label('Du'),
                        Tables\Forms\Components\DatePicker::make('to')->label('Au'),
                    ])
                    ->action(function (array $data, RelationManager $livewire) {
                        $query = $livewire->getRelationship()->getQuery()
                            ->whereHas('reservationContract');

                        if ($data['from']) {
                            $query->whereHas('reservationContract', fn($q) => $q->whereDate('generated_at', '>=', $data['from']));
                        }
                        if ($data['to']) {
                            $query->whereHas('reservationContract', fn($q) => $q->whereDate('generated_at', '<=', $data['to']));
                        }

                        $records = $query->with(['reservationContract.contractTemplate'])->get();

                        \App\Models\ActivityLog::log(
                            action: 'csv_export',
                            subject: $livewire->getOwnerRecord(),
                            description: "Export CSV des contrats - " . $records->count() . " enregistrements",
                            properties: ['tab' => 'Contrats', 'count' => $records->count()]
                        );

                        return \App\Services\ExportService::streamCsv(
                            'contrats_' . now()->format('Y-m-d_H-i') . '.csv',
                            ['Référence Réservation', 'Modèle', 'Statut', 'Généré le'],
                            $records->map(fn ($r) => [
                                $r->reference,
                                $r->reservationContract?->contractTemplate?->name,
                                $r->reservationContract?->status,
                                $r->reservationContract?->generated_at?->format('Y-m-d H:i') ?? '-',
                            ])->toArray()
                        );
                    })
                    ->visible(fn () => auth()->user()->isSuperAdmin()),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => $record->reservationContract ? route('contracts.preview', $record->reservationContract) : null)
                    ->openUrlInNewTab()
                    ->hidden(fn ($record) => !$record->reservationContract)
                    ->action(function ($record, RelationManager $livewire) {
                        \App\Models\ActivityLog::log(
                            action: 'contract_preview',
                            subject: $record,
                            description: "Consultation du contrat pour la réservation {$record->reference}",
                            properties: ['reservation_id' => $record->id, 'contract_id' => $record->reservationContract?->id]
                        );
                    }),
            ]);
    }
}
