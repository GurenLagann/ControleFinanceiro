<?php

namespace Tests\Unit;

use App\Services\ImportacaoCsvService;
use PHPUnit\Framework\TestCase;

class ImportacaoCsvServiceTest extends TestCase
{
    public function test_parseia_linha_de_receita_valida(): void
    {
        $linha = ImportacaoCsvService::parsearLinha('28/08/2026;Receita;"Salário";3000,00;"Salário"');

        $this->assertNotNull($linha);
        $this->assertSame('2026-08-28', $linha['data']->toDateString());
        $this->assertSame('receita', $linha['tipo']);
        $this->assertSame('Salário', $linha['descricao']);
        $this->assertSame(3000.0, $linha['valor']);
        $this->assertSame('Salário', $linha['categoria']);
    }

    public function test_parseia_linha_de_despesa_valida(): void
    {
        $linha = ImportacaoCsvService::parsearLinha('05/01/2026;Despesa;"Mercado";150,50;"Alimentação"');

        $this->assertNotNull($linha);
        $this->assertSame('despesa', $linha['tipo']);
        $this->assertSame(150.50, $linha['valor']);
    }

    public function test_categoria_vazia_vira_null(): void
    {
        $linha = ImportacaoCsvService::parsearLinha('05/01/2026;Despesa;"Mercado";150,50;""');

        $this->assertNull($linha['categoria']);
    }

    public function test_linha_com_poucos_campos_retorna_null(): void
    {
        $linha = ImportacaoCsvService::parsearLinha('abc;def');

        $this->assertNull($linha);
    }

    public function test_data_invalida_retorna_null(): void
    {
        $linha = ImportacaoCsvService::parsearLinha('99/99/9999;Receita;"X";10,00;""');

        $this->assertNull($linha);
    }

    public function test_valor_invalido_retorna_null(): void
    {
        $linha = ImportacaoCsvService::parsearLinha('28/08/2026;Receita;"X";abc;""');

        $this->assertNull($linha);
    }

    public function test_tipo_invalido_retorna_null(): void
    {
        $linha = ImportacaoCsvService::parsearLinha('28/08/2026;Transferencia;"X";10,00;""');

        $this->assertNull($linha);
    }

    public function test_parsear_conteudo_completo_ignora_cabecalho_e_conta_erros(): void
    {
        $conteudo = "Data;Tipo;Descricao;Valor;Categoria\n"
            . "28/08/2026;Receita;\"Salário\";3000,00;\"Salário\"\n"
            . "linha invalida\n"
            . "05/01/2026;Despesa;\"Mercado\";150,50;\"Alimentação\"\n";

        $resultado = ImportacaoCsvService::parsear($conteudo);

        $this->assertCount(2, $resultado['linhas']);
        $this->assertSame(1, $resultado['erros']);
    }
}
