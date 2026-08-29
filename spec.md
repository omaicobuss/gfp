# Feature Specification: Gestão Financeira Pessoal — MVP (Login Seguro + Cadastro de Gastos)

**Feature Branch**: `001-gestao-financeira-pessoal-mvp`

**Created**: 2026-08-29

**Status**: Draft

**Input**: User description: "Sistema web em PHP (Laravel) para gestão financeira pessoal. MVP com sistema de login completo e seguro (admin e usuários), seguindo boas práticas atuais. Cadastro de gastos, recorrentes ou não. Para gastos recorrentes: anexar documento de cobrança, comprovante de pagamento, data de pagamento recorrente, e data/valor de pagamento efetivo."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Registro e Autenticação Segura de Usuário (Priority: P1)

Como visitante, quero me cadastrar no sistema com e-mail e senha, confirmar meu e-mail e fazer login de forma segura, para começar a gerenciar minhas finanças pessoais com a garantia de que meus dados estão protegidos.

**Why this priority**: Sem autenticação segura não existe produto: é a porta de entrada e a base de isolamento de dados entre usuários. Deve ser a primeira coisa construída e validada.

**Independent Test**: Pode ser testado de forma isolada criando uma conta nova, verificando o e-mail via link/token, fazendo login, logout, e testando o fluxo de "esqueci minha senha" — sem depender de nenhuma outra funcionalidade do sistema.

**Acceptance Scenarios**:

1. **Given** um visitante na tela de cadastro, **When** ele informa nome, e-mail válido e senha que atende à política de senha, **Then** o sistema cria a conta em estado "não verificado" e envia e-mail de verificação.
2. **Given** um usuário com conta não verificada, **When** ele tenta fazer login, **Then** o sistema impede o acesso e informa que é necessário verificar o e-mail.
3. **Given** um usuário que clicou no link de verificação válido e dentro do prazo, **When** o token é validado, **Then** a conta passa para "verificada" e o usuário pode fazer login.
4. **Given** um usuário verificado, **When** ele informa e-mail e senha corretos, **Then** o sistema autentica e cria uma sessão segura.
5. **Given** um usuário, **When** ele erra a senha 5 vezes seguidas, **Then** o sistema bloqueia novas tentativas por um período (rate limiting) e informa o usuário.
6. **Given** um usuário que esqueceu a senha, **When** ele solicita redefinição informando o e-mail, **Then** o sistema envia um link de redefinição com token de uso único e validade limitada.
7. **Given** um token de redefinição de senha expirado ou já usado, **When** o usuário tenta utilizá-lo, **Then** o sistema rejeita e solicita novo pedido de redefinição.

---

### User Story 2 - Administração de Usuários (Priority: P1)

Como administrador do sistema, quero visualizar, ativar/bloquear e excluir contas de usuários, para manter a base de usuários sob controle, sem ter acesso aos dados financeiros pessoais de cada um.

**Why this priority**: É parte do "sistema de login completo" pedido, e deve existir desde o início para operação e suporte básico do sistema.

**Independent Test**: Pode ser testado logando como admin, listando usuários cadastrados, bloqueando e desbloqueando uma conta de teste e confirmando que o usuário bloqueado não consegue mais logar — sem depender do módulo de gastos.

**Acceptance Scenarios**:

1. **Given** um administrador autenticado, **When** ele acessa o painel de usuários, **Then** vê a lista de usuários com nome, e-mail, status (verificado/bloqueado) e data de cadastro.
2. **Given** um administrador, **When** ele bloqueia um usuário, **Then** esse usuário não consegue mais autenticar-se, mesmo com credenciais corretas.
3. **Given** um administrador, **When** ele exclui um usuário, **Then** o sistema solicita confirmação explícita antes de remover a conta.
4. **Given** um administrador autenticado, **When** ele tenta acessar gastos, categorias ou anexos de um usuário específico, **Then** o sistema não oferece essa opção (fora do escopo de permissão do admin).

---

### User Story 3 - Cadastro de Gasto Não Recorrente (Priority: P1)

Como usuário autenticado, quero cadastrar um gasto pontual (não recorrente), informando descrição, categoria, valor e data, para registrar rapidamente uma despesa avulsa.

**Why this priority**: É a funcionalidade central mais simples do módulo financeiro e entrega valor imediato ao usuário assim que o login estiver pronto.

**Independent Test**: Pode ser testado logando como usuário comum, criando um gasto avulso com todos os campos obrigatórios e conferindo que ele aparece salvo e associado apenas àquele usuário.

**Acceptance Scenarios**:

1. **Given** um usuário autenticado, **When** ele preenche descrição, categoria, valor (BRL) e data do gasto e salva, **Then** o gasto é criado com status conforme a data (pago/pendente/atrasado).
2. **Given** um gasto não recorrente já criado, **When** o usuário edita valor, data ou categoria, **Then** as alterações são persistidas e refletidas na listagem.
3. **Given** um gasto não recorrente já criado, **When** o usuário solicita exclusão e confirma, **Then** o gasto é removido permanentemente.
4. **Given** um usuário tentando salvar um gasto sem valor ou sem data, **When** ele envia o formulário, **Then** o sistema exibe erro de validação e não salva.

---

### User Story 4 - Cadastro de Gasto Recorrente com Anexos (Priority: P2)

Como usuário autenticado, quero cadastrar um gasto recorrente (ex: aluguel, assinatura), definindo frequência e dia de vencimento, e anexar o documento de cobrança, para ter um modelo reutilizável desse gasto mês a mês (ou na frequência escolhida).

**Why this priority**: É o diferencial central do produto pedido pelo usuário, mas depende do cadastro básico de gasto (US3) já existir.

**Independent Test**: Pode ser testado criando um gasto recorrente mensal com um documento de cobrança anexado (PDF/JPG/PNG até 10MB) e conferindo que ele fica salvo como "modelo" de gasto recorrente, sem gerar automaticamente lançamentos futuros.

**Acceptance Scenarios**:

1. **Given** um usuário autenticado, **When** ele cadastra um gasto recorrente informando descrição, categoria, valor esperado, frequência (semanal, mensal, anual ou personalizada em X dias) e dia/data de vencimento, **Then** o sistema salva esse gasto como um "gasto recorrente" (modelo).
2. **Given** um gasto recorrente sendo cadastrado, **When** o usuário anexa um arquivo de documento de cobrança em PDF, JPG ou PNG de até 10MB, **Then** o arquivo é armazenado e vinculado ao gasto recorrente.
3. **Given** um upload de arquivo acima de 10MB ou em formato não suportado, **When** o usuário tenta anexar, **Then** o sistema rejeita o arquivo e exibe mensagem de erro clara.
4. **Given** um gasto recorrente já criado, **When** o usuário edita frequência, valor esperado ou dia de vencimento, **Then** as alterações são salvas e passam a valer para as próximas ocorrências.
5. **Given** um gasto recorrente já criado, **When** o usuário o exclui, **Then** o sistema solicita confirmação e, ao confirmar, remove o gasto recorrente e (opcionalmente) mantém ou remove as ocorrências já registradas conforme confirmação do usuário.

---

### User Story 5 - Registro de Pagamento de uma Ocorrência do Gasto Recorrente (Priority: P2)

Como usuário autenticado, quero registrar manualmente cada pagamento efetivo de um gasto recorrente (data e valor pago), anexando o comprovante de pagamento, para manter o histórico real de pagamentos vinculado ao gasto recorrente "pai".

**Why this priority**: É o segundo pilar do pedido original do usuário (anexar comprovante + data/valor de pagamento) e depende diretamente da US4.

**Independent Test**: Pode ser testado a partir de um gasto recorrente já existente, registrando uma ocorrência de pagamento com data, valor pago e comprovante anexado, e conferindo que ela aparece vinculada ao gasto recorrente correto.

**Acceptance Scenarios**:

1. **Given** um gasto recorrente existente, **When** o usuário registra uma nova ocorrência de pagamento informando data de pagamento e valor pago, **Then** essa ocorrência é salva vinculada ao gasto recorrente "pai".
2. **Given** uma ocorrência de pagamento sendo registrada, **When** o usuário anexa o comprovante de pagamento (PDF, JPG ou PNG até 10MB), **Then** o arquivo é armazenado e vinculado a essa ocorrência específica.
3. **Given** uma ocorrência de pagamento já registrada, **When** o usuário edita data, valor pago ou substitui o comprovante, **Then** as alterações são persistidas.
4. **Given** uma ocorrência de pagamento já registrada, **When** o usuário a exclui, **Then** o registro dessa ocorrência é removido, sem afetar o gasto recorrente "pai" nem as demais ocorrências.
5. **Given** um gasto recorrente cujo vencimento já passou sem ocorrência de pagamento registrada, **When** o sistema avalia o status, **Then** ele é exibido como "atrasado" na listagem.

---

### User Story 6 - Listagem e Consulta de Gastos (Priority: P2)

Como usuário autenticado, quero visualizar todos os meus gastos (recorrentes e não recorrentes) em uma lista, filtrando por período, categoria e status, para entender rapidamente minha situação financeira.

**Why this priority**: Sem uma tela de consulta, os cadastros das US3/US4/US5 não geram valor percebido pelo usuário final.

**Independent Test**: Pode ser testado com uma massa de gastos previamente cadastrada, aplicando filtros de período, categoria e status, e conferindo que a lista retornada corresponde exatamente ao filtro aplicado.

**Acceptance Scenarios**:

1. **Given** um usuário autenticado com gastos cadastrados, **When** ele acessa a listagem, **Then** vê todos os seus gastos (recorrentes e não recorrentes) com descrição, categoria, valor, data e status.
2. **Given** a listagem de gastos, **When** o usuário filtra por período (data inicial/final), **Then** apenas os gastos dentro desse período são exibidos.
3. **Given** a listagem de gastos, **When** o usuário filtra por categoria e/ou status (pago, pendente, atrasado), **Then** apenas os gastos correspondentes são exibidos.
4. **Given** um usuário A autenticado, **When** ele acessa a listagem, **Then** nunca vê gastos pertencentes a outro usuário B.

---

### User Story 7 - Gerenciamento de Categorias Próprias (Priority: P3)

Como usuário autenticado, quero criar, editar e excluir minhas próprias categorias de gastos, para organizar meus gastos da forma que fizer mais sentido para mim.

**Why this priority**: Melhora a organização, mas o sistema pode funcionar no MVP com uma categoria padrão ("Outros") enquanto isso não existe — por isso é P3.

**Independent Test**: Pode ser testado criando, renomeando e excluindo uma categoria, e verificando o comportamento quando a categoria excluída está em uso por algum gasto.

**Acceptance Scenarios**:

1. **Given** um usuário autenticado, **When** ele cria uma nova categoria com nome único (para ele), **Then** ela fica disponível para seleção ao cadastrar gastos.
2. **Given** um usuário autenticado, **When** ele tenta criar uma categoria com nome já existente para ele mesmo, **Then** o sistema rejeita e exibe erro de duplicidade.
3. **Given** uma categoria em uso por um ou mais gastos, **When** o usuário tenta excluí-la, **Then** o sistema impede a exclusão e informa que ela está em uso, ou solicita que o usuário reatribua os gastos para outra categoria antes de excluir.
4. **Given** uma categoria sem nenhum gasto vinculado, **When** o usuário a exclui, **Then** ela é removida imediatamente.

---

### User Story 8 - Lembrete de Vencimento Próximo (Priority: P3)

Como usuário autenticado, quero receber um e-mail de aviso quando um gasto recorrente estiver perto do vencimento, para não esquecer de efetuar o pagamento.

**Why this priority**: Agrega valor e reduz atrasos, mas o sistema já é funcional sem isso — por isso é a última prioridade do MVP.

**Independent Test**: Pode ser testado configurando um gasto recorrente com vencimento em N dias e verificando que o e-mail de lembrete é disparado no prazo configurado, sem depender de interface adicional.

**Acceptance Scenarios**:

1. **Given** um gasto recorrente com vencimento configurado, **When** faltam X dias (parâmetro configurável, padrão sugerido: 3 dias) para o vencimento e ainda não há ocorrência de pagamento registrada para o período, **Then** o sistema envia e-mail de lembrete ao usuário.
2. **Given** um gasto recorrente cuja ocorrência já foi paga antes do vencimento, **When** a data de lembrete chegaria, **Then** nenhum e-mail de lembrete é enviado para aquele período.
3. **Given** uma falha temporária no envio de e-mail, **When** o sistema tenta notificar, **Then** o erro é registrado em log para reprocessamento, sem quebrar o restante do sistema.

---

### Edge Cases

- O que acontece se o usuário tentar se cadastrar com um e-mail já existente? → sistema deve rejeitar e informar (sem revelar se o e-mail existe, por segurança, retornar mensagem genérica de "verifique seu e-mail").
- O que acontece se o link de verificação de e-mail expirar antes do clique? → sistema deve permitir reenvio de novo e-mail de verificação.
- O que acontece se o usuário tentar anexar mais de um arquivo do mesmo tipo (ex: dois comprovantes) na mesma ocorrência? → sistema deve permitir apenas 1 documento de cobrança e 1 comprovante por ocorrência; um novo upload substitui o anterior.
- Como o sistema lida com um gasto recorrente com frequência "personalizada" cujo intervalo em dias resulta em datas que caem em meses/anos diferentes? → o cálculo de próximo vencimento deve ser sempre "data de vencimento anterior + X dias", sem regras de calendário especiais.
- O que acontece se um administrador tentar se auto-bloquear ou se autoexcluir? → sistema deve impedir essa ação para evitar perda de acesso administrativo.
- O que acontece se todos os administradores forem removidos? → sistema deve impedir a exclusão/bloqueio do último administrador ativo.
- Como o sistema trata tentativas de acesso direto (via URL) a um gasto, categoria ou anexo pertencente a outro usuário? → deve retornar erro de não autorizado/não encontrado, nunca expor dados de outro usuário.

## Requirements *(mandatory)*

### Functional Requirements

**Autenticação e Contas**

- **FR-001**: O sistema DEVE permitir que visitantes se auto-cadastrem informando nome, e-mail e senha.
- **FR-002**: O sistema DEVE aplicar uma política de senha forte (mínimo de caracteres, combinação de tipos de caractere) no cadastro e na redefinição de senha.
- **FR-003**: O sistema DEVE armazenar senhas utilizando hash forte e unidirecional (ex: bcrypt/argon2), nunca em texto plano.
- **FR-004**: O sistema DEVE exigir verificação de e-mail antes de permitir o primeiro login do usuário.
- **FR-005**: O sistema DEVE permitir o reenvio do e-mail de verificação caso o link expire ou não seja recebido.
- **FR-006**: O sistema DEVE oferecer fluxo de "esqueci minha senha" com token de uso único e validade limitada, enviado por e-mail.
- **FR-007**: O sistema DEVE aplicar limitação de tentativas de login (rate limiting / bloqueio temporário) para mitigar ataques de força bruta.
- **FR-008**: O sistema DEVE proteger todos os formulários contra CSRF e utilizar comunicação via HTTPS.
- **FR-009**: O sistema DEVE distinguir dois papéis de usuário: "Administrador" e "Usuário", com permissões distintas.
- **FR-010**: O sistema DEVE permitir que um Administrador visualize, bloqueie/desbloqueie e exclua contas de usuários, mediante confirmação explícita para exclusão.
- **FR-011**: O sistema NÃO DEVE conceder ao Administrador acesso aos gastos, categorias ou anexos financeiros de nenhum usuário.
- **FR-012**: O sistema DEVE impedir que o último administrador ativo seja bloqueado ou excluído.
- **FR-013**: O sistema DEVE encerrar a sessão do usuário de forma segura (logout) e expirar sessões inativas após um período configurável.

**Gastos (Não Recorrentes e Recorrentes)**

- **FR-014**: O sistema DEVE permitir que um usuário autenticado cadastre um gasto não recorrente com descrição, categoria, valor (BRL) e data.
- **FR-015**: O sistema DEVE permitir que um usuário autenticado cadastre um gasto recorrente ("modelo") com descrição, categoria, valor esperado, frequência (semanal, mensal, anual ou personalizada em X dias) e dia/data de vencimento.
- **FR-016**: O sistema DEVE permitir anexar, ao gasto recorrente, um único arquivo de "documento de cobrança" (PDF, JPG ou PNG, até 10MB).
- **FR-017**: O sistema DEVE permitir registrar, para cada gasto recorrente, ocorrências individuais de pagamento contendo data de pagamento e valor efetivamente pago.
- **FR-018**: O sistema DEVE permitir anexar, a cada ocorrência de pagamento, um único arquivo de "comprovante de pagamento" (PDF, JPG ou PNG, até 10MB).
- **FR-019**: O sistema DEVE rejeitar uploads que excedam 10MB ou que não estejam nos formatos permitidos (PDF, JPG, PNG), exibindo mensagem de erro clara.
- **FR-020**: O sistema DEVE calcular e exibir o status de cada gasto (não recorrente) e de cada ocorrência (recorrente) como "Pago", "Pendente" ou "Atrasado", com base na data de vencimento/pagamento.
- **FR-021**: O sistema DEVE permitir editar e excluir gastos não recorrentes já cadastrados, mediante confirmação para exclusão.
- **FR-022**: O sistema DEVE permitir editar e excluir gastos recorrentes ("modelo") já cadastrados, mediante confirmação para exclusão.
- **FR-023**: O sistema DEVE permitir editar e excluir ocorrências de pagamento de gastos recorrentes já registradas, mediante confirmação para exclusão.
- **FR-024**: O sistema DEVE garantir isolamento total de dados: um usuário jamais pode visualizar, editar ou excluir gastos, categorias ou anexos de outro usuário.

**Categorias**

- **FR-025**: O sistema DEVE permitir que cada usuário crie, edite e exclua suas próprias categorias de gastos.
- **FR-026**: O sistema DEVE impedir a criação de categorias com nome duplicado para o mesmo usuário.
- **FR-027**: O sistema DEVE impedir a exclusão de uma categoria que esteja em uso por algum gasto, a menos que o usuário reatribua os gastos vinculados a outra categoria.
- **FR-028**: O sistema DEVE disponibilizar uma categoria padrão (ex: "Outros") para uso imediato, sem exigir que o usuário crie categorias antes de cadastrar seu primeiro gasto.

**Listagem e Consulta**

- **FR-029**: O sistema DEVE exibir, para o usuário autenticado, uma listagem consolidada de todos os seus gastos (recorrentes e não recorrentes).
- **FR-030**: O sistema DEVE permitir filtrar a listagem de gastos por período (data inicial/final), categoria e status (Pago/Pendente/Atrasado).

**Notificações**

- **FR-031**: O sistema DEVE enviar um e-mail de lembrete ao usuário quando um gasto recorrente estiver a X dias (parâmetro configurável, padrão 3 dias) do vencimento e ainda não houver ocorrência de pagamento registrada para o período correspondente.
- **FR-032**: O sistema DEVE registrar em log falhas no envio de e-mails de notificação, sem interromper o funcionamento geral do sistema.

### Key Entities *(include if feature involves data)*

- **User**: Representa uma pessoa com acesso ao sistema. Atributos-chave: nome, e-mail (único), senha (hash), papel (Administrador/Usuário), status de verificação de e-mail, status de bloqueio.
- **Category**: Categoria de gasto criada por um usuário. Atributos-chave: nome, dono (User). Relaciona-se a Expenses e Recurring Expenses.
- **Expense (Gasto Não Recorrente)**: Um gasto pontual. Atributos-chave: descrição, valor, data, status (Pago/Pendente/Atrasado). Relaciona-se a um User (dono) e a uma Category.
- **Recurring Expense (Gasto Recorrente)**: Modelo/"molde" de um gasto que se repete. Atributos-chave: descrição, valor esperado, frequência (semanal/mensal/anual/personalizada em X dias), dia/data de vencimento, documento de cobrança anexado. Relaciona-se a um User (dono), a uma Category e a múltiplas Recurring Expense Occurrences.
- **Recurring Expense Occurrence (Ocorrência de Pagamento)**: Registro de um pagamento efetivo referente a um Recurring Expense. Atributos-chave: data de pagamento, valor pago, status (Pago/Pendente/Atrasado), comprovante de pagamento anexado. Relaciona-se a um Recurring Expense (pai).
- **Attachment (Anexo)**: Arquivo enviado (documento de cobrança ou comprovante de pagamento). Atributos-chave: tipo (documento de cobrança/comprovante), nome do arquivo, formato, tamanho, data de upload. Relaciona-se a um Recurring Expense ou a uma Recurring Expense Occurrence.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Um novo usuário consegue se cadastrar, verificar o e-mail e realizar o primeiro login em menos de 3 minutos.
- **SC-002**: 100% das senhas armazenadas utilizam hash forte; nenhuma senha em texto plano é encontrada em banco de dados ou logs em auditoria.
- **SC-003**: Um usuário consegue cadastrar um gasto não recorrente completo em menos de 1 minuto.
- **SC-004**: Um usuário consegue cadastrar um gasto recorrente com documento de cobrança anexado em menos de 3 minutos.
- **SC-005**: 100% dos uploads acima de 10MB ou em formato não suportado são rejeitados com mensagem de erro compreensível.
- **SC-006**: 100% dos gastos e ocorrências com vencimento vencido e sem pagamento registrado são exibidos como "Atrasado" em até 24 horas após a data de vencimento.
- **SC-007**: 0 (zero) incidentes de vazamento de dados entre usuários distintos, validado por testes de isolamento (usuário A nunca acessa dados do usuário B).
- **SC-008**: Pelo menos 95% dos e-mails de lembrete de vencimento configurados são efetivamente enviados dentro da janela configurada (ex: X dias antes do vencimento).
- **SC-009**: 100% das tentativas de login com credenciais corretas, para contas verificadas e não bloqueadas, resultam em autenticação bem-sucedida em menos de 2 segundos (excluindo latência de rede do cliente).

## Assumptions

- O sistema opera exclusivamente em Real (BRL) nesta versão; suporte a múltiplas moedas está fora do escopo do MVP.
- Não há, nesta versão, funcionalidades de controle de renda/receitas, orçamento (budget), metas financeiras ou relatórios/gráficos analíticos — apenas cadastro e consulta simples de gastos.
- Compartilhamento de dados entre usuários (ex: casal ou família gerenciando finanças em conjunto) está fora do escopo deste MVP, mas é um cenário previsto para evolução futura; o modelo de dados deve, na medida do possível, não impedir essa evolução.
- A geração automática de lançamentos futuros para gastos recorrentes está fora de escopo: cada ocorrência de pagamento é registrada manualmente pelo usuário, vinculada ao gasto recorrente "pai".
- Existe um serviço de envio de e-mail configurado e disponível (ex: SMTP/serviço transacional) para os fluxos de verificação de conta, redefinição de senha e lembretes de vencimento.
- O Administrador é um papel puramente operacional/de suporte à base de usuários, sem qualquer acesso a dados financeiros — essa restrição é uma decisão de produto, não uma limitação técnica.
- Aplicativo mobile nativo está fora de escopo; o MVP é uma aplicação web responsiva.
- Auditoria/log de eventos de segurança (login, falha de login, bloqueio) deve existir internamente por boas práticas, mas não é uma tela exposta ao usuário final nesta versão.
