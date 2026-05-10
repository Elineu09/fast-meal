Projeto: Fast Meal
Pilha Técnica
Frontend: Angular
Backend: PHP puro (PDO)
Banco de Dados: MySQL ou MariaDB
API externa: API pública de geração de QR Code (REST)

APIs sugeridas:

QuickChart API
GoQR API

Restrições:

Não utilizar Laravel
Não utilizar frameworks PHP
Não utilizar Firebase
Não utilizar ORM
Utilizar apenas PHP puro com PDO
O frontend deve utilizar Angular com arquitetura modular
Padrões de Codificação
O sistema deve utilizar TypeScript no Angular
O código deve ser claro, modular e reutilizável
Componentes devem possuir responsabilidade única
Evitar duplicação de código (DRY)
Utilizar nomes descritivos para variáveis, métodos e classes
Métodos devem ser pequenos e focados
Separar responsabilidades entre UI, lógica e dados
Evitar código acoplado
Utilizar interfaces e tipagem forte no frontend
O código deve ser organizado e de fácil manutenção
Princípios de Arquitectura
Separar claramente:
Interface (Frontend Angular)
Lógica de negócio (Backend PHP)
Persistência de dados (Base de Dados SQL)
O sistema deve seguir arquitetura em camadas:
Frontend
Components
Pages
Services
Guards
Interceptors
Models
Backend
Routes
Controllers
Services
Repositories
Models
Middleware
Cada módulo deve possuir responsabilidade única
O frontend deve comunicar exclusivamente via HTTP/JSON
O backend não deve retornar HTML
Toda lógica de negócio deve ficar no backend
Estrutura de Pastas
queueflow/
│
├── frontend/
│   ├── src/
│   │   ├── app/
│   │   │   ├── components/
│   │   │   │   ├── navbar/
│   │   │   │   ├── sidebar/
│   │   │   │   ├── ticket-card/
│   │   │   │   ├── qr-modal/
│   │   │   │   ├── queue-status/
│   │   │   │   ├── darkmode-toggle/
│   │   │   │   └── language-switcher/
│   │   │   │
│   │   │   ├── pages/
│   │   │   │   ├── login/
│   │   │   │   ├── register/
│   │   │   │   ├── forgot-password/
│   │   │   │   ├── dashboard/
│   │   │   │   ├── request-ticket/
│   │   │   │   ├── queue-monitor/
│   │   │   │   ├── history/
│   │   │   │   ├── admin-dashboard/
│   │   │   │   └── profile/
│   │   │   │
│   │   │   ├── services/
│   │   │   │   ├── api.service.ts
│   │   │   │   ├── auth.service.ts
│   │   │   │   ├── ticket.service.ts
│   │   │   │   ├── queue.service.ts
│   │   │   │   ├── qr.service.ts
│   │   │   │   ├── report.service.ts
│   │   │   │   ├── language.service.ts
│   │   │   │   └── theme.service.ts
│   │   │   │
│   │   │   ├── guards/
│   │   │   │   ├── auth.guard.ts
│   │   │   │   └── admin.guard.ts
│   │   │   │
│   │   │   ├── interceptors/
│   │   │   │   ├── auth.interceptor.ts
│   │   │   │   └── error.interceptor.ts
│   │   │   │
│   │   │   ├── models/
│   │   │   │   ├── user.model.ts
│   │   │   │   ├── ticket.model.ts
│   │   │   │   ├── attendance.model.ts
│   │   │   │   └── report.model.ts
│   │   │   │
│   │   │   ├── shared/
│   │   │   │   ├── constants/
│   │   │   │   ├── helpers/
│   │   │   │   └── validators/
│   │   │   │
│   │   │   ├── app-routing.module.ts
│   │   │   ├── app.component.ts
│   │   │   └── app.module.ts
│   │   │
│   │   ├── assets/
│   │   │   ├── images/
│   │   │   ├── icons/
│   │   │   └── i18n/
│   │   │       ├── pt.json
│   │   │       └── en.json
│   │   │
│   │   ├── environments/
│   │   └── styles.css
│   │
│   ├── angular.json
│   └── package.json
│
├── backend/
│   ├── app/
│   │   ├── controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── TicketController.php
│   │   │   ├── QueueController.php
│   │   │   ├── ReportController.php
│   │   │   └── UserController.php
│   │   │
│   │   ├── services/
│   │   │   ├── AuthService.php
│   │   │   ├── TicketService.php
│   │   │   ├── QueueService.php
│   │   │   ├── QRCodeService.php
│   │   │   ├── ReportService.php
│   │   │   └── AnalyticsService.php
│   │   │
│   │   ├── repositories/
│   │   │   ├── UserRepository.php
│   │   │   ├── TicketRepository.php
│   │   │   ├── AttendanceRepository.php
│   │   │   └── ReportRepository.php
│   │   │
│   │   ├── models/
│   │   │   ├── User.php
│   │   │   ├── Ticket.php
│   │   │   ├── Attendance.php
│   │   │   └── Report.php
│   │   │
│   │   ├── middleware/
│   │   │   ├── AuthMiddleware.php
│   │   │   └── AdminMiddleware.php
│   │   │
│   │   ├── routes/
│   │   │   ├── auth.php
│   │   │   ├── tickets.php
│   │   │   ├── queue.php
│   │   │   ├── reports.php
│   │   │   └── users.php
│   │   │
│   │   ├── utils/
│   │   │   ├── Response.php
│   │   │   ├── Validator.php
│   │   │   ├── Helpers.php
│   │   │   └── ErrorHandler.php
│   │   │
│   │   └── config/
│   │       ├── database.php
│   │       ├── app.php
│   │       └── cors.php
│   │
│   ├── database/
│   │   ├── schema.sql
│   │   ├── seed.sql
│   │   └── migrations/
│   │
│   ├── public/
│   │   └── index.php
│   │
│   ├── .env.example
│   └── composer.json
│
├── docs/
│   ├── ARCHITECTURE.md
│   ├── API.md
│   ├── DATABASE.md
│   ├── SECURITY.md
│   └── SETUP.md
│
├── README.md
├── CONTEXT.md
├── GUARDRAIL.md
└── .gitignore
Regras Técnicas Obrigatórias
O sistema deve utilizar autenticação segura
O sistema deve utilizar password_hash/password_verify
Todas as respostas do backend devem estar no formato JSON
O sistema deve tratar erros de forma consistente
O sistema deve utilizar PDO para acesso à base de dados
O sistema deve possuir CRUD completo
O sistema deve implementar sistema multilíngue
O sistema deve implementar dark/light mode
O sistema deve ser responsivo
O sistema deve implementar permissões por tipo de utilizador
Integração com API
O sistema deve consumir API pública de QR Code via HTTP
O frontend deve exibir QR Code da senha gerada
O sistema deve tratar:
erros de rede
limite de requisições
respostas inválidas
timeout da API
Regras de Negócio
Utilizador comum
Pode criar conta
Pode fazer login/logout
Pode solicitar senha
Pode visualizar posição na fila
Pode visualizar histórico de senhas
Pode cancelar senha pendente
Administrador
Pode chamar próxima senha
Pode finalizar atendimento
Pode visualizar estatísticas
Pode exportar relatórios
Pode gerir utilizadores
Pode monitorar filas em tempo real
Base de Dados

O sistema deve possuir no mínimo as seguintes tabelas:

users
id
nome
email
password
role
created_at
tickets
id
ticket_number
type
status
user_id
created_at
attendances
id
ticket_id
called_at
finished_at
counter_number
Funcionalidades Obrigatórias
Registo de utilizadores
Login e logout
Recuperação de senha
Geração de senhas
QR Code para senhas
Monitoramento da fila
Painel administrativo
CRUD de utilizadores
CRUD de senhas
Relatórios PDF/CSV
Sistema multilíngue
Dark mode / Light mode
Responsividade
Gestão de permissões
Funcionalidade Inteligente

O sistema deve possuir pelo menos uma funcionalidade inteligente.

Sugestões:

previsão de tempo de espera
identificação de horários de pico
estatísticas automáticas
priorização inteligente de filas
Instruções para a IA
Explicar sempre as decisões arquiteturais e escolhas técnicas
Fazer perguntas de esclarecimento se os requisitos forem ambíguos
Começar com a solução mais simples que funcione
Gerar código incrementalmente
Validar integração entre frontend e backend
Manter arquitetura modular
Priorizar código legível e manutenível

A IA não deve:

adicionar funcionalidades não solicitadas
alterar requisitos definidos
misturar responsabilidades entre camadas
gerar código monolítico
ignorar validações ou segurança
Regras de Segurança
Nunca armazenar palavras-passe em texto simples
Validar todas as entradas do utilizador
Utilizar prepared statements com PDO
Evitar SQL Injection
Evitar XSS
Não expor credenciais
Proteger rotas administrativas
Validar permissões no backend
Sanitizar dados recebidos
UI/UX
Design responsivo (mobile-first)
Interface moderna e intuitiva
Layout limpo e organizado
Feedback visual para ações do utilizador
Sistema de notificações
Utilização de cards e tabelas organizadas
Interface acessível e consistente
Navegação simples
Alternância clara entre dark/light mode