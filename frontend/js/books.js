document.addEventListener('DOMContentLoaded', () => {
    const newPanel = document.getElementById('new-books');
    const topPanel = document.getElementById('top-books');
    if (!newPanel && !topPanel) return;

    const newCase = newPanel ? newPanel.querySelector('.books-case') : null;
    const topCase = topPanel ? topPanel.querySelector('.books-case') : null;

    if (newCase) newCase.innerHTML = '<p class="loader">Загрузка...</p>';
    if (topCase) topCase.innerHTML = '<p class="loader">Загрузка...</p>';

    const MAX_CARDS = 100;

    fetch('/books')
        .then(res => res.json())
        .then(json => {
            if (!json || !json.success) {
                if (newCase) newCase.innerHTML = '<p class="error">Не удалось загрузить книги.</p>';
                if (topCase) topCase.innerHTML = '<p class="error">Не удалось загрузить книги.</p>';
                return;
            }
            const books = json.data || [];
            if (!books.length) {
                if (newCase) newCase.innerHTML = '<p class="empty">Книги не найдены.</p>';
                if (topCase) topCase.innerHTML = '<p class="empty">Книги не найдены.</p>';
                return;
            }

            // Sort for new-books: by created_at desc (newest first)
            if (newCase) {
                const byDate = Array.from(books).slice();
                byDate.sort((a, b) => {
                    const da = a.created_at ? new Date(a.created_at).getTime() : 0;
                    const db = b.created_at ? new Date(b.created_at).getTime() : 0;
                    return db - da;
                });
                renderBooksInto(byDate.slice(0, MAX_CARDS), newCase);
            }

            // Sort for top-books: by title ascending
            if (topCase) {
                const byTitle = Array.from(books).slice();
                byTitle.sort((a, b) => {
                    const ta = (a.book_title || '').toLowerCase();
                    const tb = (b.book_title || '').toLowerCase();
                    return ta < tb ? -1 : ta > tb ? 1 : 0;
                });
                renderBooksInto(byTitle.slice(0, MAX_CARDS), topCase);
            }
        })
        .catch(() => {
            if (newCase) newCase.innerHTML = '<p class="error">Ошибка сети при загрузке книг.</p>';
            if (topCase) topCase.innerHTML = '<p class="error">Ошибка сети при загрузке книг.</p>';
        });
});

function renderBooksInto(books, container) {
    container.innerHTML = '';
    books.forEach(book => {
        const card = document.createElement('div');
        card.className = 'book-card';

        const img = document.createElement('img');
        img.className = 'book-cover';
        img.alt = 'обложка книги';
        img.src = book.book_cover_url && book.book_cover_url.trim() !== '' ? book.book_cover_url : 'images/book-cover.jpg';

        const name = document.createElement('p');
        name.className = 'book-name';
        name.textContent = book.book_title || 'Без названия';

        const author = document.createElement('p');
        author.className = 'book-author';
        author.textContent = book.author_name || 'Неизвестный автор';

        card.appendChild(img);
        card.appendChild(name);
        card.appendChild(author);

        container.appendChild(card);
    });
}
