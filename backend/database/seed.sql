-- ============================================================================
-- Fast Meal - Database Seeds
-- Inserção de dados iniciais para testes
-- ============================================================================

USE fast_meal;

-- ============================================================================
-- USERS SEEDS
-- Credenciais de teste:
--   Admin: admin@example.com / admin123
--   Funcionário 1: funcionar1@example.com / func123
--   Funcionário 2: funcionar2@example.com / func123
--   Estudante 1: estudante1@example.com / student123
--   Estudante 2: estudante2@example.com / student123
-- ============================================================================

-- Hash: bcrypt(admin123) - gerado com PHP password_hash()
INSERT INTO users (nome, email, password, role) VALUES
('Administrador Sistema', 'admin@example.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/TVm', 'admin');

-- Hash: bcrypt(func123) - gerado com PHP password_hash()
INSERT INTO users (nome, email, password, role) VALUES
('João da Silva', 'funcionar1@example.com', '$2y$10$slYQmyNdGzin7olVN3junOYvnH8KO0jey/xY4yS0EUgPfQbXK2Z4e', 'employee'),
('Maria Santos', 'funcionar2@example.com', '$2y$10$slYQmyNdGzin7olVN3junOYvnH8KO0jey/xY4yS0EUgPfQbXK2Z4e', 'employee');

-- Hash: bcrypt(student123) - gerado com PHP password_hash()
INSERT INTO users (nome, email, password, role) VALUES
('Pedro Oliveira', 'estudante1@example.com', '$2y$10$yEGKO4UTJrKF79L5GEqb7.K3jbV0z1u0vMR8j4IGWv2M2BhIdLJHy', 'student'),
('Ana Costa', 'estudante2@example.com', '$2y$10$yEGKO4UTJrKF79L5GEqb7.K3jbV0z1u0vMR8j4IGWv2M2BhIdLJHy', 'student');

-- ============================================================================
-- TICKETS SEEDS
-- Senhas geradas pelos utilizadores
-- Funcionários: tipo 'priority' com prefixo 'P'
-- Estudantes: tipo 'normal' com prefixo 'A'
-- ============================================================================

-- Funcionários (Employee) - Type: priority, Prefixo: P
INSERT INTO tickets (ticket_number, type, status, user_id) VALUES
('P001', 'priority', 'completed', 2),      -- João da Silva
('P002', 'priority', 'in_attendance', 3);  -- Maria Santos

-- Estudantes (Student) - Type: normal, Prefixo: A
INSERT INTO tickets (ticket_number, type, status, user_id) VALUES
('A001', 'normal', 'pending', 4),          -- Pedro Oliveira
('A002', 'normal', 'completed', 5);        -- Ana Costa

-- ============================================================================
-- ATTENDANCES SEEDS
-- Histórico de atendimentos das senhas
-- ============================================================================

-- Atendimento da senha P001 (João da Silva) - Finalizado
INSERT INTO attendances (ticket_id, called_at, finished_at, counter_number) VALUES
(1, '2026-05-10 10:15:00', '2026-05-10 10:22:00', 1);

-- Atendimento da senha P002 (Maria Santos) - Em curso
INSERT INTO attendances (ticket_id, called_at, finished_at, counter_number) VALUES
(2, '2026-05-10 10:30:00', NULL, 2);

-- Atendimento da senha A002 (Ana Costa) - Finalizado
INSERT INTO attendances (ticket_id, called_at, finished_at, counter_number) VALUES
(4, '2026-05-10 10:25:00', '2026-05-10 10:40:00', 1);

-- ============================================================================
-- SUMMARY
-- ============================================================================
-- Utilizadores: 1 Admin + 2 Funcionários + 2 Estudantes = 5 utilizadores
-- Senhas: 4 senhas (2 priority, 2 normal)
-- Atendimentos: 3 registos de atendimento
-- ============================================================================
