@php
// Filter and search JavaScript
// Extracted to keep the main file clean
@endphp
<script>
(function() {
    const list = document.getElementById('contacts-list');
    if (!list) {
        console.error('Table list element (contacts-list) not found');
        return;
    }

    // Filter inputs
    const filterName = document.getElementById('filter-name');
    const filterEmail = document.getElementById('filter-email');
    const filterGender = document.getElementById('filter-gender');
    const clearFiltersBtn = document.getElementById('clear-filters');
    
    // Legacy search (for backward compatibility)
    const searchInput = document.getElementById('search');
    const clearBtn = document.getElementById('clear-search');
    const spinner = document.getElementById('search-spinner');

    let debounceTimer = null;
    let controller = null;

    function showSpinner(show) {
        if (!spinner) return;
        if (show) {
            spinner.classList.remove('hidden');
            spinner.classList.add('inline-flex');
        } else {
            spinner.classList.remove('inline-flex');
            spinner.classList.add('hidden');
        }
    }

    function updateClearBtn() {
        if (!clearBtn || !searchInput) return;
        if (searchInput.value.length > 0) {
            clearBtn.classList.remove('hidden');
            clearBtn.classList.add('inline-flex');
        } else {
            clearBtn.classList.remove('inline-flex');
            clearBtn.classList.add('hidden');
        }
    }

    function collectFilters() {
        const filters = {};

        if (filterName && filterName.value.trim()) {
            filters.name = filterName.value.trim();
        }

        if (filterEmail && filterEmail.value.trim()) {
            filters.email = filterEmail.value.trim();
        }

        if (filterGender && filterGender.value !== '') {
            filters.gender = filterGender.value;
        }

        // Custom fields filters
        const customFieldInputs = document.querySelectorAll('[data-custom-field-id]');
        const customFields = {};
        customFieldInputs.forEach(input => {
            const fieldId = input.getAttribute('data-custom-field-id');
            const fieldType = input.getAttribute('data-field-type');
            if (input.value && input.value.trim()) {
                let value = input.value.trim();
                
                // Convert date from dd/mm/yyyy to Y-m-d format for date fields
                if (fieldType === 'date' && /^\d{2}\/\d{2}\/\d{4}$/.test(value)) {
                    const parts = value.split('/');
                    if (parts.length === 3) {
                        value = parts[2] + '-' + parts[1] + '-' + parts[0]; // Y-m-d format
                    }
                }
                
                customFields[fieldId] = value;
            }
        });
        if (Object.keys(customFields).length > 0) {
            filters.custom_fields = customFields;
        }

        // Legacy search (only if no other filters are set)
        if (searchInput && searchInput.value.trim() && Object.keys(filters).length === 0) {
            filters.search = searchInput.value.trim();
        }

        return filters;
    }

    async function doFilter() {
        const filters = collectFilters();
        console.debug('[filter] applying filters:', filters);

        if (controller) controller.abort();
        controller = new AbortController();
        const signal = controller.signal;

        showSpinner(true);

        try {
            const url = new URL("{{ route('contacts.index') }}", window.location.origin);
            
            Object.keys(filters).forEach(key => {
                if (key === 'custom_fields') {
                    Object.keys(filters[key]).forEach(fieldId => {
                        url.searchParams.append(`custom_fields[${fieldId}]`, filters[key][fieldId]);
                    });
                } else {
                    url.searchParams.set(key, filters[key]);
                }
            });

            const resp = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                },
                signal,
            });

            const text = await resp.text();
            console.debug('[filter] status', resp.status, 'response length', text.length);

            if (resp.status === 200 && text.indexOf('<tr') !== -1) {
                list.innerHTML = text;
                return;
            }

            if (resp.status === 401 || resp.status === 419) {
                console.warn('[filter] auth issue, reloading');
                location.reload();
                return;
            }

            if (text.indexOf('<html') !== -1 || text.indexOf('<!doctype') !== -1) {
                console.warn('[filter] full HTML received, possible redirect');
                list.innerHTML = '<tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">Session may have expired. Please reload the page.</td></tr>';
                return;
            }

            console.warn('[filter] unexpected response');
            list.innerHTML = '<tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">No results found.</td></tr>';

        } catch (err) {
            if (err.name === 'AbortError') {
                console.debug('[filter] request aborted');
                return;
            }
            console.error('[filter] fetch error', err);
            if (list) {
                list.innerHTML = '<tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">Filter failed. Try again later.</td></tr>';
            }
        } finally {
            showSpinner(false);
        }
    }

    function debouncedFilter() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => doFilter(), 350);
    }

    // Attach event listeners
    if (filterName) {
        filterName.addEventListener('input', debouncedFilter);
    }

    if (filterEmail) {
        filterEmail.addEventListener('input', debouncedFilter);
    }

    if (filterGender) {
        filterGender.addEventListener('change', doFilter);
    }

    const customFieldInputs = document.querySelectorAll('[data-custom-field-id]');
    customFieldInputs.forEach(input => {
        input.addEventListener('input', debouncedFilter);
    });

    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            if (filterName) filterName.value = '';
            if (filterEmail) filterEmail.value = '';
            if (filterGender) filterGender.value = '';
            if (searchInput) searchInput.value = '';
            
            customFieldInputs.forEach(input => {
                input.value = '';
            });

            updateClearBtn();
            doFilter();
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            updateClearBtn();
            if ((!filterName || !filterName.value.trim()) && 
                (!filterEmail || !filterEmail.value.trim()) && 
                (!filterGender || filterGender.value === '')) {
                debouncedFilter();
            }
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(debounceTimer);
                doFilter();
            }
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            if (searchInput) {
                searchInput.value = '';
                updateClearBtn();
                doFilter();
                searchInput.focus();
            }
        });
    }

    updateClearBtn();

    // Date input formatting (dd/mm/yyyy)
    function formatDateInput(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2);
        }
        if (value.length >= 5) {
            value = value.substring(0, 5) + '/' + value.substring(5, 9);
        }
        input.value = value;
    }

    document.querySelectorAll('.date-input').forEach(input => {
        input.addEventListener('input', function(e) {
            formatDateInput(e.target);
        });

        input.addEventListener('blur', function(e) {
            const value = e.target.value;
            if (value && !/^\d{2}\/\d{2}\/\d{4}$/.test(value)) {
                const parts = value.split('/');
                if (parts.length === 3) {
                    const day = parts[0].padStart(2, '0');
                    const month = parts[1].padStart(2, '0');
                    const year = parts[2];
                    if (day && month && year && year.length === 4) {
                        e.target.value = `${day}/${month}/${year}`;
                    }
                }
            }
        });
    });
})();
</script>

