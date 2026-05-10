Projeto: Fast Meal

Descrição:
Uma aplicação web desenvolvida para digitalizar, organizar e otimizar o fluxo de atendimento num refeitório. O sistema permite que os utentes (estudantes e funcionários) solicitem senhas digitais com códigos QR integrados e acompanhem a sua posição na fila em tempo real. Paralelamente, fornece à equipa de administração as ferramentas necessárias para gerir as chamadas, controlar o estado do atendimento e extrair relatórios estatísticos, promovendo um serviço mais célere e organizado.

Objetivo geral: Desenvolver uma aplicação funcional e completa que:
Permita a gestão digital e eficiente das filas de espera de um refeitório, desde a autenticação de utilizadores até à gestão de senhas com diferentes níveis de prioridade. O sistema visa automatizar a chamada de utentes, fornecer feedback visual em tempo real (via QR Code e painéis de acompanhamento), e capacitar a administração com ferramentas de gestão e exportação de dados, garantindo uma arquitetura robusta, segura e totalmente responsiva.

Requisitos Funcionais:
Descrevem o que o sistema deve fazer e quais as funcionalidades que oferece aos utilizadores.

RF01 - Gestão de Conta (Registo): O sistema deve permitir o registo de novos utilizadores com nome, email e palavra-passe.

RF02 - Autenticação: O sistema deve permitir o login, logout e a gestão de sessões dos utilizadores.

RF03 - Recuperação de Palavra-passe: O sistema deve fornecer um mecanismo para recuperação de senha (via link ou código).

RF04 - Solicitação de Senhas: O sistema deve permitir que utilizadores comuns solicitem uma senha para atendimento no refeitório.

RF05 - Geração de QR Code: Ao solicitar uma senha, o sistema deve gerar e exibir um QR Code correspondente contendo o número, data e tipo de atendimento.

RF06 - Acompanhamento de Fila: O sistema deve permitir que o utilizador visualize a sua posição na fila e o estado atual da sua senha em tempo real.

RF07 - Cancelamento e Histórico: O sistema deve permitir ao utilizador cancelar a sua senha ativa e visualizar o histórico de senhas passadas.

RF08 - Controlo de Atendimento (Admin): O sistema deve permitir ao administrador chamar a próxima senha, pausar ou finalizar um atendimento.

RF09 - Gestão da Fila (Admin): O sistema deve exibir ao administrador a lista completa da fila de espera e o seu estado atual.

RF10 - Exportação de Relatórios: O sistema deve permitir ao administrador exportar o histórico de atendimentos e estatísticas diárias nos formatos PDF e CSV.

RF11 - Internacionalização: O sistema deve permitir a alternância de idioma (mínimo Português e Inglês).

RF12 - Personalização de Interface: O sistema deve permitir a alternância entre o modo claro (Light Mode) e modo escuro (Dark Mode).

Requisitos Não Funcionais:
Descrevem como o sistema deve operar, focando na tecnologia, segurança, usabilidade e desempenho.

RNF01 - Stack Tecnológica: O frontend deve ser desenvolvido em Angular e o backend em PHP puro com recurso a PDO.

RNF02 - Base de Dados: O sistema deve utilizar uma base de dados relacional (MySQL ou MariaDB) com, no mínimo, as tabelas de utilizadores, senhas e atendimentos.

RNF03 - Arquitetura de Comunicação: A comunicação entre o frontend e o backend deve ser feita exclusivamente via API RESTful através de requisições HTTP, com tráfego de dados em formato JSON.

RNF04 - Integração Externa: A geração do QR Code deve ser feita através do consumo de uma API externa (ex: QuickChart API ou GoQR API).

RNF05 - Responsividade: A interface de utilizador deve ser responsiva, garantindo o funcionamento e boa visualização em smartphones, tablets e computadores.

RNF06 - Segurança de Dados: As palavras-passe dos utilizadores devem ser armazenadas na base de dados utilizando algoritmos de hash seguros (bcrypt/password_hash).

RNF07 - Versionamento: O código fonte deve estar alojado num repositório GitHub, com histórico de commits que comprovem a evolução progressiva e o contributo individual.

Regras de Negócio:
As regras lógicas e restrições que guiam o comportamento das funcionalidades dentro do contexto do refeitório.

RN01 - Controlo de Acesso: Apenas utilizadores devidamente autenticados podem solicitar senhas ou aceder aos painéis do sistema.

RN02 - Tipologia e Prioridade de Senhas: O sistema gera senhas com base no tipo de utilizador. Senhas de Funcionários começam por "P" (ex: P001) e têm prioridade de atendimento. Senhas de Estudantes começam por "A" (ex: A001) e seguem a ordem normal de chegada.

RN03 - Limite de Solicitação: Um utilizador só pode ter uma (1) senha com o estado "pendente" ou "em_atendimento" num determinado momento. Para pedir uma nova senha, a anterior deve ser concluída ou cancelada.

RN04 - Exclusividade de Ação Administrativa: Apenas utilizadores com o perfil de "Administrador" têm permissão para interagir com o módulo de Controlo de Atendimento (chamar, pausar, finalizar) e visualizar os relatórios estatísticos.

RN05 - Integridade do Atendimento: Uma vez que o atendimento de uma senha é marcado como "finalizado" ou "cancelado", o seu estado não pode ser revertido.