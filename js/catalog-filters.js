(() => {
    const normalize = (value) => String(value || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');

    document.querySelectorAll('[data-filter-catalog]').forEach((catalog) => {
        const filterButtons = Array.from(catalog.querySelectorAll('[data-filter-type]'));
        const cards = Array.from(catalog.querySelectorAll('[data-filter-card]'));
        const groups = Array.from(catalog.querySelectorAll('[data-filter-group]'));
        const countTarget = catalog.querySelector('[data-filter-count]');
        const countPanelTarget = catalog.querySelector('[data-filter-count-panel]');
        const resetButton = catalog.querySelector('[data-filter-reset]');
        const emptyState = catalog.querySelector('[data-filter-empty]');
        const filterShell = catalog.querySelector('.catalog-filter-toolbar');
        const toggle = catalog.querySelector('[data-filter-toggle]');
        const panel = catalog.querySelector('[data-filter-panel]');
        const backdrop = catalog.querySelector('[data-filter-backdrop]');
        const closeButton = catalog.querySelector('[data-filter-close]');
        const applyButton = catalog.querySelector('[data-filter-apply]');
        const activeCount = catalog.querySelector('[data-filter-active-count]');
        const chipsTarget = catalog.querySelector('[data-filter-chips]');
        const sortSelect = catalog.querySelector('[data-sort-select]');
        const sortLabel = catalog.querySelector('[data-sort-label]');
        const sortSummary = catalog.querySelector('.catalog-sort-summary');
        const availabilityToggle = catalog.querySelector('[data-availability-toggle]');
        const priceFilter = catalog.querySelector('[data-price-filter]');
        const priceRange = catalog.querySelector('[data-price-range]');
        const priceMinInput = catalog.querySelector('[data-price-min]');
        const priceMaxInput = catalog.querySelector('[data-price-max]');
        const singular = catalog.dataset.filterSingular || 'resultado';
        const plural = catalog.dataset.filterPlural || 'resultados';
        const filters = {};
        const state = {
            availableOnly: false,
            sort: sortSelect?.value || 'relevance',
            priceMin: Number(priceFilter?.dataset.priceDefaultMin || 0),
            priceMax: Number(priceFilter?.dataset.priceDefaultMax || Number.MAX_SAFE_INTEGER),
            defaultPriceMin: Number(priceFilter?.dataset.priceDefaultMin || 0),
            defaultPriceMax: Number(priceFilter?.dataset.priceDefaultMax || Number.MAX_SAFE_INTEGER),
        };

        if (!cards.length) {
            return;
        }

        filterButtons.forEach((button) => {
            const type = button.dataset.filterType;
            if (!type || Object.prototype.hasOwnProperty.call(filters, type)) {
                return;
            }

            const activeButton = filterButtons.find((item) => item.dataset.filterType === type && item.classList.contains('is-active'));
            filters[type] = activeButton?.dataset.filterValue || 'all';
        });

        const getButtonLabel = (button) => {
            return button.querySelector('.catalog-filter-text')?.textContent?.trim() || button.textContent.trim();
        };

        const setPanelOpen = (isOpen) => {
            if (!toggle || !panel) {
                return;
            }

            panel.hidden = !isOpen;
            backdrop && (backdrop.hidden = !isOpen);
            document.body.classList.toggle('filters-open', isOpen);
            toggle.classList.toggle('is-open', isOpen);
            toggle.setAttribute('aria-expanded', String(isOpen));
        };

        const updateButtons = (type, value) => {
            filterButtons
                .filter((button) => button.dataset.filterType === type)
                .forEach((button) => {
                    const isActive = button.dataset.filterValue === value;
                    button.classList.toggle('is-active', isActive);
                    button.setAttribute('aria-pressed', String(isActive));
                });
        };

        const getActiveFilterLabels = () => {
            const labels = [];

            Object.entries(filters).forEach(([type, value]) => {
                if (!value || value === 'all') {
                    return;
                }

                const button = filterButtons.find((item) => item.dataset.filterType === type && item.dataset.filterValue === value);
                labels.push({
                    key: type,
                    label: getButtonLabel(button),
                    onRemove: () => {
                        filters[type] = 'all';
                        updateButtons(type, 'all');
                        applyFilters();
                    },
                });
            });

            if (state.availableOnly) {
                labels.push({
                    key: 'available',
                    label: availabilityToggle ? availabilityToggle.closest('.catalog-switch-row')?.querySelector('span')?.textContent?.trim() || 'Disponibles' : 'Disponibles',
                    onRemove: () => {
                        state.availableOnly = false;
                        availabilityToggle?.setAttribute('aria-pressed', 'false');
                        availabilityToggle?.classList.remove('is-active');
                        applyFilters();
                    },
                });
            }

            if (priceFilter && (state.priceMin !== state.defaultPriceMin || state.priceMax !== state.defaultPriceMax)) {
                labels.push({
                    key: 'price',
                    label: `S/ ${state.priceMin} - S/ ${state.priceMax}`,
                    onRemove: () => {
                        setPriceValues(state.defaultPriceMin, state.defaultPriceMax);
                        applyFilters();
                    },
                });
            }

            return labels;
        };

        const updateActiveCount = () => {
            const count = getActiveFilterLabels().length;

            if (activeCount) {
                activeCount.textContent = count;
                activeCount.hidden = count === 0;
            }
        };

        const updateChips = () => {
            if (!chipsTarget) {
                return;
            }

            chipsTarget.replaceChildren();

            getActiveFilterLabels().forEach((item) => {
                const chip = document.createElement('button');
                chip.type = 'button';
                chip.className = 'catalog-filter-chip';
                chip.innerHTML = `<span>${item.label}</span><i class="bi bi-x-lg" aria-hidden="true"></i>`;
                chip.addEventListener('click', item.onRemove);
                chipsTarget.appendChild(chip);
            });
        };

        const updateSortLabel = () => {
            if (!sortSelect || !sortLabel) {
                return;
            }

            sortLabel.textContent = sortSelect.options[sortSelect.selectedIndex]?.textContent || 'Más relevantes';
        };

        const sortCards = () => {
            const compare = (a, b) => {
                if (state.sort === 'alpha-asc' || state.sort === 'alpha-desc') {
                    const result = normalize(a.dataset.title).localeCompare(normalize(b.dataset.title));
                    return state.sort === 'alpha-asc' ? result : -result;
                }

                if (state.sort === 'price-asc' || state.sort === 'price-desc') {
                    const result = Number(a.dataset.price || 0) - Number(b.dataset.price || 0);
                    return state.sort === 'price-asc' ? result : -result;
                }

                if (state.sort === 'date-asc' || state.sort === 'date-desc') {
                    const result = new Date(a.dataset.date || 0).getTime() - new Date(b.dataset.date || 0).getTime();
                    return state.sort === 'date-asc' ? result : -result;
                }

                return Number(a.dataset.order || 0) - Number(b.dataset.order || 0);
            };

            const sortableGroups = groups.length ? groups : [catalog];

            sortableGroups.forEach((group) => {
                const grid = group.querySelector('.product-catalog-grid, .catalog-content-grid');
                if (!grid) {
                    return;
                }

                Array.from(grid.querySelectorAll('[data-filter-card]'))
                    .sort(compare)
                    .forEach((card) => grid.appendChild(card));
            });
        };

        const updateGroupCounts = () => {
            groups.forEach((group) => {
                const groupCards = cards.filter((card) => group.contains(card));
                const visibleInGroup = groupCards.filter((card) => !card.hidden).length;
                const sectionCount = group.querySelector('[data-section-count]');
                group.hidden = visibleInGroup === 0;

                if (sectionCount) {
                    sectionCount.textContent = `${visibleInGroup} ${visibleInGroup === 1 ? singular : plural}`;
                }
            });
        };

        const clampPrice = (value) => {
            return Math.max(state.defaultPriceMin, Math.min(state.defaultPriceMax, Number(value || 0)));
        };

        function setPriceValues(min, max) {
            const nextMin = clampPrice(min);
            const nextMax = Math.max(nextMin, clampPrice(max));

            state.priceMin = nextMin;
            state.priceMax = nextMax;

            if (priceMinInput) {
                priceMinInput.value = nextMin;
            }

            if (priceMaxInput) {
                priceMaxInput.value = nextMax;
            }

            if (priceRange) {
                priceRange.value = nextMax;
            }
        }

        function applyFilters() {
            let visibleTotal = 0;

            sortCards();

            cards.forEach((card) => {
                const matchesFilters = Object.entries(filters).every(([type, value]) => {
                    return value === 'all' || card.dataset[type] === value;
                });
                const matchesAvailability = !state.availableOnly || card.dataset.available === 'true';
                const cardPrice = Number(card.dataset.price || 0);
                const matchesPrice = !priceFilter || (cardPrice >= state.priceMin && cardPrice <= state.priceMax);
                const isVisible = matchesFilters && matchesAvailability && matchesPrice;

                card.hidden = !isVisible;

                if (isVisible) {
                    visibleTotal += 1;
                }
            });

            if (countTarget) {
                countTarget.textContent = visibleTotal;
            }

            if (countPanelTarget) {
                countPanelTarget.textContent = visibleTotal;
            }

            if (emptyState) {
                emptyState.hidden = visibleTotal !== 0;
            }

            updateGroupCounts();
            updateActiveCount();
            updateChips();
            updateSortLabel();
        }

        filterButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const type = button.dataset.filterType;
                const value = button.dataset.filterValue;
                filters[type] = filters[type] === value && value !== 'all' ? 'all' : value;
                updateButtons(type, filters[type]);
                applyFilters();
            });
        });

        sortSelect?.addEventListener('change', () => {
            state.sort = sortSelect.value;
            applyFilters();
        });
        sortSummary?.setAttribute('role', 'button');
        sortSummary?.setAttribute('tabindex', '0');
        const openSort = () => { setPanelOpen(true); window.setTimeout(() => sortSelect?.focus(), 120); };
        sortSummary?.addEventListener('click', openSort);
        sortSummary?.addEventListener('keydown', event => { if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); openSort(); } });

        availabilityToggle?.addEventListener('click', () => {
            state.availableOnly = !state.availableOnly;
            availabilityToggle.classList.toggle('is-active', state.availableOnly);
            availabilityToggle.setAttribute('aria-pressed', String(state.availableOnly));
            applyFilters();
        });

        priceMinInput?.addEventListener('input', () => {
            setPriceValues(priceMinInput.value, state.priceMax);
            applyFilters();
        });

        priceMaxInput?.addEventListener('input', () => {
            setPriceValues(state.priceMin, priceMaxInput.value);
            applyFilters();
        });

        priceRange?.addEventListener('input', () => {
            setPriceValues(state.defaultPriceMin, priceRange.value);
            applyFilters();
        });

        catalog.querySelectorAll('[data-section-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const body = button.nextElementSibling;
                const isOpen = button.getAttribute('aria-expanded') === 'true';

                button.setAttribute('aria-expanded', String(!isOpen));
                button.classList.toggle('is-collapsed', isOpen);

                if (body) {
                    body.hidden = isOpen;
                }
            });
        });

        resetButton?.addEventListener('click', () => {
            Object.keys(filters).forEach((type) => {
                filters[type] = 'all';
                updateButtons(type, 'all');
            });

            if (sortSelect) {
                sortSelect.value = 'relevance';
                state.sort = 'relevance';
            }

            state.availableOnly = false;
            availabilityToggle?.classList.remove('is-active');
            availabilityToggle?.setAttribute('aria-pressed', 'false');

            if (priceFilter) {
                setPriceValues(state.defaultPriceMin, state.defaultPriceMax);
            }

            applyFilters();
        });

        toggle?.addEventListener('click', () => {
            setPanelOpen(panel?.hidden ?? true);
        });

        closeButton?.addEventListener('click', () => setPanelOpen(false));
        applyButton?.addEventListener('click', () => setPanelOpen(false));
        backdrop?.addEventListener('click', () => setPanelOpen(false));

        document.addEventListener('click', (event) => {
            if (!panel || panel.hidden || panel.contains(event.target) || filterShell?.contains(event.target)) {
                return;
            }

            setPanelOpen(false);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape' || !panel || panel.hidden) {
                return;
            }

            setPanelOpen(false);
            toggle?.focus();
        });

        applyFilters();
    });
})();
