<?php

namespace App\Filament\Auth;

use App\Models\Plan;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TagsInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Auth\Http\Responses\Contracts\RegistrationResponse;
use Filament\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Model;
use Filament\Facades\Filament;
use Filament\Schemas\Components\Component;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;

class CustomRegister extends BaseRegister
{
    protected static string $layout = 'filament.components.layout.auth-register';

    protected string $view = 'filament.pages.auth.register';

    public function getTitle(): string | Htmlable
    {
        return 'Créer un compte';
    }

    public function getHeading(): string | Htmlable | null
    {
        return 'Créer un compte';
    }

    public function getMaxWidth(): Width | string | null
    {
        return '3xl';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)->schema([
                    $this->getNameFormComponent(),
                    $this->getEmailFormComponent(),
                    TextInput::make('requested_company_name')
                        ->label('Nom de l\'entreprise')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('requested_ice')
                        ->label('ICE')
                        ->required()
                        ->string()
                        ->regex('/^\d+$/', 'L\'ICE doit contenir uniquement des chiffres.')
                        ->minLength(12)
                        ->maxLength(20)
                        ->validationAttribute('ICE')
                        ->live(onBlur: true)
                        ->helperText('12 à 20 chiffres.'),
                    TextInput::make('phone')
                        ->label('Téléphone')
                        ->tel()
                        ->required()
                        ->maxLength(255),
                    TextInput::make('requested_country')
                        ->label('Pays')
                        ->required()
                        ->maxLength(255),
                    Select::make('requested_plan')
                        ->label('Plan choisi')
                        ->options(fn () => static::activePlansOptions())
                        ->required()
                        ->searchable(),
                    TextInput::make('fleet_size')
                        ->label('Taille de la flotte (Nombre de véhicules)')
                        ->numeric()
                        ->required(),
                    TagsInput::make('operating_cities')
                        ->label('Villes d\'opération')
                        ->placeholder('Ex. Casablanca'),
                    Textarea::make('registration_message')
                        ->label('Message / Notes (Optionnel)')
                        ->rows(3),
                    $this->getPasswordFormComponent(),
                    $this->getPasswordConfirmationFormComponent(),
                ]),
            ]);
    }

    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $data['status'] = 'pending';
        $data['role'] = 'company_admin';

        $planId = $data['requested_plan'] ?? null;
        if ($planId && is_numeric($planId)) {
            $plan = Plan::find($planId);
            $data['requested_plan'] = $plan ? $plan->name : (string) $planId;
        }

        return $data;
    }

    /** @return array<int|string, string> id => label for active plans */
    public static function activePlansOptions(): array
    {
        $plans = Plan::query()->where('is_active', true)->orderBy('name')->get();
        $options = [];
        foreach ($plans as $plan) {
            $label = $plan->name;
            if ($plan->monthly_price !== null && $plan->monthly_price !== '') {
                $label .= ' — ' . $plan->monthly_price . ' MAD/mois';
            }
            $options[$plan->id] = $label;
        }
        return $options;
    }

    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        if ($this->isRegisterRateLimited($this->data['email'] ?? '')) {
            return null;
        }

        $user = $this->wrapInDatabaseTransaction(function (): Model {
            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeRegister($data);

            $this->callHook('beforeRegister');

            $user = $this->handleRegistration($data);

            $this->form->model($user)->saveRelationships();

            $this->callHook('afterRegister');

            return $user;
        });

        event(new Registered($user));

        try {
            $this->sendEmailVerificationNotification($user);
        } catch (\Throwable $e) {
            report($e);
            // Don't block redirect if email fails (e.g. mail not configured)
        }

        // Trigger redirect in Livewire so the client actually navigates to the success page
        $this->redirect(route('auth.pending-approval'));
        return app(RegistrationResponse::class);
    }

    public function getSubheading(): string | Htmlable | null
    {
        if (! filament()->hasLogin()) {
            return null;
        }

        return new HtmlString('Déjà un compte ? ' . $this->loginAction->toHtml());
    }

    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label('Nom complet')
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Adresse e-mail')
            ->email()
            ->required()
            ->maxLength(255)
            ->unique($this->getUserModel());
    }

    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()
            ->label('Mot de passe')
            ->validationAttribute('mot de passe');
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return parent::getPasswordConfirmationFormComponent()
            ->label('Confirmer le mot de passe');
    }

    public function loginAction(): \Filament\Actions\Action
    {
        return parent::loginAction()
            ->label('Se connecter');
    }

    public function getRegisterFormAction(): \Filament\Actions\Action
    {
        return parent::getRegisterFormAction()
            ->label('Créer mon compte');
    }
}
