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
9. [Services](#services)
10. [Views](#views)
11. [Servicos Transversais (Cache, Auditoria, Notificacoes)](#servicos-transversais-cache-auditoria-notificacoes)

---

## Visao Geral

O Controle Financeiro e uma aplicacao web para gerenciamento de financas pessoais que permite:

- Registrar receitas e despesas (simples, parceladas e recorrentes)
- Controlar dividas e planos de quitacao
- Gerenciar cartoes de credito e faturas
- Importar extratos bancarios (CSV)
- Exportar relatorios em PDF/CSV e fazer backup/restauracao dos dados
- Acompanhar fluxo de caixa mensal com relatorio dedicado
- Gerenciar orcamento mensal por categoria
- Visualizar insights automaticos sobre variacao de gastos
- Acompanhar gamificacao (streak de lancamentos)
- Gerenciar categorias personalizadas, metas financeiras e reserva de emergencia
- Receber alertas inteligentes (com notificacao por e-mail)
- Auditar todas as operacoes do sistema

## Tecnologias

| Tecnologia | Versao | Descricao |
|------------|--------|-----------|
| PHP | 8.2+ | Linguagem backend |
| Laravel | 12.x | Framework PHP |
| MongoDB | 7.0 | Banco de dados NoSQL (via `mongodb/laravel-mongodb`) |
| barryvdh/laravel-dompdf | 3.x | Geracao de relatorios em PDF |
| Vite + Tailwind CSS 4 | - | Build de assets e estilizacao |
| Bootstrap | 5.3 | Framework CSS (telas legadas) |
| Chart.js | 4.x | Biblioteca de graficos |
| GSAP | 3.x | Animacoes JavaScript |
| Docker | - | Containerizacao |

### Containers Docker

- `financas_php` - Aplicacao PHP/Laravel
- `financas_nginx` - Servidor web (porta 8080, com labels Traefik para `financeiro.local`)
- `financas_mongodb` - Banco de dados (porta 27017)
- `financas_mongo_express` - Interface web MongoDB (porta 8081)

> O `nginx` esta configurado com labels do Traefik (`traefik-net`, rede externa) para ser roteado via `financeiro.local`, alem de continuar exposto localmente na porta 8080.

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

# Instalar dependencias PHP
docker exec -it financas_php composer install

# Copiar arquivo de ambiente
cp .env.example .env

# Gerar chave da aplicacao
docker exec -it financas_php php artisan key:generate

# Instalar dependencias JS e compilar assets (Vite + Tailwind)
docker exec -it financas_php npm install
docker exec -it financas_php npm run build

# Acessar aplicacao
# http://localhost:8080

# Acessar MongoDB Express (interface web)
# http://localhost:8081
```

> Alternativamente, `composer run setup` executa install, copia do `.env`, `key:generate`, migrate e build do frontend em sequencia.

## Estrutura do Projeto

```
ControleFinanceiro/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── OptimizeAssets.php          # Comando artisan para otimizar assets
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── FinancaController.php       # Dashboard, receitas e despesas
│   │   │   ├── ConfiguracaoController.php  # Categorias, Metas, Alertas, Logs
│   │   │   ├── DividaController.php        # Dividas e pagamentos
│   │   │   ├── CartaoController.php        # Cartoes de credito
│   │   │   ├── ImportacaoController.php    # Importacao de extrato CSV
│   │   │   └── ExportController.php        # PDF, CSV, backup e fluxo de caixa
│   │   └── Middleware/
│   │       └── CompressResponse.php        # Compressao das respostas HTTP
│   ├── Models/
│   │   ├── Receita.php                     # Model de receitas
│   │   ├── Despesa.php                     # Model de despesas
│   │   ├── Categoria.php                   # Model de categorias
│   │   ├── Meta.php                        # Model de metas
│   │   ├── Alerta.php                      # Model de alertas
│   │   ├── AuditLog.php                    # Model de logs de auditoria
│   │   ├── Divida.php                      # Model de dividas
│   │   ├── Cartao.php                      # Model de cartoes de credito
│   │   └── User.php                        # Usuario (autenticacao)
│   ├── Notifications/
│   │   └── AlertaCriado.php                # Notificacao por e-mail ao criar alerta
│   ├── Services/
│   │   ├── CacheService.php                # Cache de receitas/despesas
│   │   ├── BackupService.php               # Exportacao/importacao de backup JSON
│   │   ├── FluxoCaixaService.php           # Calculo do fluxo de caixa mensal
│   │   ├── OrcamentoService.php            # Progresso de orcamento por categoria
│   │   ├── InsightsService.php             # Insights automaticos de gastos
│   │   ├── GamificacaoService.php          # Streak de lancamentos
│   │   ├── AssinaturasService.php          # Listagem de assinaturas/recorrentes
│   │   ├── FaturaService.php               # Calculo de fatura de cartao
│   │   ├── QuitacaoDividasService.php      # Plano de quitacao de dividas
│   │   ├── ReservaEmergenciaService.php    # Calculo de reserva de emergencia
│   │   └── ImportacaoCsvService.php        # Parser/import de extrato CSV
│   └── Traits/
│       └── Auditable.php                   # Rastreamento de mudancas
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php               # Layout principal (sidebar + bottom nav)
│       ├── financas/
│       │   ├── index.blade.php             # Dashboard principal
│       │   └── transacoes.blade.php        # Listagem de transacoes
│       ├── configuracoes/
│       │   ├── categorias.blade.php        # Gerenciamento de categorias
│       │   ├── metas.blade.php             # Gerenciamento de metas
│       │   ├── alertas.blade.php           # Visualizacao de alertas
│       │   └── logs.blade.php              # Logs de auditoria
│       ├── dividas/
│       │   └── index.blade.php             # Gerenciamento de dividas
│       ├── cartoes/
│       │   └── index.blade.php             # Gerenciamento de cartoes
│       ├── importacao/
│       │   ├── index.blade.php             # Upload de extrato CSV
│       │   └── preview.blade.php           # Preview antes de confirmar import
│       ├── relatorios/
│       │   └── fluxo-caixa.blade.php       # Relatorio de fluxo de caixa mensal
│       ├── exports/
│       │   ├── relatorio-pdf.blade.php     # Template PDF do relatorio mensal
│       │   ├── extrato-pdf.blade.php       # Template PDF do extrato
│       │   ├── fluxo-caixa-pdf.blade.php   # Template PDF do fluxo de caixa
│       │   └── backup.blade.php            # Pagina de backup/restauracao
│       └── partials/
│           └── _categoria-pills.blade.php  # Componente reutilizavel de categorias
├── routes/
│   └── web.php                             # Rotas da aplicacao (~65 rotas)
└── docker-compose.yml                      # Configuracao Docker (com labels Traefik)
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
- Pode ser associada a um cartao de credito (`cartao_id`)

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

### 5. Cartoes de Credito

- Cadastro de cartoes com dia de fechamento, dia de vencimento e limite
- Calculo automatico da fatura atual (`FaturaService`) a partir das despesas vinculadas
- Ativar/desativar e excluir cartoes

### 6. Dividas

- Cadastro de dividas com credor, valor total, datas de inicio/vencimento, categoria e taxa de juros mensal
- Registro de pagamentos parciais (embutidos no documento da divida)
- Status automatico: `ativa`, `em_atraso`, `quitada`
- Acompanhamento de valor pago, valor restante e percentual pago
- Plano de quitacao sugerido (`QuitacaoDividasService`)

### 7. Importacao de Extrato (CSV)

- Upload de arquivo CSV com preview antes da confirmacao
- Deteccao/parse de linhas via `ImportacaoCsvService`
- Confirmacao cria as receitas/despesas em lote; opcao de cancelar a importacao

### 8. Exportacao e Backup

- **PDF**: relatorio mensal, extrato completo e relatorio de fluxo de caixa
- **CSV**: receitas, despesas, transacoes e fluxo de caixa
- **Backup**: exportacao de todos os dados em JSON e importacao/restauracao a partir de um arquivo de backup

### 9. Relatorio de Fluxo de Caixa Mensal

- Pagina propria (`/relatorios/fluxo-caixa`) com visao mes a mes de um ano
- Exportavel em PDF e CSV
- Calculado pelo `FluxoCaixaService`

### 10. Orcamento por Categoria

- Definicao de `orcamento_mensal` em cada categoria
- Progresso calculado pelo `OrcamentoService`, com status `ok` (< 80%), `atencao` (80%-100%) e `excedido` (>= 100%)

### 11. Insights Automaticos

- `InsightsService` compara o gasto do mes atual por categoria com a media dos meses anteriores
- Gera mensagens automaticas quando a variacao e relevante (limiar de 15%)

### 12. Gamificacao

- `GamificacaoService` calcula o streak (dias consecutivos com lancamentos), com 1 dia de tolerancia

### 13. Reserva de Emergencia

- `ReservaEmergenciaService` calcula o gasto mensal medio e a faixa de reserva de emergencia recomendada

### 14. Assinaturas/Recorrentes

- `AssinaturasService` lista receitas/despesas recorrentes ativas e o total mensal comprometido

### 15. Graficos e Visualizacoes

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

### 16. Dashboard

- **Cards de resumo**: Total receitas, despesas e saldo
- **Saldo do mes atual**: Visualizacao do mes corrente
- **Previsao**: Projecao para o proximo mes
- **Insights**: Cartoes com variacoes relevantes de gastos por categoria
- **Progresso de orcamento**: Barra de progresso por categoria com orcamento definido
- **Streak de gamificacao**: Indicador de dias consecutivos com lancamentos
- **Tabelas**: Listagem de receitas, despesas e parcelas

### 17. Categorias

- Criar categorias personalizadas para receitas e despesas
- Definir cores (hex), icones e orcamento mensal para cada categoria
- Tipos: receita, despesa ou ambos
- Ativar/desativar categorias

### 18. Metas Financeiras

- **Tipos de metas**:
  - `economia` - Guardar determinado valor
  - `limite_gasto` - Nao ultrapassar limite de gastos
  - `receita` - Atingir meta de receita
- Contribuicoes manuais (aportes) por meta, com adicao/remocao individual
- Acompanhamento de progresso automatico (valor atual + soma de contribuicoes)
- Status: concluida, vencida, urgente, em andamento
- Calculo de dias restantes

### 19. Alertas Inteligentes

- Geracao automatica de alertas
- **Criacao manual** de alertas personalizados
- **Notificacao por e-mail** ao criar um alerta (quando `services.notificacoes.email` configurado)
- **Tipos de alertas** (cada um com modal especifico):
  - `lembrete` - Alertas personalizados criados pelo usuario
  - `vencimento` - Parcelas proximas do vencimento (com valor e data)
  - `limite` - Limite de gastos atingido (com valor limite e atual)
  - `meta` - Notificacoes sobre metas (com valor alvo e prazo)
  - `info` - Informacoes gerais
- Marcar como lido individualmente ou todos de uma vez

### 20. Logs de Auditoria

- Registro automatico de todas as operacoes (create, update, delete)
- Filtros por modelo, acao e periodo
- Visualizacao de valores antigos e novos
- Limpeza de logs antigos

### 21. Transacoes

- Listagem paginada de todas as receitas e despesas
- Ordenacao por data
- Filtragem e busca

## API/Rotas

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
| GET | `/dividas` | Gerenciamento de dividas |
| GET | `/cartoes` | Gerenciamento de cartoes |
| GET | `/importar` | Pagina de importacao de extrato |
| GET | `/relatorios/fluxo-caixa` | Relatorio de fluxo de caixa mensal |
| GET | `/backup` | Pagina de backup/restauracao |

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
| POST | `/metas/{id}/contribuicoes` | Adicionar contribuicao (aporte) |
| DELETE | `/metas/{id}/contribuicoes/{contribId}` | Remover contribuicao |

### Alertas

| Metodo | Rota | Descricao |
|--------|------|-----------|
| POST | `/alertas` | Criar alerta manualmente |
| PATCH | `/alertas/{id}/lido` | Marcar como lido |
| POST | `/alertas/marcar-todos-lidos` | Marcar todos como lidos |
| DELETE | `/alertas/{id}` | Excluir alerta |

### Logs

| Metodo | Rota | Descricao |
|--------|------|-----------|
| POST | `/logs/limpar` | Limpar logs antigos |

### Dividas

| Metodo | Rota | Descricao |
|--------|------|-----------|
| POST | `/dividas` | Criar divida |
| PUT | `/dividas/{id}` | Atualizar divida |
| DELETE | `/dividas/{id}` | Excluir divida |
| POST | `/dividas/{id}/pagamentos` | Registrar pagamento |
| DELETE | `/dividas/{id}/pagamentos/{pagamentoId}` | Remover pagamento |

### Cartoes

| Metodo | Rota | Descricao |
|--------|------|-----------|
| POST | `/cartoes` | Criar cartao |
| PUT | `/cartoes/{id}` | Atualizar cartao |
| PATCH | `/cartoes/{id}/toggle` | Ativar/desativar cartao |
| DELETE | `/cartoes/{id}` | Excluir cartao |

### Importacao de Extrato (CSV)

| Metodo | Rota | Descricao |
|--------|------|-----------|
| POST | `/importar/preview` | Gerar preview do arquivo CSV |
| POST | `/importar/confirmar` | Confirmar e criar os lancamentos |
| POST | `/importar/cancelar` | Cancelar a importacao pendente |

### Exportacao PDF

| Metodo | Rota | Descricao |
|--------|------|-----------|
| GET | `/exportar/pdf/relatorio` | Relatorio mensal em PDF |
| GET | `/exportar/pdf/extrato` | Extrato completo em PDF |
| GET | `/exportar/pdf/fluxo-caixa` | Fluxo de caixa mensal em PDF |

### Exportacao CSV

| Metodo | Rota | Descricao |
|--------|------|-----------|
| GET | `/exportar/csv/receitas` | Receitas em CSV |
| GET | `/exportar/csv/despesas` | Despesas em CSV |
| GET | `/exportar/csv/transacoes` | Transacoes em CSV |
| GET | `/exportar/csv/fluxo-caixa` | Fluxo de caixa mensal em CSV |

### Backup e Restauracao

| Metodo | Rota | Descricao |
|--------|------|-----------|
| GET | `/backup/exportar` | Exportar backup em JSON |
| POST | `/backup/importar` | Importar/restaurar backup |

### APIs (JSON)

| Metodo | Rota | Descricao |
|--------|------|-----------|
| GET | `/api/alertas` | Retorna alertas nao lidos |
| GET | `/api/categorias` | Retorna categorias ativas |

## Models

**Total: 9 models** (MongoDB, exceto `User` que usa autenticacao padrao Laravel)

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
- cartao_id: string|null (referencia ao Cartao)
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
- orcamento_mensal: float|null

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
- contribuicoes: array (aportes manuais embutidos: valor, data, etc.)

// Accessors: somaContribuicoes, valorTotal, progresso, diasRestantes, status
// Auditavel: Sim
```

### Alerta

```php
// Campos
- _id: ObjectId (MongoDB)
- titulo: string
- mensagem: string
- tipo: string (vencimento, limite, meta, lembrete, info)
- data_alerta: date
- referencia_tipo: string|null (despesa, receita, meta)
- referencia_id: ObjectId|null
- lido: boolean
- ativo: boolean

// Scopes: NaoLidos(), Ativos()
// Ao criar: dispara notificacao AlertaCriado por e-mail (se configurado)
// Auditavel: Nao
```

### Divida

```php
// Campos
- _id: ObjectId (MongoDB)
- descricao: string
- credor: string
- valor_total: float
- data_inicio: date
- data_vencimento: date
- categoria: string|null
- status: string (ativa, em_atraso, quitada)
- observacoes: string|null
- pagamentos: array (pagamentos embutidos: valor, data, etc.)
- taxa_juros_mensal: float|null

// Accessors: valorPago, valorRestante, percentualPago
// Scopes: Ativas(), EmAtraso(), Quitadas()
// Auditavel: Sim
```

### Cartao

```php
// Campos
- _id: ObjectId (MongoDB)
- nome: string
- dia_fechamento: integer
- dia_vencimento: integer
- limite: float
- ativo: boolean

// Scopes: Ativos()
// Auditavel: Sim
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

### User

Model de autenticacao padrao do Laravel (`name`, `email`, `password`), usado para acesso a aplicacao.

## Controllers

### FinancaController

Gerencia o dashboard, receitas e despesas.

| Metodo | Descricao |
|--------|-----------|
| `index()` | Carrega dashboard com dados, insights, orcamento e streak de gamificacao |
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

Gerencia categorias, metas (e contribuicoes), alertas e logs de auditoria.

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
| `storeContribuicao()` | Adiciona uma contribuicao (aporte) manual a meta |
| `destroyContribuicao()` | Remove uma contribuicao |

#### Alertas

| Metodo | Descricao |
|--------|-----------|
| `alertas()` | Lista alertas com geracao automatica |
| `storeAlerta()` | Cria alerta manualmente (lembrete, vencimento, limite, meta) |
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

### DividaController

| Metodo | Descricao |
|--------|-----------|
| `index()` | Lista dividas com plano de quitacao sugerido |
| `store()` | Cria nova divida |
| `update()` | Atualiza divida existente |
| `destroy()` | Remove divida |
| `storePagamento()` | Registra pagamento em uma divida |
| `destroyPagamento()` | Remove um pagamento registrado |

### CartaoController

| Metodo | Descricao |
|--------|-----------|
| `index()` | Lista cartoes com fatura atual calculada |
| `store()` | Cria novo cartao |
| `update()` | Atualiza cartao existente |
| `toggle()` | Ativa/desativa cartao |
| `destroy()` | Remove cartao |

### ImportacaoController

| Metodo | Descricao |
|--------|-----------|
| `index()` | Exibe pagina de upload do extrato |
| `preview()` | Faz o parse do CSV e exibe preview antes de confirmar |
| `confirmar()` | Cria as receitas/despesas a partir do preview confirmado |
| `cancelar()` | Descarta a importacao pendente |

### ExportController

| Metodo | Descricao |
|--------|-----------|
| `relatorioPdf()` | Gera relatorio mensal em PDF |
| `extratoPdf()` | Gera extrato completo em PDF |
| `fluxoCaixaIndex()` | Exibe pagina do relatorio de fluxo de caixa mensal |
| `fluxoCaixaPdf()` | Gera fluxo de caixa mensal (ano) em PDF |
| `fluxoCaixaCsv()` | Gera fluxo de caixa mensal (ano) em CSV |
| `receitasCsv()` | Exporta receitas em CSV |
| `despesasCsv()` | Exporta despesas em CSV |
| `transacoesCsv()` | Exporta transacoes em CSV |
| `backupIndex()` | Exibe pagina de backup |
| `exportarBackup()` | Gera arquivo de backup em JSON |
| `importarBackup()` | Restaura dados a partir de um backup |

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

## Services

Camada de servicos que concentra regras de negocio reutilizadas pelos controllers.

| Service | Responsabilidade |
|---------|-------------------|
| `CacheService` | Cacheia e invalida listas de receitas/despesas para reduzir consultas ao MongoDB |
| `BackupService` | Serializa todos os dados da aplicacao em JSON e restaura a partir de um backup |
| `FluxoCaixaService` | Calcula entradas, saidas e saldo mes a mes para o relatorio de fluxo de caixa |
| `OrcamentoService` | Calcula progresso (`calcularProgresso`) e status (`ok`/`atencao`/`excedido`) do orcamento mensal por categoria |
| `InsightsService` | Compara o gasto atual por categoria com a media dos meses anteriores e gera mensagens de insight (`gerarInsight`, limiar de 15% de variacao) |
| `GamificacaoService` | Calcula o streak de dias consecutivos com lancamentos (`calcularStreak`, 1 dia de tolerancia) |
| `AssinaturasService` | Lista receitas/despesas recorrentes ativas (`listar`) e soma o compromisso mensal (`totalMensal`) |
| `FaturaService` | Calcula a fatura atual de um cartao de credito (`faturaAtual`) com base nas despesas vinculadas e no dia de fechamento |
| `QuitacaoDividasService` | Monta um plano de quitacao sugerido para as dividas ativas (`planoAtual`) |
| `ReservaEmergenciaService` | Calcula o gasto mensal medio (`gastoMensalMedio`) e a faixa recomendada de reserva de emergencia (`faixaAtual`) |
| `ImportacaoCsvService` | Faz o parse de um extrato CSV e importa as linhas confirmadas como receitas/despesas (`importar`) |

## Views

### Layout (app.blade.php)

- Sidebar de navegacao (colapsavel) + bottom nav para mobile
- Menu de navegacao para todas as paginas (Dashboard, Transacoes, Dividas, Cartoes, Fluxo de Caixa, Categorias, Metas, Alertas, Logs, Backup, Importacao)
- Tema escuro com cores neon (#00ff88, #ff4757, #3742fa)
- Animacoes GSAP para interatividade
- Bootstrap 5 + Bootstrap Icons, com Tailwind CSS 4 via Vite para novas telas

### Financas

- **Index (`financas/index.blade.php`)**: cards de resumo, insights, progresso de orcamento, streak de gamificacao, graficos, despesas parceladas, receitas/despesas recorrentes e listagens completas
- **Transacoes (`financas/transacoes.blade.php`)**: listagem paginada com filtros e busca

### Configuracoes

- **Categorias**: listagem, formulario de criacao/edicao (incluindo orcamento mensal) e toggle de ativacao
- **Metas**: listagem com progresso, contribuicoes/aportes e indicadores visuais de status
- **Alertas**: listagem, marcar como lido e geracao automatica
- **Logs**: listagem de auditoria com filtros por modelo, acao e periodo

### Dividas (`dividas/index.blade.php`)

- Listagem de dividas com status, valor pago/restante e percentual
- Registro e remocao de pagamentos
- Exibicao do plano de quitacao sugerido

### Cartoes (`cartoes/index.blade.php`)

- Listagem de cartoes com fatura atual calculada
- Formulario de criacao/edicao (limite, dia de fechamento/vencimento)
- Toggle de ativacao

### Importacao (`importacao/index.blade.php`, `importacao/preview.blade.php`)

- Upload do arquivo CSV do extrato
- Tela de preview com confirmacao ou cancelamento antes de criar os lancamentos

### Relatorios (`relatorios/fluxo-caixa.blade.php`)

- Visao anual do fluxo de caixa mensal, com exportacao em PDF/CSV

### Exports (`exports/*.blade.php`)

- Templates dedicados para os PDFs (relatorio mensal, extrato, fluxo de caixa) e para a pagina de backup/restauracao

---

## Servicos Transversais (Cache, Auditoria, Notificacoes)

### CacheService

Cache de listas de receitas e despesas para reduzir consultas repetidas ao MongoDB, com invalidacao automatica nas operacoes de escrita.

### Auditable Trait

Rastreamento automatico de mudancas nos models que o utilizam (Receita, Despesa, Categoria, Meta, Divida, Cartao).

```php
// Funcionalidades
- Registra automaticamente create, update, delete via AuditLog
- Armazena valores antigos e novos
- Captura IP e User-Agent
```

### Notificacao de Alertas

Ao criar um `Alerta`, se `services.notificacoes.email` estiver configurado no `.env`/`config/services.php`, a notificacao `App\Notifications\AlertaCriado` e disparada por e-mail via `Notification::route('mail', ...)`.

---

## Contribuicao

1. Crie uma branch para sua feature
2. Faca commit das mudancas
3. Atualize o CHANGELOG.md
4. Abra um Pull Request

## Licenca

Projeto privado - Todos os direitos reservados.
