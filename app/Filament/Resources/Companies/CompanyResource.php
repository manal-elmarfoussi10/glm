<?php

namespace App\Filament\Resources\Companies;

use App\Filament\Resources\Companies\Pages\EditCompany;
use App\Filament\Resources\Companies\Pages\ListCompanies;
use App\Filament\Resources\Companies\Pages\ViewCompany;
use App\Models\Company;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->disabled(!auth()->user()->isSuperAdmin()),
                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                    ])
                    ->required(),
                Select::make('plan')
                    ->options([
                        'starter' => 'Starter',
                        'professional' => 'Professional',
                        'enterprise' => 'Enterprise',
                    ])
                    ->required(),
                Select::make('plan_id')
                    ->label('Plan Système')
                    ->relationship('planRelation', 'name')
                    ->required(),
                TextInput::make('subscription_status')
                    ->label('Statut Abonnement')
                    ->disabled(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Infolists\Components\Tabs::make('Company 360°')
                    ->tabs([
                        \Filament\Infolists\Components\Tabs\Tab::make('Résumé')
                            ->icon('heroicon-o-presentation-chart-line')
                            ->schema([
                                \Filament\Infolists\Components\Grid::make(4)
                                    ->schema([
                                        \Filament\Infolists\Components\Section::make('Performance')
                                            ->schema([
                                                TextEntry::make('revenue')
                                                    ->label('Revenu Total')
                                                    ->state(fn (Company $record) => $record->payments()->sum('amount'))
                                                    ->money('mad')
                                                    ->color('success')
                                                    ->weight('bold'),
                                                TextEntry::make('vehicles_count')
                                                    ->label('Flotte')
                                                    ->state(fn (Company $record) => $record->vehicles()->count())
                                                    ->suffix(' véhicules')
                                                    ->icon('heroicon-o-truck'),
                                                TextEntry::make('reservations_count')
                                                    ->label('Réservations')
                                                    ->state(fn (Company $record) => $record->reservations()->count())
                                                    ->icon('heroicon-o-calendar'),
                                                TextEntry::make('customers_count')
                                                    ->label('Clients')
                                                    ->state(fn (Company $record) => $record->customers()->count())
                                                    ->icon('heroicon-o-user-group'),
                                            ])->columns(4),
                                    ]),
                                \Filament\Infolists\Components\Section::make('Informations Générales')
                                    ->schema([
                                        TextEntry::make('name')->label('Nom de l\'entreprise'),
                                        TextEntry::make('email')->label('Email de contact')->copyable(),
                                        TextEntry::make('phone')->label('Téléphone')->copyable(),
                                        TextEntry::make('ice')->label('ICE'),
                                        TextEntry::make('status')
                                            ->label('Statut')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'active' => 'success',
                                                'inactive' => 'gray',
                                                'suspended' => 'danger',
                                                default => 'gray',
                                            }),
                                        TextEntry::make('plan')
                                            ->label('Offre Actuelle')
                                            ->badge()
                                            ->color('info'),
                                        TextEntry::make('approved_at')
                                            ->label('Date d\'approbation')
                                            ->dateTime('d/m/Y H:i'),
                                    ])->columns(3),
                            ]),
                        \Filament\Infolists\Components\Tabs\Tab::make('Hub Documents')
                            ->icon('heroicon-o-document-duplicate')
                            ->schema([
                                \Filament\Infolists\Components\RepeatableEntry::make('all_documents')
                                    ->label('Fichiers Centralisés (Clients & Véhicules)')
                                    ->state(function (Company $record) {
                                        $docs = [];
                                        
                                        // Customer docs
                                        foreach ($record->customers as $customer) {
                                            if ($customer->cin_front_path) $docs[] = ['name' => "CIN Face - {$customer->name}", 'path' => $customer->cin_front_path, 'type' => 'Client', 'ref' => $customer->cin];
                                            if ($customer->cin_back_path) $docs[] = ['name' => "CIN Dos - {$customer->name}", 'path' => $customer->cin_back_path, 'type' => 'Client', 'ref' => $customer->cin];
                                            if ($customer->license_document_path) $docs[] = ['name' => "Permis - {$customer->name}", 'path' => $customer->license_document_path, 'type' => 'Client', 'ref' => $customer->cin];
                                        }
                                        
                                        // Vehicle docs
                                        foreach ($record->vehicles as $vehicle) {
                                            if ($vehicle->insurance_document_path) $docs[] = ['name' => "Assurance - {$vehicle->plate}", 'path' => $vehicle->insurance_document_path, 'type' => 'Véhicule', 'ref' => $vehicle->plate];
                                            if ($vehicle->vignette_receipt_path) $docs[] = ['name' => "Vignette - {$vehicle->plate}", 'path' => $vehicle->vignette_receipt_path, 'type' => 'Véhicule', 'ref' => $vehicle->plate];
                                            if ($vehicle->visite_document_path) $docs[] = ['name' => "Visite - {$vehicle->plate}", 'path' => $vehicle->visite_document_path, 'type' => 'Véhicule', 'ref' => $vehicle->plate];
                                            if ($vehicle->financing_contract_path) $docs[] = ['name' => "Contrat Financement - {$vehicle->plate}", 'path' => $vehicle->financing_contract_path, 'type' => 'Véhicule', 'ref' => $vehicle->plate];
                                        }
                                        
                                        return $docs;
                                    })
                                    ->schema([
                                        TextEntry::make('type')
                                            ->badge()
                                            ->color(fn ($state) => $state === 'Client' ? 'info' : 'warning')
                                            ->icon(fn ($state) => $state === 'Client' ? 'heroicon-o-user' : 'heroicon-o-truck'),
                                        TextEntry::make('ref')->label('Référence')->weight('bold'),
                                        TextEntry::make('name')->label('Type de Document'),
                                        \Filament\Infolists\Components\Actions\Action::make('download')
                                            ->label('Télécharger')
                                            ->icon('heroicon-o-arrow-down-tray')
                                            ->url(fn ($state, $record) => \Illuminate\Support\Facades\Storage::url($record['path']))
                                            ->openUrlInNewTab()
                                            ->action(function ($record, Company $owner) {
                                                \App\Models\ActivityLog::log(
                                                    action: 'document_download',
                                                    subject: $owner,
                                                    description: "Téléchargement du document : {$record['name']}",
                                                    properties: ['document' => $record['name'], 'path' => $record['path']]
                                                );
                                            }),
                                    ])->columns(4),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'suspended' => 'danger',
                        default => 'gray',
                    }),
                \Filament\Tables\Columns\TextColumn::make('plan')
                    ->badge()
                    ->color('info'),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\Companies\RelationManagers\CustomersRelationManager::class,
            \App\Filament\Resources\Companies\RelationManagers\VehiclesRelationManager::class,
            \App\Filament\Resources\Companies\RelationManagers\ReservationsRelationManager::class,
            \App\Filament\Resources\Companies\RelationManagers\PaymentsRelationManager::class,
            \App\Filament\Resources\Companies\RelationManagers\ContractsRelationManager::class,
            \App\Filament\Resources\Companies\RelationManagers\ActivityLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCompanies::route('/'),
            'create' => \App\Filament\Resources\Companies\Pages\CreateCompany::route('/create'),
            'view' => ViewCompany::route('/{record}'),
            'edit' => EditCompany::route('/{record}/edit'),
        ];
    }
}
