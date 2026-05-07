// Remote API base and storage base — configure here
const API_BASE = 'http://localhost:8080'; // api backend
const STORAGE_BASE = 'https://your-storage.example.com/'; // img and books storage

document.addEventListener('DOMContentLoaded', () => {
    const newPanel = document.getElementById('new-books');
    const topPanel = document.getElementById('top-books');
    if (!newPanel && !topPanel) return;

    const newCase = newPanel ? newPanel.querySelector('.books-case') : null;
    const topCase = topPanel ? topPanel.querySelector('.books-case') : null;

    // API_BASE and STORAGE_BASE are defined in module scope above

    if (newCase) newCase.innerHTML = '<p class="loader">Загрузка...</p>';
    if (topCase) topCase.innerHTML = '<p class="loader">Загрузка...</p>';

    const MAX_CARDS = 100;

    const primaryUrl = API_BASE ? (API_BASE.replace(/\/$/, '') + '/books') : '/books';

    function handleJson(json) {
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
    }

    // Try primary URL (API_BASE); on network error, retry relative '/books'
    fetch(primaryUrl)
        .then(res => {
            const ct = (res.headers.get('content-type') || '').toLowerCase();
            if (!res.ok) throw new Error('Network response not ok: ' + res.status);
            if (!ct.includes('application/json')) throw new Error('Invalid content-type: ' + ct);
            return res.json();
        })
        .then(handleJson)
        .catch(err => {
            console.error('Primary fetch failed:', err, 'Trying fallback /books');
            if (primaryUrl !== '/books') {
                fetch('/books')
                    .then(res => {
                        const ct = (res.headers.get('content-type') || '').toLowerCase();
                        if (!res.ok) throw new Error('Network response not ok: ' + res.status);
                        if (!ct.includes('application/json')) throw new Error('Invalid content-type: ' + ct);
                        return res.json();
                    })
                    .then(handleJson)
                    .catch(err2 => {
                        console.error('Fallback fetch failed:', err2);
                        if (newCase) newCase.innerHTML = '<p class="error">Ошибка сети при загрузке книг.</p>';
                        if (topCase) topCase.innerHTML = '<p class="error">Ошибка сети при загрузке книг.</p>';
                    });
            } else {
                if (newCase) newCase.innerHTML = '<p class="error">Ошибка сети при загрузке книг.</p>';
                if (topCase) topCase.innerHTML = '<p class="error">Ошибка сети при загрузке книг.</p>';
            }
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
        // If cover URL is already absolute (http/https), use it.
        // Otherwise, if a STORAGE_BASE is configured, prepend it. Fallback to local placeholder.
        const cover = book.book_cover_url ? book.book_cover_url.trim() : '';
        
        img.src = 'images/placeholder-book-cover.jpg';

        // If remote cover fails to load, replace with placeholder (avoid infinite loop)
        img.onerror = () => {
            if (!img.src.endsWith('placeholder-book-cover.jpg')) {
                img.src = 'images/placeholder-book-cover.jpg';
            }
        };

        const name = document.createElement('p');
        name.className = 'book-name';
        name.textContent = book.book_title || 'Без названия';

        const author = document.createElement('p');
        author.className = 'book-author';
        author.textContent = book.author_name || 'Неизвестный автор';

        card.appendChild(img);
        card.appendChild(name);
        card.appendChild(author);

        const bookId = book.id || book.book_id || book.book_slug || book.slug || '';
        if (bookId) {
            card.dataset.bookId = bookId;
            card.style.cursor = 'pointer';
            card.addEventListener('click', () => {
                const target = 'book-page.html?id=' + encodeURIComponent(bookId);
                window.location.href = target;
            });
        } else {
            // Fallback: navigate by title if no id available
            const fallback = (book.book_title || '').trim();
            if (fallback) {
                card.style.cursor = 'pointer';
                card.addEventListener('click', () => {
                    const slug = fallback.replace(/\s+/g, '-').toLowerCase();
                    window.location.href = 'book-page.html?title=' + encodeURIComponent(slug);
                });
            }
        }

        container.appendChild(card);
    });
}
