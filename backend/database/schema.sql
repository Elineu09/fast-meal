-- ============================================================================
-- Fast Meal - Database Schema
-- Sistema de Atendimento de Fila do Refeitório
-- ============================================================================

-- ============================================================================
-- DATABASE CREATION
-- ============================================================================
DROP DATABASE IF EXISTS fast_meal;
CREATE DATABASE fast_meal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fast_meal;

-- ============================================================================
-- USERS TABLE
-- Armazena informações de utilizadores: estudantes, funcionários e admins
-- ============================================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'employee', 'student') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TICKETS TABLE
-- Armazena senhas geradas pelos utilizadores
-- type: 'normal' para estudantes, 'priority' para funcionários
-- status: pending -> in_attendance -> completed/cancelled
-- ============================================================================
CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_number VARCHAR(10) NOT NULL UNIQUE,
    type ENUM('normal', 'priority') NOT NULL,
    status ENUM('pending', 'in_attendance', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_type (type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- ATTENDANCES TABLE
-- Armazena histórico de atendimentos das senhas
-- called_at: quando a senha foi chamada
-- finished_at: quando o atendimento foi finalizado (NULL se ainda em atendimento)
-- ============================================================================
CREATE TABLE attendances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL UNIQUE,
    called_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    finished_at TIMESTAMP NULL,
    counter_number INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    INDEX idx_ticket_id (ticket_id),
    INDEX idx_called_at (called_at),
    INDEX idx_finished_at (finished_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- DATABASE SUMMARY
-- ============================================================================
-- Tabelas: users, tickets, attendances
-- Relacionamentos:
--   - tickets.user_id -> users.id (ON DELETE CASCADE)
--   - attendances.ticket_id -> tickets.id (ON DELETE CASCADE)
-- ============================================================================
