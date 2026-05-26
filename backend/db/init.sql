-- Таблица авторов
CREATE TABLE IF NOT EXISTS authors (
    author_id SERIAL PRIMARY KEY,
    author_name VARCHAR(100) UNIQUE NOT NULL
);

-- Таблица книг (связь с автором)
CREATE TABLE IF NOT EXISTS books (
    book_id SERIAL PRIMARY KEY,
    book_title VARCHAR(200) NOT NULL,
    book_cover_url VARCHAR(500),
    annotation TEXT,
    table_of_contents TEXT,
    author_id INTEGER NOT NULL REFERENCES authors(author_id) ON DELETE CASCADE
);

-- Таблица жанров и связь многие-ко-многим
CREATE TABLE IF NOT EXISTS genres (
    genre_id SERIAL PRIMARY KEY,
    genre_name VARCHAR(100) UNIQUE NOT NULL
);

CREATE TABLE IF NOT EXISTS books_genres (
    book_id INTEGER NOT NULL REFERENCES books(book_id) ON DELETE CASCADE,
    genre_id INTEGER NOT NULL REFERENCES genres(genre_id) ON DELETE CASCADE,
    PRIMARY KEY (book_id, genre_id)
);

-- Индексы для ускорения
CREATE INDEX IF NOT EXISTS idx_authors_name ON authors(author_name);
CREATE INDEX IF NOT EXISTS idx_books_author_id ON books(author_id);
CREATE INDEX IF NOT EXISTS idx_books_genres_book_id ON books_genres(book_id);
CREATE INDEX IF NOT EXISTS idx_books_genres_genre_id ON books_genres(genre_id);
CREATE INDEX IF NOT EXISTS idx_genres_name ON genres(genre_name);