<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CustomersRelationManager extends RelationManager
{
    protected static string $relationship = 'customers';

    protected static ?string $recordTitleAttribute = 'name';

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
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
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
                        
                        if ($data['from']) $query->where('created_at', '>=', $data['from']);
                        if ($data['to']) $query->where('created_at', '<=', $data['to']);
                        
                        $records = $query->get();
                        
                        \App\Models\ActivityLog::log(
                            action: 'csv_export',
                            subject: $livewire->getOwnerRecord(),
                            description: "Export CSV des clients - " . $records->count() . " enregistrements",
                            properties: ['tab' => 'Clients', 'count' => $records->count()]
                        );
                        
                        return \App\Services\ExportService::streamCsv(
                            filename: 'clients_' . $livewire->getOwnerRecord()->name,
                            headers: ['Nom', 'Email', 'Téléphone', 'Ville', 'Date'],
                            records: $records,
                            rowCallback: fn ($record) => [
                                $record->name,
                                $record->email,
                                $record->phone,
                                $record->city,
                                $record->created_at->format('Y-m-d H:i'),
                            ]
                        );
                    })
                    ->visible(fn () => auth()->user()->isSuperAdmin()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->after(function ($record, RelationManager $livewire) {
                        \App\Models\AuditLog::log(
                            action: 'view_client_details',
                            subjectType: 'Company',
                            subjectId: $livewire->getOwnerRecord()->id,
                            newValues: ['client_id' => $record->id, 'client_name' => $record->name]
                        );
                    }),
            ])
            ->bulkActions([
                // Read-only
            ]);
    }
}
