<?php

namespace Tests\Feature;

use App\Models\Alerta;
use App\Notifications\AlertaCriado;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificacaoAlertaTest extends TestCase
{
    public function test_criar_alerta_envia_notificacao_por_email_quando_configurado(): void
    {
        config(['services.notificacoes.email' => 'usuario@example.com']);
        Notification::fake();

        Alerta::create([
            'titulo' => 'Vencimento Proximo Teste',
            'mensagem' => 'A despesa X vence em 2 dias',
            'tipo' => 'vencimento',
            'data_alerta' => now(),
        ]);

        Notification::assertSentOnDemand(
            AlertaCriado::class,
            fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'usuario@example.com'
        );
    }

    public function test_criar_alerta_nao_envia_notificacao_quando_email_nao_configurado(): void
    {
        config(['services.notificacoes.email' => null]);
        Notification::fake();

        Alerta::create([
            'titulo' => 'Vencimento Proximo Teste',
            'mensagem' => 'A despesa X vence em 2 dias',
            'tipo' => 'vencimento',
            'data_alerta' => now(),
        ]);

        Notification::assertNothingSent();
    }
}
