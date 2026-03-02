<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Support\HtmlString;

class CustomRequestPasswordReset extends BaseRequestPasswordReset
{
    protected static string $layout = 'filament.components.layout.auth';

    protected string $view = 'filament.pages.auth.request-password-reset';

    public function getTitle(): string
    {
        return 'Mot de passe oublié';
    }

    public function getHeading(): string | null
    {
        return 'Réinitialiser votre mot de passe';
    }

    public function getSubheading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        if (! filament()->hasLogin()) {
            return null;
        }

        return new HtmlString($this->loginAction->toHtml());
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

    public function loginAction(): \Filament\Actions\Action
    {
        return parent::loginAction()
            ->label('Retour à la connexion')
            ->icon(null);
    }

    protected function getRequestFormAction(): \Filament\Actions\Action
    {
        return parent::getRequestFormAction()
            ->label('Envoyer le lien de réinitialisation');
    }
}
