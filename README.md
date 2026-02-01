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
10. [Servicos e Traits](#servicos-e-traits)

---

## Visao Geral

O Controle Financeiro e uma aplicacao web para gerenciamento de financas pessoais que permite:

- Registrar receitas e despesas
- Controlar despesas parceladas
- Gerenciar receitas/despesas recorrentes
- Visualizar graficos e projecoes financeiras
- Acompanhar evolucao financeira ao longo do tempo
- Gerenciar categorias personalizadas
- Definir e acompanhar metas financeiras
- Receber alertas inteligentes
- Auditar todas as operacoes do sistema

## Tecnologias

| Tecnologia | Versao | Descricao |
|------------|--------|-----------|
| PHP | 8.2+ | Linguagem backend |
| Laravel | 12.x | Framework PHP |
| MongoDB | 7.0 | Banco de dados NoSQL |
| Bootstrap | 5.3 | Framework CSS |
| Chart.js | 4.x | Biblioteca de graficos |
| GSAP | 3.x | Animacoes JavaScript |
| Docker | - | Containerizacao |

### Containers Docker

- `php` - Aplicacao PHP/Laravel
- `nginx` - Servidor web (porta 8080)
- `mongodb` - Banco de dados (porta 27017)
- `mongo-express` - Interface web MongoDB (porta 8081)

## Instalacao

### Pre-requisitos

- Docker e Docker Compose instalados
- Git

### Passos

```bash
# Clonar repositorio
git clone <url-do-repositorio>
cd ControleFinanceiro

# Subir containers
docker-compose up -d

# Instalar dependencias
docker exec -it php composer install

# Copiar arquivo de ambiente
cp .env.example .env

# Gerar chave da aplicacao
docker exec -it php php artisan key:generate

# Acessar aplicacao
# http://localhost:8080

# Acessar MongoDB Express (interface web)
# http://localhost:8081
```

## Estrutura do Projeto

```
ControleFinanceiro/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── FinancaController.php       # Receitas e Despesas
│   │       └── ConfiguracaoController.php  # Categorias, Metas, Alertas, Logs
│   ├── Models/
│   │   ├── Receita.php                     # Model de receitas
│   │   ├── Despesa.php                     # Model de despesas
│   │   ├── Categoria.php                   # Model de categorias
│   │   ├── Meta.php                        # Model de metas
│   │   ├── Alerta.php                      # Model de alertas
│   │   └── AuditLog.php                    # Model de logs de auditoria
│   ├── Services/
│   │   └── CacheService.php                # Gerenciamento de cache
│   └── Traits/
│       └── Auditable.php                   # Rastreamento de mudancas
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php               # Layout principal
│       ├── financas/
│       │   ├── index.blade.php             # Dashboard principal
│       │   └── transacoes.blade.php        # Listagem de transacoes
│       └── configuracoes/
│           ├── categorias.blade.php        # Gerenciamento de categorias
│           ├── metas.blade.php             # Gerenciamento de metas
│           ├── alertas.blade.php           # Visualizacao de alertas
│           └── logs.blade.php              # Logs de auditoria
├── routes/
│   └── web.php                             # Rotas da aplicacao (31 rotas)
└── docker-compose.yml                      # Configuracao Docker
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
| Comparativo Mensal | Comparacao mes a mes |
| Tendencia Anual | Analise dos ultimos 12 meses |
| Distribuicao por Dia | Gastos por dia da semana |

#### Funcionalidade de Ampliar Graficos

- Clique em qualquer grafico para abrir modal ampliado
- Exibe detalhes adicionais em tabela
- Grafico em tamanho maior para melhor visualizacao

### 6. Dashboard

- **Cards de resumo**: Total receitas, despesas e saldo
- **Saldo do mes atual**: Visualizacao do mes corrente
- **Previsao**: Projecao para o proximo mes
- **Tabelas**: Listagem de receitas, despesas e parcelas

### 7. Categorias

- Criar categorias personalizadas para receitas e despesas
- Definir cores (hex) e icones para cada categoria
- Tipos: receita, despesa ou ambos
- Ativar/desativar categorias

### 8. Metas Financeiras

- **Tipos de metas**:
  - `economia` - Guardar determinado valor
  - `limite_gasto` - Nao ultrapassar limite de gastos
  - `receita` - Atingir meta de receita
- Acompanhamento de progresso automatico
- Status: concluida, vencida, urgente, em andamento
- Calculo de dias restantes

### 9. Alertas Inteligentes

- Geracao automatica de alertas
- **Tipos de alertas**:
  - `vencimento` - Parcelas proximas do vencimento
  - `limite` - Limite de gastos atingido
  - `meta` - Notificacoes sobre metas
  - `info` - Informacoes gerais
- Marcar como lido individualmente ou todos de uma vez

### 10. Logs de Auditoria

- Registro automatico de todas as operacoes (create, update, delete)
- Filtros por modelo, acao e periodo
- Visualizacao de valores antigos e novos
- Limpeza de logs antigos

### 11. Transacoes

- Listagem paginada de todas as receitas e despesas
- Ordenacao por data
- Filtragem e busca

## API/Rotas

**Total: 31 rotas**

### Paginas

| Metodo | Rota | Descricao |
|--------|------|-----------|
| GET | `/` | Dashboard principal |
| GET | `/transacoes` | Listagem de transacoes |
| GET | `/categorias` | Gerenciamento de categorias |
| GET | `/metas` | Gerenciamento de metas |
| GET | `/alertas` | Visualizacao de alertas |
| GET | `/logs` | Logs de auditoria |
| GET | `/logs/{id}` | Detalhes de um log |

### Receitas

| Metodo | Rota | Descricao |
|--------|------|-----------|
| POST | `/receitas` | Criar receita |
| PUT | `/receitas/{id}` | Atualizar receita |
| DELETE | `/receitas/{id}` | Excluir receita |
| PATCH | `/receitas/{id}/toggle` | Ativar/desativar recorrente |

### Despesas

| Metodo | Rota | Descricao |
|--------|------|-----------|
| POST | `/despesas` | Criar despesa |
| POST | `/despesas/multiplas` | Criar multiplas despesas |
| PUT | `/despesas/{id}` | Atualizar despesa |
| DELETE | `/despesas/{id}` | Excluir despesa |
| PATCH | `/despesas/{id}/toggle` | Ativar/desativar recorrente |
| PATCH | `/despesas/{id}/avancar-parcela` | Pagar proxima parcela |
| POST | `/despesas/grupo/{grupoId}/adiantar` | Adiantar multiplas parcelas |
| DELETE | `/despesas/grupo/{grupoId}` | Excluir todas parcelas do grupo |

### Categorias

| Metodo | Rota | Descricao |
|--------|------|-----------|
| POST | `/categorias` | Criar categoria |
| PUT | `/categorias/{id}` | Atualizar categoria |
| DELETE | `/categorias/{id}` | Excluir categoria |
| PATCH | `/categorias/{id}/toggle` | Ativar/desativar categoria |

### Metas

| Metodo | Rota | Descricao |
|--------|------|-----------|
| POST | `/metas` | Criar meta |
| PUT | `/metas/{id}` | Atualizar meta |
| DELETE | `/metas/{id}` | Excluir meta |

### Alertas

| Metodo | Rota | Descricao |
|--------|------|-----------|
| PATCH | `/alertas/{id}/lido` | Marcar como lido |
| POST | `/alertas/marcar-todos-lidos` | Marcar todos como lidos |
| DELETE | `/alertas/{id}` | Excluir alerta |

### Logs

| Metodo | Rota | Descricao |
|--------|------|-----------|
| POST | `/logs/limpar` | Limpar logs antigos |

### APIs (JSON)

| Metodo | Rota | Descricao |
|--------|------|-----------|
| GET | `/api/alertas` | Retorna alertas nao lidos |
| GET | `/api/categorias` | Retorna categorias ativas |

## Models

**Total: 6 models** (MongoDB)

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
- origem_recorrente_id: ObjectId|null

// Scopes: Recorrentes(), NaoRecorrentes()
// Auditavel: Sim
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

// Scopes: Recorrentes(), Parceladas()
// Accessor: descricaoCompleta (mostra numero da parcela)
// Auditavel: Sim
```

### Categoria

```php
// Campos
- _id: ObjectId (MongoDB)
- nome: string
- cor: string (hex)
- icone: string
- tipo: string (receita, despesa, ambos)
- ativo: boolean

// Scopes: Ativas(), ParaReceitas(), ParaDespesas()
// Auditavel: Sim
```

### Meta

```php
// Campos
- _id: ObjectId (MongoDB)
- titulo: string
- descricao: string|null
- valor_alvo: float
- valor_atual: float
- data_inicio: date
- data_fim: date
- categoria: string|null
- tipo: string (economia, limite_gasto, receita)
- ativo: boolean

// Accessors: progresso, dias_restantes, status
// Auditavel: Sim
```

### Alerta

```php
// Campos
- _id: ObjectId (MongoDB)
- titulo: string
- mensagem: string
- tipo: string (vencimento, limite, meta, info)
- data_alerta: date
- referencia_tipo: string|null (despesa, receita, meta)
- referencia_id: ObjectId|null
- lido: boolean
- ativo: boolean

// Scopes: NaoLidos(), Ativos()
// Auditavel: Nao
```

### AuditLog

```php
// Campos
- _id: ObjectId (MongoDB)
- model_type: string
- model_id: ObjectId
- action: string (create, update, delete)
- old_values: object|null
- new_values: object|null
- user_ip: string|null
- user_agent: string|null
- created_at: datetime

// Metodos estaticos: logCreate(), logUpdate(), logDelete()
// Accessors: action_label, model_name, badge_color
// Scopes: forModel(), byAction()
```

## Controllers

### FinancaController

Gerencia receitas e despesas.

| Metodo | Descricao |
|--------|-----------|
| `index()` | Carrega dashboard com todos os dados |
| `transacoes()` | Lista paginada de transacoes |
| `storeReceita()` | Cria nova receita |
| `updateReceita()` | Atualiza receita existente |
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

### ConfiguracaoController

Gerencia categorias, metas, alertas e logs de auditoria.

#### Categorias

| Metodo | Descricao |
|--------|-----------|
| `categorias()` | Lista todas as categorias |
| `storeCategoria()` | Cria nova categoria |
| `updateCategoria()` | Atualiza categoria existente |
| `destroyCategoria()` | Remove categoria |
| `toggleCategoria()` | Ativa/desativa categoria |

#### Metas

| Metodo | Descricao |
|--------|-----------|
| `metas()` | Lista metas com calculo de progresso |
| `storeMeta()` | Cria nova meta |
| `updateMeta()` | Atualiza meta existente |
| `destroyMeta()` | Remove meta |
| `atualizarValorMeta()` | Recalcula valor atual da meta |

#### Alertas

| Metodo | Descricao |
|--------|-----------|
| `alertas()` | Lista alertas com geracao automatica |
| `gerarAlertasAutomaticos()` | Cria alertas inteligentes |
| `marcarAlertaLido()` | Marca alerta como lido |
| `marcarTodosAlertasLidos()` | Marca todos como lidos |
| `destroyAlerta()` | Remove alerta |

#### Logs de Auditoria

| Metodo | Descricao |
|--------|-----------|
| `logs()` | Lista logs com filtros e paginacao |
| `showLog()` | Exibe detalhes de um log |
| `limparLogs()` | Remove logs antigos |

#### APIs

| Metodo | Descricao |
|--------|-----------|
| `getAlertasNaoLidos()` | Retorna alertas nao lidos (JSON) |
| `getCategoriasJson()` | Retorna categorias ativas (JSON) |

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

**Total: 7 views Blade**

### Layout (app.blade.php)

- Navbar com botoes para adicionar receita/despesa
- Menu de navegacao para todas as paginas
- Tema escuro com cores neon (#00ff88, #ff4757, #3742fa)
- Animacoes GSAP para interatividade
- Bootstrap 5 + Bootstrap Icons

### Financas

#### Index (index.blade.php)

Secoes:

1. **Cards de resumo** - Totais e previsoes
2. **Graficos** - Multiplos graficos em grid + projecao
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

#### Transacoes (transacoes.blade.php)

- Listagem paginada de receitas e despesas
- Ordenacao por data
- Filtros de busca

### Configuracoes

#### Categorias (categorias.blade.php)

- Listagem de categorias
- Formulario para criar/editar
- Toggle para ativar/desativar

#### Metas (metas.blade.php)

- Listagem de metas com progresso
- Formulario para criar/editar
- Indicadores visuais de status

#### Alertas (alertas.blade.php)

- Listagem de alertas
- Opcao para marcar como lido
- Geracao automatica de alertas

#### Logs (logs.blade.php)

- Listagem de logs de auditoria
- Filtros por modelo, acao e periodo
- Visualizacao de detalhes

---

## Servicos e Traits

### CacheService

Gerenciamento de cache para otimizacao de performance.

```php
// Metodos
- cacheReceitas()
- cacheDespesas()
- invalidateCache()
```

### Auditable Trait

Rastreamento automatico de mudancas em modelos.

```php
// Funcionalidades
- Registra automaticamente create, update, delete
- Armazena valores antigos e novos
- Captura IP e User-Agent
```

---

## Contribuicao

1. Crie uma branch para sua feature
2. Faca commit das mudancas
3. Atualize o CHANGELOG.md
4. Abra um Pull Request

## Licenca

Projeto privado - Todos os direitos reservados.
