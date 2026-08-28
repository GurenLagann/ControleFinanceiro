<?php

namespace Tests\Feature;

use App\Models\Despesa;
use Tests\TestCase;

class ReservaEmergenciaTest extends TestCase
{
    /**
     * Testa se a pagina de metas expoe a faixa recomendada de reserva de emergencia
     */
    public function test_pagina_metas_expoe_faixa_de_reserva_de_emergencia(): void
    {
        $despesa = Despesa::create([
            'descricao' => 'Despesa Reserva Teste',
            'valor' => 300,
            'data' => now(),
            'recorrente' => false,
            'parcelado' => false,
            'ativo' => true,
        ]);

        $response = $this->get('/metas');

        $response->assertViewHas('reservaEmergencia', function ($faixa) {
            return $faixa['minimo'] >= 0 && $faixa['ideal'] >= $faixa['minimo'];
        });

        $despesa->delete();
    }
}
