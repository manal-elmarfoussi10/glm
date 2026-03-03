<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TagsInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Auth\Http\Responses\Contracts\RegistrationResponse;
use Filament\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Model;
use Filament\Facades\Filament;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;

class CustomRegister extends BaseRegister
{
    protected static string $layout = 'filament.components.layout.auth';

    protected string $view = 'filament.pages.auth.register';

    public function getTitle(): string | Htmlable
    {
        return 'Créer un compte';
    }

    public function getHeading(): string | Htmlable | null
    {
        return 'Créer un compte';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    $this->getNameFormComponent(),
                    $this->getEmailFormComponent(),
                    TextInput::make('requested_company_name')
                        ->label('Nom de l\'entreprise')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('requested_ice')
                        ->label('ICE')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('fleet_size')
                        ->label('Taille de la flotte (Nombre de véhicules)')
                        ->numeric()
                        ->required(),
                    TagsInput::make('operating_cities')
                        ->label('Villes d\'opération')
                        ->placeholder('Entrer une ville et appuyer sur Entrée')
                        ->required(),
                    $this->getPasswordFormComponent(),
                    $this->getPasswordConfirmationFormComponent(),
                ]),
            ]);
    }

    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $data['status'] = 'pending';
        $data['role'] = 'company_admin';
        return $data;
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

        $this->sendEmailVerificationNotification($user);

        // DO NOT LOGIN the user!
        // Filament::auth()->login($user);
        // session()->regenerate();

        // Redirect to a custom pending approval page
        return redirect()->route('auth.pending-approval');
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
