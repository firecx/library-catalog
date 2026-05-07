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

function formatDateToDDMMYYYY(raw) {
    if (!raw) return '';
    try {
        let t = String(raw).trim();
        if (/^\d{4}-\d{2}-\d{2} /.test(t)) {
            t = t.replace(' ', 'T');
        }
        const d = new Date(t);
        if (isNaN(d.getTime())) return '';
        const dd = String(d.getDate()).padStart(2, '0');
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const yyyy = d.getFullYear();
        return `${dd}.${mm}.${yyyy}`;
    } catch (e) {
        return '';
    }
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
            const rawDate = b.last_text_update || b.created_at || '';
            setText('update-date', rawDate ? formatDateToDDMMYYYY(rawDate) : '');
            setText('book-tags', Array.isArray(b.genres) ? b.genres.join(', ') : '');

            // Cover
            const coverImg = document.querySelector('.book-line img') || document.querySelector('.book-panel img');
            if (coverImg) {
                coverImg.src = 'images/placeholder-book-cover.jpg';
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
                    const rawAnn = String(b.annotation);
                    // If the annotation already contains HTML tags, render as HTML; otherwise escape.
                    const looksLikeHtml = /<\s*\/?\s*[a-zA-Z]+[^>]*>/.test(rawAnn);
                    if (looksLikeHtml) {
                        ann.innerHTML = rawAnn;
                    } else {
                        const safe = escapeHtml(rawAnn).replace(/\r?\n/g, '<br>');
                        ann.innerHTML = `<p>${safe}</p>`;
                    }
                } else {
                    ann.innerHTML = '<p>Аннотация отсутствует.</p>';
                }
            }

            // Author card: load author details if author_id present and fill the author card
            const authorInfoNameEl = document.getElementById('author-info-name');
            const briefAuthorNameEl = document.getElementById('author-name');
            const authorBooksCountEl = document.querySelector('.author-statistics-books p');
            const applyAuthorData = (a) => {
                const name = (a && a.author_name) || b.author_name || '';
                const count = (a && (a.book_count != null)) ? String(a.book_count) : ((b.book_count != null) ? String(b.book_count) : '');
                if (authorInfoNameEl) authorInfoNameEl.textContent = name;
                if (briefAuthorNameEl) briefAuthorNameEl.textContent = name;
                if (authorBooksCountEl) authorBooksCountEl.textContent = count;
            };

            if (b.author_id) {
                const authorUrl = `${base}/authors/${encodeURIComponent(b.author_id)}`;
                fetch(authorUrl)
                    .then(res => {
                        if (!res.ok) throw new Error('Network response not ok: ' + res.status);
                        return res.json();
                    })
                    .then(js => {
                        if (!js || !js.success || !js.data) {
                            console.error('Author not found or API error', js);
                            applyAuthorData(null);
                            return;
                        }
                        applyAuthorData(js.data);
                    })
                    .catch(err => {
                        console.error('Failed to load author:', err);
                        applyAuthorData(null);
                    });
            } else {
                applyAuthorData(null);
            }

            // Load author's other books and render into .books-line (exclude current book)
            const booksLineEl = document.querySelector('.authors-books .books-line');
            const renderAuthorBooks = (items) => {
                if (!booksLineEl) return;
                booksLineEl.innerHTML = '';
                const other = Array.isArray(items) ? items.filter(it => String(it.book_id) !== String(b.book_id)) : [];
                if (other.length === 0) {
                    const msg = document.createElement('p');
                    msg.textContent = 'Других книг автора не найдено.';
                    booksLineEl.appendChild(msg);
                    return;
                }
                other.forEach(item => {
                    const link = document.createElement('a');
                    link.className = 'book-card';
                    link.href = `book-page.html?id=${encodeURIComponent(item.book_id)}`;

                    const img = document.createElement('img');
                    img.className = 'book-cover';
                    img.src = 'images/placeholder-book-cover.jpg';
                    img.alt = item.book_title || 'обложка книги';
                    img.onerror = () => {
                        if (!img.src.endsWith('placeholder-book-cover.jpg')) {
                            img.src = 'images/placeholder-book-cover.jpg';
                        }
                    };

                    const info = document.createElement('div');
                    info.className = 'book-card-info';
                    const nameP = document.createElement('p');
                    nameP.className = 'book-name';
                    nameP.innerHTML = escapeHtml(item.book_title || '');
                    const authorP = document.createElement('p');
                    authorP.className = 'book-author';
                    authorP.innerHTML = escapeHtml(item.author_name || '');

                    info.appendChild(nameP);
                    info.appendChild(authorP);
                    link.appendChild(img);
                    link.appendChild(info);
                    booksLineEl.appendChild(link);
                });
            };

            if (b.author_id) {
                const booksUrl = `${base}/authors/${encodeURIComponent(b.author_id)}/books`;
                fetch(booksUrl)
                    .then(res => {
                        if (!res.ok) throw new Error('Network response not ok: ' + res.status);
                        return res.json();
                    })
                    .then(js => {
                        if (!js || !js.success || !js.data) {
                            console.error('Author books not found or API error', js);
                            renderAuthorBooks([]);
                            return;
                        }
                        renderAuthorBooks(js.data);
                    })
                    .catch(err => {
                        console.error('Failed to load author books:', err);
                        renderAuthorBooks([]);
                    });
            } else {
                renderAuthorBooks([]);
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
