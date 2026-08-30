# 💰 GFP — Gestão Financeira Pessoal

Sistema web completo para controle e gestão financeira pessoal desenvolvido em **PHP (Laravel 12)** e **SQLite**, com foco em usabilidade, segurança rigorosa e controle total de despesas avulsas e recorrentes.

---

## 🚀 Tecnologias Utilizadas

* **Framework**: [Laravel 12](https://laravel.com)
* **Linguagem**: PHP 8.2+ (Testado no PHP 8.3)
* **Banco de Dados**: SQLite
* **Autenticação & Interface**: Laravel Breeze (Blade, Tailwind CSS e Alpine.js)
* **Serviço de E-mails em Desenvolvimento**: Mailpit (SMTP `127.0.0.1:1025`)
* **Testes Automatizados**: PHPUnit (89 testes com 100% de aprovação)

---

## 🌟 Principais Funcionalidades

### 1. 🔒 Autenticação e Segurança (US1)
* **Auto-Cadastro**: Registro com ativação obrigatória de conta via link assinado enviado por e-mail.
* **Política de Senha Forte**: Mínimo de 8 caracteres contendo letras maiúsculas, minúsculas, números e caracteres especiais, criptografadas com `bcrypt`.
* **Proteção Anti-Força Bruta**: Bloqueio temporário da conta por 15 minutos após 5 erros consecutivos de senha.
* **Recuperação de Senha**: Fluxo seguro de redefinição de credenciais via token de uso único.
* **Proteção CSRF**: Validação em todos os formulários e término seguro de sessão com invalidação de cookies.

### 2. 👥 Painel Administrativo de Usuários (US2)
* **Gestão Centralizada de Contas**: Listagem de todos os usuários cadastrados com busca textual e filtros por status (Ativos, Não Verificados, Bloqueados, Administradores).
* **Bloqueio e Desbloqueio**: Capacidade de bloquear acessos (invalida instantaneamente a sessão ativa do usuário).
* **Exclusão com Confirmação**: Remoção definitiva de contas com confirmação explícita.
* **Regras de Proteção**:
  * O administrador não pode se auto-bloquear ou se auto-excluir.
  * O sistema impede o bloqueio ou exclusão do último administrador ativo.
  * **Isolamento**: Administradores não têm acesso aos dados financeiros dos usuários comuns.

### 3. 🏷️ Gerenciamento de Categorias (US7)
* CRUD completo de categorias de despesas exclusivas para cada usuário.
* Unicidade de nome no escopo de cada usuário (dois usuários diferentes podem ter uma categoria "Alimentação" isoladamente).
* **Categoria Padrão**: Criação automática da categoria **"Outros"** no momento do cadastro de qualquer usuário.
* **Proteção de Integridade**: Bloqueio de exclusão de qualquer categoria vinculada a lançamentos financeiros existentes.

### 4. 🔹 Gastos Não Recorrentes (US3)
* Cadastro de despesas pontuais com descrição, categoria, valor em Real (BRL), data e observações.
* **Cálculo Automático de Status**:
  * 🟢 **Pago**: Marcado como liquidado.
  * 🟡 **Pendente**: Não pago com data igual ou posterior a hoje.
  * 🔴 **Atrasado**: Não pago com data anterior a hoje.
* Edição e exclusão de lançamentos com confirmação.

### 5. 🔄 Gastos Recorrentes e Documentos de Cobrança (US4)
* Cadastro de modelos de despesas periódicas suportando:
  * **Semanal**
  * **Mensal** (com definição do dia do mês de 1 a 31)
  * **Anual**
  * **Personalizada** (intervalo configurável em X dias)
* **Upload de Documento de Cobrança**: Permite anexar boletos e contas padrão (PDF, JPG, PNG até 10MB) com substituição automática e download seguro autenticado.
* Ação rápida para pausar ou reativar modelos recorrentes.

### 6. 🧾 Ocorrências de Pagamento de Recorrentes (US5)
* Geração automática de ciclos de faturas e vencimentos futuros baseados na periodicidade do modelo.
* **Registro de Pagamento**: Permite informar o valor real efetivamente pago (suporta juros/descontos) e a data do pagamento.
* **Comprovante de Pagamento**: Upload de comprovante bancário (PDF, JPG, PNG até 10MB) para cada ocorrência paga.
* Histórico de vencimentos detalhado na tela do modelo recorrente com suporte a desmarcar pagamento (`unpay`).

### 7. 📊 Listagem Consolidada e Painel Financeiro (US6)
* **Visão Unificada**: Listagem consolidada de todas as despesas avulsas (🔹) e ocorrências recorrentes (🔄).
* **Filtros Combinados**:
  * Tipo de Gasto: Todos (Consolidado), 🔹 Avulsos, 🔄 Recorrentes
  * Período (Data Inicial e Data Final)
  * Categoria
  * Status (Pago, Pendente, Atrasado)
  * Busca por termo
* **Totalizadores em Tempo Real**: Total Consolidado, Total Pago, Total Pendente e Total Atrasado.
* **Dashboard Interativo**: Visão mensal com métricas consolidadas, últimos lançamentos e distribuição gráfica percentual de despesas por categoria.

### 8. 🔔 Lembretes de Vencimento por E-mail (US8)
* Comando artisan `expenses:send-reminders --days=2` para envio automatizado de alertas de vencimento (vencendo hoje e 2 dias antes).
* **Agrupamento sem Spam**: Envia um único e-mail consolidado por usuário com todas as contas a vencer.
* Ignora contas já pagas e usuários não verificados ou bloqueados.
* Agendamento diário configurado para execução automática às 08:00.

---

## 🔐 Isolamento e Segurança de Dados (FR-024)

O sistema implementa regras estritas de isolamento (prevenção contra **IDOR**):
* Nenhum usuário consegue visualizar, editar, excluir ou baixar despesas, categorias, ocorrências ou arquivos anexados de outros usuários.
* Todas as consultas e manipulações de anexos validam rigorosamente a propriedade do recurso antes de executar qualquer ação.

---

## 🛠️ Instalação e Execução Local

### Pré-requisitos
* PHP 8.2 ou superior (com extensões `pdo_sqlite`, `fileinfo`, `mbstring`, `openssl`, `curl`)
* Composer
* Node.js e NPM
* [Mailpit](https://github.com/axllent/mailpit) (para envio e visualização de e-mails em desenvolvimento)

### 1. Clonar o repositório e instalar dependências
```bash
git clone <url-do-repositorio>
cd gfp

# Instalar dependências PHP
composer install

# Instalar dependências de frontend
npm install
npm run build
```

### 2. Configurar o ambiente (.env)
Copie o arquivo de exemplo caso não exista:
```bash
cp .env.example .env
php artisan key:generate
```

Certifique-se de que as configurações de banco e e-mail no `.env` correspondam a:
```env
DB_CONNECTION=sqlite

MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="nao-responda@gfp.local"
MAIL_FROM_NAME="GFP - Gestão Financeira Pessoal"
```

### 3. Criar o banco e rodar migrations com seeds
```bash
# Cria o arquivo SQLite se não existir e aplica as migrações
touch database/database.sqlite
php artisan migrate --seed
```

### 4. Iniciar a aplicação
```bash
# Iniciar o servidor web
php artisan serve
```

Acesse no navegador: **[http://localhost:8000](http://localhost:8000)** (ou via Laragon em `http://localhost/gfp/public`).

---

## 👤 Credenciais Padrão de Acesso

### Administrador
* **E-mail**: `admin@gfp.local`
* **Senha**: `Admin@123456`
* **Painel de Gestão**: Acesso liberado ao menu **👥 Gestão de Usuários** (`/admin/users`).

### Novo Usuário Comum
* Acesse a tela de **Cadastro** (`/register`) e crie uma conta.
* Acesse o [Mailpit](http://localhost:8025) para clicar no link de confirmação e ativar a conta.

---

## 🧪 Testes Automatizados (PHPUnit)

O sistema possui uma suite abrangente com **89 testes automatizados** cobrindo todos os fluxos críticos de negócio:

```bash
# Executar todos os testes de feature
php vendor/phpunit/phpunit/phpunit tests/Feature/
```

### Cobertura de Testes:
* `tests/Feature/Auth/*`: Cadastro, senha forte, ativação de e-mail, rate limiting e redefinição de senha.
* `tests/Feature/Admin/UserManagementTest.php`: Permissões de admin, bloqueio, exclusão e proteções do último admin.
* `tests/Feature/CategoryTest.php`: CRUD, unicidade de categoria por usuário, categoria padrão "Outros" e proteção contra exclusão em uso.
* `tests/Feature/ExpenseTest.php`: Gastos não recorrentes, cálculo de status, normalização de moeda BRL e isolamento IDOR.
* `tests/Feature/RecurringExpenseTest.php`: Gastos recorrentes, periodicidades, validação de arquivos 10MB (PDF/JPG/PNG) e substituição de anexos.
* `tests/Feature/RecurringExpenseOccurrenceTest.php`: Ocorrências de vencimento, pagamento com comprovante, cancelamento de pagamento e segurança.
* `tests/Feature/ConsolidatedExpenseTest.php`: Visão consolidada unificada e filtros combinados.
* `tests/Feature/DashboardTest.php`: Métricas consolidadas do mês e renderização de lançamentos.
* `tests/Feature/ExpenseReminderCommandTest.php`: Regras de envio do comando de lembrete por e-mail e templates.

---

## ⏰ Comandos e Rotinas do Sistema

### Enviar Lembretes de Vencimento Manualmente
```bash
php artisan expenses:send-reminders --days=2
```

### Executar Fila do Agendador de Tarefas
```bash
php artisan schedule:work
```

---

## 📄 Licença

Este projeto é desenvolvido para fins educacionais e de gestão financeira pessoal sob a licença [MIT](LICENSE).
