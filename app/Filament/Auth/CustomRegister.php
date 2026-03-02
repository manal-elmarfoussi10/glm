<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

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
