<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('action')
                    ->label('Action')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'reservation_created', 'vehicle_created', 'payment_created' => 'success',
                        'reservation_status_changed', 'vehicle_updated' => 'warning',
                        'vehicle_deleted' => 'danger',
                        'csv_export', 'document_download' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('subject_type')
                    ->label('Type')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Utilisateur')
                    ->options(fn () => User::query()->pluck('name', 'id')->toArray()),
                SelectFilter::make('action')
                    ->label('Action')
                    ->options([
                        'reservation_created' => 'Réservations créées',
                        'reservation_status_changed' => 'Statuts changés',
                        'payment_created' => 'Paiements',
                        'vehicle_created' => 'Véhicules ajoutés',
                        'expense_created' => 'Dépenses enregistrées',
                        'csv_export' => 'Exports CSV',
                        'document_download' => 'Téléchargements',
                    ]),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')->label('Du'),
                        DatePicker::make('to')->label('Au'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date))
                            ->when($data['to'], fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date));
                    })
            ]);
    }
}
