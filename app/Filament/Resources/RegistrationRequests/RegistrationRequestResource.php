<?php

namespace App\Filament\Resources\RegistrationRequests;
 
use App\Filament\Resources\RegistrationRequests\Pages\ManageRegistrationRequests;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RegistrationRequestResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Demandes d’inscription';
    protected static ?string $pluralLabel = 'Demandes d’inscription';
    protected static ?string $modelLabel = 'Demande d’inscription';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserPlus;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('role', 'company_admin');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(255),
                TextInput::make('requested_company_name')
                    ->label('Entreprise')
                    ->maxLength(255),
                TextInput::make('requested_ice')
                    ->label('ICE')
                    ->maxLength(255),
                TextInput::make('requested_plan')
                    ->label('Plan')
                    ->maxLength(255),
                TextInput::make('requested_country')
                    ->label('Pays')
                    ->maxLength(255),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('requested_company_name')->label('Entreprise'),
                TextEntry::make('requested_ice')->label('ICE'),
                TextEntry::make('name')->label('Responsable'),
                TextEntry::make('email')->label('Email'),
                TextEntry::make('phone')->label('Téléphone'),
                TextEntry::make('requested_country')->label('Pays'),
                TextEntry::make('requested_plan')->label('Plan choisi'),
                TextEntry::make('fleet_size')->label('Taille de la flotte'),
                TextEntry::make('operating_cities')->label('Villes d\'opération')->listWithLineBreaks(),
                TextEntry::make('registration_message')->label('Message de l\'inscrit'),
                
                \Filament\Infolists\Components\Section::make('Audit & Notes Internes')
                    ->schema([
                        TextEntry::make('admin_notes')->label('Notes internes admin'),
                        TextEntry::make('rejection_reason')
                            ->label('Raison du refus')
                            ->hidden(fn ($record) => $record->status !== 'rejected'),
                        TextEntry::make('approved_at')->label('Approuvé le')->dateTime(),
                        TextEntry::make('approvedBy.name')->label('Approuvé par'),
                        TextEntry::make('rejected_at')->label('Refusé le')->dateTime(),
                        TextEntry::make('rejectedBy.name')->label('Refusé par'),
                    ])->columns(2),

                \Filament\Infolists\Components\Section::make('Historique des actions')
                    ->schema([
                        TextEntry::make('registration_logs')
                            ->label('')
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) return 'Aucun historique';
                                $html = '<div class="space-y-2">';
                                foreach (array_reverse($state) as $log) {
                                    $action = match($log['action']) {
                                        'approved' => '<span class="text-success-600 font-bold">APPROUVÉ</span>',
                                        'rejected' => '<span class="text-danger-600 font-bold">REFUSÉ</span>',
                                        default => strtoupper($log['action']),
                                    };
                                    $html .= "<div class='text-sm border-l-2 border-gray-200 pl-2'>
                                        <div>{$action} par <strong>{$log['user_name']}</strong> le {$log['timestamp']}</div>
                                        <div class='text-gray-500'>{$log['note']}</div>
                                    </div>";
                                }
                                $html .= '</div>';
                                return new \Illuminate\Support\HtmlString($html);
                            }),
                    ])->collapsible(),
                
                TextEntry::make('created_at')->label('Date d\'inscription')->dateTime(),
                TextEntry::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'active' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'active' => 'Approuvé',
                        'rejected' => 'Refusé',
                        default => $state,
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('requested_company_name')
                    ->label('Entreprise')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Responsable')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable(),
                TextColumn::make('requested_country')
                    ->label('Pays')
                    ->sortable(),
                TextColumn::make('requested_plan')
                    ->label('Plan')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Inscription')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'active' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'active' => 'Approuvé',
                        'rejected' => 'Refusé',
                        default => $state,
                    })
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'En attente',
                        'active' => 'Approuvé',
                        'rejected' => 'Refusé',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('requested_plan')
                    ->label('Plan')
                    ->options([
                        'starter' => 'Starter',
                        'professional' => 'Professional',
                        'enterprise' => 'Enterprise',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()->label('Voir')->slideOver()->icon('heroicon-o-eye'),
                ActionGroup::make([
                    Action::make('approve')
                        ->label('Approuver')
                        ->color('success')
                        ->icon('heroicon-o-check-circle')
                        ->hidden(fn (User $record): bool => $record->status !== 'pending')
                        ->form([
                            \Filament\Forms\Components\Select::make('trial_days')
                                ->label('Durée de l\'essai')
                                ->options([
                                    7 => '7 jours',
                                    14 => '14 jours',
                                    30 => '30 jours',
                                ])
                                ->default(14)
                                ->required(),
                            \Filament\Forms\Components\TextInput::make('custom_pricing')
                                ->label('Tarification personnalisée (optionnel)'),
                            \Filament\Forms\Components\Textarea::make('admin_notes')
                                ->label('Note interne admin')
                                ->rows(3),
                        ])
                        ->action(function (User $record, array $data): void {
                            \Illuminate\Support\Facades\DB::transaction(function () use ($record, $data) {
                                // 1. Create Company
                                $company = \App\Models\Company::create([
                                    'name' => $record->requested_company_name,
                                    'ice' => $record->requested_ice,
                                    'status' => 'active',
                                    'plan' => $record->requested_plan,
                                    'trial_ends_at' => now()->addDays($data['trial_days']),
                                    'subscription_status' => 'trial',
                                ]);

                                // 2. Update User
                                $record->update([
                                    'company_id' => $company->id,
                                    'status' => 'active',
                                    'approved_at' => now(),
                                    'approved_by' => auth()->id(),
                                    'admin_notes' => $data['admin_notes'],
                                ]);

                                // 3. Log Action
                                $record->logRegistrationAction('approved', $data['admin_notes'], [
                                    'trial_days' => $data['trial_days'],
                                    'company_id' => $company->id,
                                    'custom_pricing' => $data['custom_pricing'] ?? null,
                                ]);

                                $record->notify(new \App\Notifications\CompanyApprovedNotification($company->name, url('/app')));
                            });

                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Inscription approuvée')
                                ->body("L'entreprise {$record->requested_company_name} a été créée et l'utilisateur est maintenant actif.")
                                ->send();
                        }),
                    Action::make('reject')
                        ->label('Refuser')
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->hidden(fn (User $record): bool => $record->status !== 'pending')
                        ->form([
                            \Filament\Forms\Components\Textarea::make('rejection_reason')
                                ->label('Raison du refus')
                                ->required()
                                ->rows(3),
                            \Filament\Forms\Components\Textarea::make('admin_notes')
                                ->label('Note interne admin')
                                ->rows(3),
                        ])
                        ->action(function (User $record, array $data): void {
                            $record->update([
                                'status' => 'rejected',
                                'rejection_reason' => $data['rejection_reason'],
                                'rejected_at' => now(),
                                'rejected_by' => auth()->id(),
                                'admin_notes' => $data['admin_notes'],
                            ]);

                            $record->logRegistrationAction('rejected', $data['admin_notes'], [
                                'reason' => $data['rejection_reason'],
                            ]);

                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Inscription refusée')
                                ->send();
                        }),
                ])->label('Gérer')->icon('heroicon-m-ellipsis-vertical')->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRegistrationRequests::route('/'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\RegistrationStats::class,
        ];
    }
}
