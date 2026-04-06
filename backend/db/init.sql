-- Расширение для UUID (опционально)
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Таблица авторов
CREATE TABLE IF NOT EXISTS authors (
    author_id SERIAL PRIMARY KEY,
    author_name VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица книг (связь с автором)
CREATE TABLE IF NOT EXISTS books (
    book_id SERIAL PRIMARY KEY,
    book_title VARCHAR(200) NOT NULL,
    author_id INTEGER NOT NULL REFERENCES authors(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Индексы для ускорения
CREATE INDEX IF NOT EXISTS idx_books_author_id ON books(author_id);
CREATE INDEX IF NOT EXISTS idx_authors_name ON authors(author_name);