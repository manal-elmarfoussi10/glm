<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class CustomLogin extends BaseLogin
{
    protected static string $layout = 'filament.components.layout.auth';

    protected string $view = 'filament.pages.auth.login';

    public function getTitle(): string
    {
        return 'Connexion';
    }

    public function getHeading(): string
    {
        if (filled($this->userUndertakingMultiFactorAuthentication)) {
            return 'Vérification en deux étapes';
        }

        return 'Connexion à votre espace';
    }

    public function getSubheading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        if (filled($this->userUndertakingMultiFactorAuthentication)) {
            return 'Veuillez confirmer votre identité pour continuer.';
        }

        if (! filament()->hasRegistration()) {
            return null;
        }

        return new HtmlString('Pas encore de compte ? ' . $this->registerAction->toHtml());
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Adresse e-mail')
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus();
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Mot de passe')
            ->hint(
                filament()->hasPasswordReset()
                    ? new HtmlString(Blade::render('<x-filament::link :href="filament()->getRequestPasswordResetUrl()">Mot de passe oublié ?</x-filament::link>'))
                    : null
            )
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required();
    }

    protected function getRememberFormComponent(): Component
    {
        return Checkbox::make('remember')
            ->label('Se souvenir de moi');
    }

    public function registerAction(): \Filament\Actions\Action
    {
        return parent::registerAction()
            ->label('Créer un compte');
    }

    protected function getAuthenticateFormAction(): \Filament\Actions\Action
    {
        return parent::getAuthenticateFormAction()
            ->label('Se connecter');
    }

    protected function getMultiFactorAuthenticateFormAction(): \Filament\Actions\Action
    {
        return parent::getMultiFactorAuthenticateFormAction()
            ->label('Vérifier');
    }
}
