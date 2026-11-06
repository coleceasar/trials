(() => {
    const normalize = (value) => value.trim().toLocaleLowerCase();
    const styles = document.createElement('style');
    styles.textContent = `
        .list-filter-controls {
            display: flex;
            align-items: end;
            flex-wrap: wrap;
            gap: 12px;
            margin: 16px 0;
        }
        .list-filter-field {
            display: grid;
            gap: 5px;
            min-width: 200px;
            color: #1f2937;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .list-filter-input {
            box-sizing: border-box;
            min-height: 40px;
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background: #fff;
            color: #1f2937;
            font: inherit;
        }
        .list-filter-actions {
            display: flex;
            gap: 8px;
        }
        .list-filter-button {
            width: auto;
            min-height: 40px;
            margin: 0;
            padding: 8px 14px;
            border: 1px solid #1d4ed8;
            border-radius: 6px;
            background: #1d4ed8;
            color: #fff;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
        }
        .list-filter-button.list-filter-clear {
            border-color: #64748b;
            background: #fff;
            color: #334155;
        }
    `;
    document.head.append(styles);

    document.querySelectorAll('table').forEach((table) => {
        if (table.dataset.listFilter === 'off') {
            return;
        }

        const headers = Array.from(table.querySelectorAll('thead th'));
        const rows = Array.from(table.querySelectorAll('tbody tr'));
        if (headers.length === 0 || rows.length === 0) {
            return;
        }

        const statusColumn = headers.findIndex((header) => /status/i.test(header.textContent || ''));
        const wrapper = document.createElement('div');
        wrapper.className = 'list-filter-controls';

        const searchLabel = document.createElement('label');
        searchLabel.className = 'list-filter-field';
        searchLabel.textContent = 'Search';
        const searchInput = document.createElement('input');
        searchInput.type = 'search';
        searchInput.className = 'list-filter-input';
        searchInput.placeholder = 'Search this list';
        searchInput.setAttribute('aria-label', 'Search this list');
        searchLabel.append(searchInput);
        wrapper.append(searchLabel);

        let statusSelect = null;
        if (statusColumn !== -1) {
            const statusLabel = document.createElement('label');
            statusLabel.className = 'list-filter-field';
            statusLabel.textContent = 'Status';
            statusSelect = document.createElement('select');
            statusSelect.className = 'list-filter-input';
            statusSelect.setAttribute('aria-label', 'Filter by status');

            const allOption = document.createElement('option');
            allOption.value = '';
            allOption.textContent = 'All statuses';
            statusSelect.append(allOption);

            const statuses = new Map();
            rows.forEach((row) => {
                const cell = row.cells[statusColumn];
                const value = cell ? cell.textContent.trim() : '';
                const key = normalize(value);
                if (key && !statuses.has(key)) {
                    statuses.set(key, value);
                }
            });

            Array.from(statuses.entries())
                .sort((left, right) => left[1].localeCompare(right[1]))
                .forEach(([key, value]) => {
                    const option = document.createElement('option');
                    option.value = key;
                    option.textContent = value;
                    statusSelect.append(option);
                });

            statusLabel.append(statusSelect);
            wrapper.append(statusLabel);

            const defaultStatus = normalize(table.dataset.defaultStatus || '');
            if (defaultStatus && statuses.has(defaultStatus)) {
                statusSelect.value = defaultStatus;
            }
        }

        const applyButton = document.createElement('button');
        applyButton.type = 'button';
        applyButton.className = 'list-filter-button';
        applyButton.textContent = 'Filter';

        const clearButton = document.createElement('button');
        clearButton.type = 'button';
        clearButton.className = 'list-filter-button list-filter-clear';
        clearButton.textContent = 'Clear';

        const actions = document.createElement('div');
        actions.className = 'list-filter-actions';
        actions.append(applyButton, clearButton);
        wrapper.append(actions);

        const applyFilters = () => {
            const searchTerm = normalize(searchInput.value);
            const selectedStatus = statusSelect ? statusSelect.value : '';

            rows.forEach((row) => {
                const matchesSearch = normalize(row.textContent || '').includes(searchTerm);
                const statusCell = statusColumn === -1 ? null : row.cells[statusColumn];
                const matchesStatus = !selectedStatus
                    || (statusCell && normalize(statusCell.textContent || '') === selectedStatus);
                row.hidden = !(matchesSearch && matchesStatus);
            });
        };

        applyButton.addEventListener('click', applyFilters);
        searchInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                applyFilters();
            }
        });
        clearButton.addEventListener('click', () => {
            searchInput.value = '';
            if (statusSelect) {
                statusSelect.value = '';
            }
            applyFilters();
        });

        table.before(wrapper);
        if (statusSelect && statusSelect.value) {
            applyFilters();
        }
    });
})();
