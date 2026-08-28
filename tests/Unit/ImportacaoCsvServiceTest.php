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

    public function test_parseia_linha_nubank_negativa_como_despesa(): void
    {
        $linha = ImportacaoCsvService::parsearLinhaNubank('01/08/2026,-15.00,6a6e518b-388f-47a7-bb77-ccf35bb61e5d,Compra no débito - GS PASTEL DE FEIRA LTD');

        $this->assertNotNull($linha);
        $this->assertSame('2026-08-01', $linha['data']->toDateString());
        $this->assertSame('despesa', $linha['tipo']);
        $this->assertSame('Compra no débito - GS PASTEL DE FEIRA LTD', $linha['descricao']);
        $this->assertSame(15.0, $linha['valor']);
        $this->assertNull($linha['categoria']);
    }

    public function test_parseia_linha_nubank_positiva_como_receita(): void
    {
        $linha = ImportacaoCsvService::parsearLinhaNubank('05/08/2026,83.90,6a73cabd-a96e-4b8a-a064-b1e77327d197,Crédito em conta');

        $this->assertNotNull($linha);
        $this->assertSame('receita', $linha['tipo']);
        $this->assertSame(83.90, $linha['valor']);
    }

    public function test_parseia_linha_nubank_com_poucos_campos_retorna_null(): void
    {
        $linha = ImportacaoCsvService::parsearLinhaNubank('abc,def');

        $this->assertNull($linha);
    }

    public function test_parseia_linha_nubank_com_valor_invalido_retorna_null(): void
    {
        $linha = ImportacaoCsvService::parsearLinhaNubank('01/08/2026,abc,identificador,Descricao');

        $this->assertNull($linha);
    }

    public function test_parsear_detecta_formato_nubank_pelo_cabecalho(): void
    {
        $conteudo = "Data,Valor,Identificador,Descrição\n"
            . "01/08/2026,-15.00,6a6e518b-388f-47a7-bb77-ccf35bb61e5d,Compra no débito - GS PASTEL DE FEIRA LTD\n"
            . "05/08/2026,83.90,6a73cabd-a96e-4b8a-a064-b1e77327d197,Crédito em conta\n";

        $resultado = ImportacaoCsvService::parsear($conteudo);

        $this->assertCount(2, $resultado['linhas']);
        $this->assertSame(0, $resultado['erros']);
        $this->assertSame('despesa', $resultado['linhas'][0]['tipo']);
        $this->assertSame('receita', $resultado['linhas'][1]['tipo']);
    }
}
