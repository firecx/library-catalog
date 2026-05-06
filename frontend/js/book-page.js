// Book page data loader
const API_BASE = 'http://localhost:8080';

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const id = params.get('id');
    const titleParam = params.get('title');

    if (!id && !titleParam) return;

    const base = API_BASE.replace(/\/$/, '');
    const url = id ? `${base}/books/${encodeURIComponent(id)}` : `${base}/books?title=${encodeURIComponent(titleParam)}`;

    fetch(url)
        .then(res => {
            if (!res.ok) throw new Error('Network response not ok: ' + res.status);
            return res.json();
        })
        .then(json => {
            if (!json || !json.success || !json.data) {
                console.error('Book not found or API error', json);
                const titleEl = document.getElementById('book-title');
                if (titleEl) titleEl.textContent = 'Книга не найдена';
                return;
            }
            const b = json.data;

            const setText = (id, value) => {
                const el = document.getElementById(id);
                if (el) el.textContent = value || '';
            };

            setText('book-title', b.book_title || 'Без названия');
            setText('author-name', b.author_name || '');
            setText('book-series', b.series_name ? 'Цикл: ' + b.series_name : '');
            setText('book-status', b.book_status === 'completed' ? 'Весь текст' : (b.book_status || ''));
            setText('update-date', b.last_text_update || b.created_at || '');
            setText('book-tags', Array.isArray(b.genres) ? b.genres.join(', ') : '');

            // Cover
            const coverImg = document.querySelector('.book-line img') || document.querySelector('.book-panel img');
            if (coverImg) {
                coverImg.src = b.book_cover_url || 'images/placeholder-book-cover.jpg';
                coverImg.alt = b.book_title || 'обложка книги';
                coverImg.onerror = () => {
                    if (!coverImg.src.endsWith('placeholder-book-cover.jpg')) {
                        coverImg.src = 'images/placeholder-book-cover.jpg';
                    }
                };
            }

            // Annotation
            const ann = document.getElementById('annotation-case');
            if (ann) {
                if (b.annotation) {
                    const safe = escapeHtml(b.annotation).replace(/\r?\n/g, '<br>');
                    ann.innerHTML = `<p>${safe}</p>`;
                } else {
                    ann.innerHTML = '<p>Аннотация отсутствует.</p>';
                }
            }

            // Optionally set read/download buttons if URLs provided
            const readBtn = document.getElementById('read-book-button');
            const dlBtn = document.getElementById('download-book-button');
            if (readBtn) {
                if (b.read_url) {
                    readBtn.addEventListener('click', () => window.location.href = b.read_url);
                } else {
                    readBtn.disabled = true;
                }
            }
            if (dlBtn) {
                if (b.download_url) {
                    dlBtn.addEventListener('click', () => window.location.href = b.download_url);
                } else {
                    dlBtn.disabled = true;
                }
            }
        })
        .catch(err => {
            console.error('Failed to load book:', err);
            const titleEl = document.getElementById('book-title');
            if (titleEl) titleEl.textContent = 'Ошибка загрузки данных';
        });
});
