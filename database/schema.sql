-- Script de creación de base de datos para el sistema de Gimnasio
-- Este script crea las tablas en el SERVIDOR de base de datos PostgreSQL

-- Crear base de datos (ejecutar como superusuario)
-- CREATE DATABASE gimnasio_db;
-- \c gimnasio_db;

-- Tabla de Miembros
CREATE TABLE IF NOT EXISTS members (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    registration_date DATE NOT NULL DEFAULT CURRENT_DATE,
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive', 'suspended')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Clases
CREATE TABLE IF NOT EXISTS classes (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    instructor VARCHAR(100) NOT NULL,
    schedule_time TIME NOT NULL,
    schedule_days VARCHAR(50) NOT NULL, -- Ej: 'Lunes, Miércoles, Viernes'
    capacity INTEGER NOT NULL CHECK (capacity > 0),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Horarios del Gimnasio
CREATE TABLE IF NOT EXISTS schedules (
    id SERIAL PRIMARY KEY,
    day_of_week VARCHAR(20) NOT NULL,
    opening_time TIME NOT NULL,
    closing_time TIME NOT NULL,
    is_active BOOLEAN DEFAULT true,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT valid_time_range CHECK (closing_time > opening_time)
);

-- Tabla de Tipos de Membresía
CREATE TABLE IF NOT EXISTS membership_types (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    price DECIMAL(10, 2) NOT NULL CHECK (price >= 0),
    duration_days INTEGER NOT NULL CHECK (duration_days > 0),
    description TEXT
);

-- Tabla de Pagos
CREATE TABLE IF NOT EXISTS payments (
    id SERIAL PRIMARY KEY,
    member_id INTEGER NOT NULL REFERENCES members(id) ON DELETE CASCADE,
    membership_type_id INTEGER NOT NULL REFERENCES membership_types(id),
    amount DECIMAL(10, 2) NOT NULL CHECK (amount >= 0),
    payment_date DATE NOT NULL DEFAULT CURRENT_DATE,
    payment_method VARCHAR(50) DEFAULT 'cash' CHECK (payment_method IN ('cash', 'card', 'transfer')),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar datos de ejemplo para tipos de membresía
INSERT INTO membership_types (name, price, duration_days, description) VALUES
    ('Mensual', 50.00, 30, 'Membresía mensual estándar'),
    ('Trimestral', 135.00, 90, 'Membresía trimestral con descuento'),
    ('Anual', 500.00, 365, 'Membresía anual con máximo descuento')
ON CONFLICT (name) DO NOTHING;

-- Insertar datos de ejemplo
INSERT INTO members (name, email, phone, registration_date) VALUES
    ('Juan Pérez', 'juan.perez@email.com', '0987654321', '2024-01-15'),
    ('María García', 'maria.garcia@email.com', '0987654322', '2024-02-20'),
    ('Carlos López', 'carlos.lopez@email.com', '0987654323', '2024-03-10')
ON CONFLICT (email) DO NOTHING;

INSERT INTO classes (name, instructor, schedule_time, schedule_days, capacity, description) VALUES
    ('Yoga Matutino', 'Ana Martínez', '08:00:00', 'Lunes, Miércoles, Viernes', 20, 'Clase de yoga para principiantes'),
    ('CrossFit', 'Pedro Rodríguez', '18:00:00', 'Martes, Jueves, Sábado', 15, 'Entrenamiento de alta intensidad'),
    ('Pilates', 'Laura Sánchez', '10:00:00', 'Lunes, Miércoles, Viernes', 18, 'Clase de pilates para fortalecimiento')
ON CONFLICT DO NOTHING;

-- Insertar horarios de ejemplo
INSERT INTO schedules (day_of_week, opening_time, closing_time, is_active, notes) VALUES
    ('Lunes', '06:00:00', '22:00:00', true, 'Horario regular de lunes'),
    ('Martes', '06:00:00', '22:00:00', true, 'Horario regular de martes'),
    ('Miércoles', '06:00:00', '22:00:00', true, 'Horario regular de miércoles'),
    ('Jueves', '06:00:00', '22:00:00', true, 'Horario regular de jueves'),
    ('Viernes', '06:00:00', '22:00:00', true, 'Horario regular de viernes'),
    ('Sábado', '08:00:00', '20:00:00', true, 'Horario reducido de sábado'),
    ('Domingo', '08:00:00', '14:00:00', true, 'Horario reducido de domingo')
ON CONFLICT DO NOTHING;

