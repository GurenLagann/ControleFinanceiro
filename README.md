# Controle Financeiro - Documentacao

Sistema de controle financeiro pessoal desenvolvido em Laravel com MongoDB.

## Indice

1. [Visao Geral](#visao-geral)
2. [Tecnologias](#tecnologias)
3. [Instalacao](#instalacao)
4. [Estrutura do Projeto](#estrutura-do-projeto)
5. [Funcionalidades](#funcionalidades)
6. [API/Rotas](#apirotas)
7. [Models](#models)
8. [Controllers](#controllers)
9. [Views](#views)

---

## Visao Geral

O Controle Financeiro e uma aplicacao web para gerenciamento de financas pessoais que permite:

- Registrar receitas e despesas
- Controlar despesas parceladas
- Gerenciar receitas/despesas recorrentes
- Visualizar graficos e projecoes financeiras
- Acompanhar evolucao financeira ao longo do tempo

## Tecnologias

| Tecnologia | Versao | Descricao |
|------------|--------|-----------|
| PHP | 8.2+ | Linguagem backend |
| Laravel | 12.x | Framework PHP |
| MongoDB | 6.x | Banco de dados NoSQL |
| Bootstrap | 5.3 | Framework CSS |
| Chart.js | 4.x | Biblioteca de graficos |
| GSAP | 3.x | Animacoes JavaScript |
| Docker | - | Containerizacao |

### Containers Docker

- `financas_php` - Aplicacao PHP/Laravel
- `financas_nginx` - Servidor web
- `financas_mongodb` - Banco de dados

## Instalacao

### Pre-requisitos

- Docker e Docker Compose instalados
- Git

### Passos

```bash
# Clonar repositorio
git clone <url-do-repositorio>
cd projeto_financa

# Subir containers
docker-compose up -d

# Instalar dependencias
docker exec financas_php composer install

# Copiar arquivo de ambiente
cp .env.example .env

# Gerar chave da aplicacao
docker exec financas_php php artisan key:generate

# Acessar aplicacao
# http://localhost:8080
```

## Estrutura do Projeto

```
projeto_financa/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── FinancaController.php    # Controller principal
│   └── Models/
│       ├── Receita.php                  # Model de receitas
│       └── Despesa.php                  # Model de despesas
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php            # Layout principal
│       └── financas/
│           └── index.blade.php          # Pagina principal
├── routes/
│   └── web.php                          # Rotas da aplicacao
├── docs/
│   ├── README.md                        # Esta documentacao
│   └── CHANGELOG.md                     # Historico de mudancas
└── docker-compose.yml                   # Configuracao Docker
```

## Funcionalidades

### 1. Receitas

- **Adicionar receita**: Formulario com descricao, valor, data e categoria
- **Receita recorrente**: Opcao para marcar como recorrente (mensal, semanal, quinzenal, anual)
- **Ativar/Desativar**: Toggle para receitas recorrentes
- **Excluir**: Remocao de receitas

### 2. Despesas

#### Despesa Simples
- Registro unico com descricao, valor, data e categoria

#### Despesa Parcelada
- Valor total dividido automaticamente em parcelas
- Suporta de 2 a 48 parcelas
- Cada parcela e criada como registro individual
- Agrupadas por `grupo_parcela_id` (UUID)
- Ajuste automatico da ultima parcela para compensar arredondamento

#### Despesa Recorrente
- Similar a receita recorrente
- Frequencias: mensal, semanal, quinzenal, anual

#### Multiplas Despesas
- Adicionar varias despesas de uma vez
- Mesma data para todas
- Interface dinamica para adicionar/remover linhas

### 3. Gerenciamento de Parcelas

- **Pagar parcela**: Marca proxima parcela como paga (altera data para hoje)
- **Adiantar parcelas**: Pagar multiplas parcelas de uma vez
- **Excluir grupo**: Remove todas as parcelas de uma compra

### 4. Edicao de Despesas

- Editar descricao, valor, data e categoria
- Modal dedicado para edicao

### 5. Graficos e Visualizacoes

| Grafico | Descricao |
|---------|-----------|
| Pizza (Receitas vs Despesas) | Comparativo visual entre entradas e saidas |
| Despesas por Categoria | Barras horizontais por categoria |
| Receitas por Categoria | Barras horizontais por categoria |
| Evolucao 7 Dias | Linha temporal dos ultimos 7 dias |
| Projecao 6 Meses | Barras com projecao futura baseada em recorrentes e parcelas |

#### Funcionalidade de Ampliar Graficos

- Clique em qualquer grafico para abrir modal ampliado
- Exibe detalhes adicionais em tabela
- Grafico em tamanho maior para melhor visualizacao

### 6. Dashboard

- **Cards de resumo**: Total receitas, despesas e saldo
- **Previsao**: Projecao para o proximo mes
- **Tabelas**: Listagem de receitas, despesas e parcelas

## API/Rotas

### Receitas

| Metodo | Rota | Nome | Descricao |
|--------|------|------|-----------|
| POST | `/receitas` | `receitas.store` | Criar receita |
| DELETE | `/receitas/{id}` | `receitas.destroy` | Excluir receita |
| PATCH | `/receitas/{id}/toggle` | `receitas.toggle` | Ativar/desativar recorrente |

### Despesas

| Metodo | Rota | Nome | Descricao |
|--------|------|------|-----------|
| POST | `/despesas` | `despesas.store` | Criar despesa |
| POST | `/despesas/multiplas` | `despesas.storeMultiplas` | Criar multiplas despesas |
| PUT | `/despesas/{id}` | `despesas.update` | Atualizar despesa |
| DELETE | `/despesas/{id}` | `despesas.destroy` | Excluir despesa |
| PATCH | `/despesas/{id}/toggle` | `despesas.toggle` | Ativar/desativar recorrente |
| PATCH | `/despesas/{id}/avancar-parcela` | `despesas.avancarParcela` | Pagar proxima parcela |
| POST | `/despesas/grupo/{grupoId}/adiantar` | `despesas.adiantarParcelas` | Adiantar multiplas parcelas |
| DELETE | `/despesas/grupo/{grupoId}` | `despesas.destroyGrupo` | Excluir todas parcelas do grupo |

## Models

### Receita

```php
// Campos
- _id: ObjectId (MongoDB)
- descricao: string
- valor: float
- data: date
- categoria: string|null
- recorrente: boolean
- frequencia: string|null (mensal, semanal, quinzenal, anual)
- dia_vencimento: integer|null (1-31)
- ativo: boolean
```

### Despesa

```php
// Campos
- _id: ObjectId (MongoDB)
- descricao: string
- valor: float
- valor_total: float|null (para parceladas)
- data: date
- categoria: string|null
- recorrente: boolean
- frequencia: string|null
- dia_vencimento: integer|null
- parcelado: boolean
- parcela_atual: integer|null
- total_parcelas: integer|null
- grupo_parcela_id: string|null (UUID)
- ativo: boolean
```

## Controllers

### FinancaController

Metodos principais:

| Metodo | Descricao |
|--------|-----------|
| `index()` | Carrega dashboard com todos os dados |
| `storeReceita()` | Cria nova receita |
| `storeDespesa()` | Cria despesa (simples, parcelada ou recorrente) |
| `storeMultiplasDespesas()` | Cria varias despesas de uma vez |
| `updateDespesa()` | Atualiza despesa existente |
| `destroyReceita()` | Remove receita |
| `destroyDespesa()` | Remove despesa individual |
| `destroyDespesaGrupo()` | Remove todas parcelas de um grupo |
| `toggleRecorrenteReceita()` | Ativa/desativa receita recorrente |
| `toggleRecorrenteDespesa()` | Ativa/desativa despesa recorrente |
| `avancarParcela()` | Paga proxima parcela |
| `adiantarParcelas()` | Paga multiplas parcelas |

### Calculos do Dashboard

```php
// Totais
$totalReceitas = $receitas->sum('valor');
$totalDespesas = $despesas->sum('valor');
$saldo = $totalReceitas - $totalDespesas;

// Previsao proximo mes
$previsaoReceitas = $receitasRecorrentes->sum('valor');
$previsaoDespesas = $despesasRecorrentes->sum('valor') + $parcelasFuturas;

// Projecao 6 meses
// Usa Carbon::now()->startOfMonth()->addMonths($i) para evitar overflow de datas
```

## Views

### Layout (app.blade.php)

- Navbar com botoes para adicionar receita/despesa
- Tema escuro com cores neon (#00ff88, #ff4757, #3742fa)
- Animacoes GSAP para interatividade
- Bootstrap 5 + Bootstrap Icons

### Index (index.blade.php)

Secoes:

1. **Cards de resumo** - Totais e previsoes
2. **Graficos** - 4 graficos em grid + projecao
3. **Despesas Parceladas** - Tabela com progresso
4. **Receitas Recorrentes** - Lista com toggle
5. **Despesas Recorrentes** - Lista com toggle
6. **Receitas** - Listagem completa
7. **Despesas** - Listagem com edicao

Modais:

- `modalReceita` - Adicionar receita
- `modalDespesa` - Adicionar despesa
- `modalMultiplasDespesas` - Adicionar varias despesas
- `modalEditarDespesa` - Editar despesa
- `modalAdiantar` - Adiantar parcelas
- `modalGrafico` - Grafico ampliado

---

## Contribuicao

1. Crie uma branch para sua feature
2. Faca commit das mudancas
3. Atualize o CHANGELOG.md
4. Abra um Pull Request

## Licenca

Projeto privado - Todos os direitos reservados.
