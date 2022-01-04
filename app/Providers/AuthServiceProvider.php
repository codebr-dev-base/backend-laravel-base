<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        VerifyEmail::createUrlUsing(function ($notifiable) {
            $verifyUrl = URL::temporarySignedRoute(
                'verification.verify',
                Carbon::now()->addMinutes(Config::get('auth.verification.expire', 720)),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );

            return $verifyUrl;

            //return config('app.frontend_url') .  '?verify_url=' . urlencode($verifyUrl);

        });

        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            return (new MailMessage)
                ->greeting('Olá')
                ->subject('Verifique o endereço de e-mail')
                ->line('Clique no botão abaixo para verificar o seu endereço de e-mail.')
                ->action('Verifique o endereço de e-mail', $url)
                ->line('Se você não criou uma conta, nenhuma ação adicional é necessária.');
        });

        ResetPassword::toMailUsing(function ($notifiable, $token) {
            $urlReset = config('app.url') . "/api/reset-password?token=$token&email={$notifiable->getEmailForPasswordReset()}";

            return (new MailMessage)
            ->subject('Notificação de redefinição de senha')
            ->line('Você está recebendo este e-mail porque recebemos uma solicitação de redefinição de senha para sua conta.')
            ->action('Redefinir senha', $urlReset)
            ->line('Este link de redefinição de senha irá expirar em: minutos de contagem.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')])
            ->line('Se você não solicitou uma redefinição de senha, nenhuma ação adicional é necessária.');
        });

        //
    }
}
