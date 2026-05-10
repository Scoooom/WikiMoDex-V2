{{-- Shared gallery pagination + search JS --}}
{{-- Include at bottom of any gallery view that uses .gallery-table + .gallery-wrap --}}
<script>
(function() {
    const PER_PAGE = 25;
    let rows = [], filtered = [], currentPage = 1, sortCol = -1, sortDir = 1;

    function init() {
        const tbody = document.querySelector('.gallery-table tbody');
        if (!tbody) return;
        rows = Array.from(tbody.querySelectorAll('tr'));
        filtered = [...rows];
        render();

        const search = document.getElementById('gallery-search');
        if (search) {
            search.addEventListener('input', () => {
                const q = search.value.toLowerCase().trim();
                filtered = q
                    ? rows.filter(r => r.dataset.search && r.dataset.search.includes(q))
                    : [...rows];
                currentPage = 1;
                render();
            });
        }

        document.querySelectorAll('.gallery-table th[data-col]').forEach(th => {
            th.addEventListener('click', () => {
                const col = parseInt(th.dataset.col);
                if (sortCol === col) sortDir *= -1;
                else { sortCol = col; sortDir = 1; }
                document.querySelectorAll('.gallery-table th').forEach(h => h.classList.remove('sorted-asc','sorted-desc'));
                th.classList.add(sortDir === 1 ? 'sorted-asc' : 'sorted-desc');
                filtered.sort((a, b) => {
                    const av = (a.querySelectorAll('td')[col]?.dataset.sort ?? a.querySelectorAll('td')[col]?.textContent ?? '').trim().toLowerCase();
                    const bv = (b.querySelectorAll('td')[col]?.dataset.sort ?? b.querySelectorAll('td')[col]?.textContent ?? '').trim().toLowerCase();
                    return av < bv ? -sortDir : av > bv ? sortDir : 0;
                });
                currentPage = 1;
                render();
            });
        });
    }

    function render() {
        const tbody = document.querySelector('.gallery-table tbody');
        const total = filtered.length;
        const totalPages = Math.max(1, Math.ceil(total / PER_PAGE));
        currentPage = Math.min(currentPage, totalPages);
        const start = (currentPage - 1) * PER_PAGE;
        const page = filtered.slice(start, start + PER_PAGE);

        tbody.innerHTML = '';
        if (page.length === 0) {
            tbody.innerHTML = '<tr><td colspan="99" style="text-align:center;color:var(--dim);padding:24px">No results found</td></tr>';
        } else {
            page.forEach(r => tbody.appendChild(r));
        }

        const info = document.getElementById('pag-info');
        if (info) info.textContent = total === 0
            ? 'No results'
            : `Showing ${start + 1}–${Math.min(start + PER_PAGE, total)} of ${total}`;

        renderPagination(totalPages);
    }

    function renderPagination(totalPages) {
        const container = document.getElementById('pag-btns');
        if (!container) return;
        const btns = [];

        const prev = document.createElement('button');
        prev.className = 'pag-btn';
        prev.textContent = '‹ Prev';
        prev.disabled = currentPage === 1;
        prev.onclick = () => { currentPage--; render(); };
        btns.push(prev);

        const pages = pageNumbers(currentPage, totalPages);
        let lastN = null;
        pages.forEach(n => {
            if (lastN !== null && n - lastN > 1) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'pag-btn';
                ellipsis.textContent = '…';
                ellipsis.style.cursor = 'default';
                btns.push(ellipsis);
            }
            const btn = document.createElement('button');
            btn.className = 'pag-btn' + (n === currentPage ? ' active' : '');
            btn.textContent = n;
            btn.onclick = () => { currentPage = n; render(); };
            btns.push(btn);
            lastN = n;
        });

        const next = document.createElement('button');
        next.className = 'pag-btn';
        next.textContent = 'Next ›';
        next.disabled = currentPage === totalPages;
        next.onclick = () => { currentPage++; render(); };
        btns.push(next);

        container.innerHTML = '';
        btns.forEach(b => container.appendChild(b));
    }

    function pageNumbers(current, total) {
        if (total <= 7) return Array.from({length: total}, (_, i) => i + 1);
        const set = new Set([1, 2, total - 1, total, current - 1, current, current + 1].filter(n => n >= 1 && n <= total));
        return [...set].sort((a, b) => a - b);
    }

    document.addEventListener('DOMContentLoaded', init);
})();
</script>
