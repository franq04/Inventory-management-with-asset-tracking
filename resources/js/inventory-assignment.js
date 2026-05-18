import $ from 'jquery';

const csrfToken = () => $('meta[name="csrf-token"]').attr('content');

const formatCurrency = (value) => {
    const number = Number(value ?? 0);
    return `₱${number.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
};

const todayIso = () => new Date().toISOString().slice(0, 10);

$(() => {
    const config = window.inventoryAssignmentConfig || {};
    const routes = config.routes || {};
    const categories = config.categories || [];
    const locations = config.locations || [];

    const $itemsBody = $('#inventoryItemsBody');
    const $search = $('#inventorySearch');
    const $stateFilter = $('#inventoryState');
    const $paginationContainer = $('#paginationContainer');
    const $pageLinks = $('#pageLinks');
    const $pageInfo = $('#pageInfo');
    const $pageMeta = $('#pageMeta');
    const pageQueryKey = 'inventory_page';
    let currentItems = [];
    let currentPage = 1;
    const itemsPerPage = 5;

    const $dateFrom = $('#date_from');
    const $dateTo = $('#date_to');
    const $applyDateBtn = $('#inventoryApplyDate');
    const $printPdfBtn = $('#inventoryPrintPdfBtn');
    const $exportExcelBtn = $('#inventoryExportExcelBtn');
    const $pendingCount = $('#inventoryPendingCount');
    const $recordedCount = $('#inventoryRecordedCount');

    const $createModal = $('#inventoryCreateModal');
    const $createForm = $('#inventoryCreateForm');
    const $createErrors = $('#inventoryCreateErrors');
    const $categoryIdField = $('#inventoryCategoryId');
    const $categoryInputField = $('#inventoryCategoryInput');
    const $categoryList = $('#inventoryCategoryList');
    const $categorySuggestions = $('#inventoryCategorySuggestions');
    const $categorySuggestionsList = $('#inventoryCategorySuggestionsList');
    const $clearCategoryBtn = $('#inventoryClearCategoryBtn');
    const $subCategoryIdField = $('#inventorySubCategoryId');
    const $subCategoryInputField = $('#inventorySubCategoryInput');
    const $subCategoryList = $('#inventorySubCategoryList');
    const $subCategorySuggestions = $('#inventorySubCategorySuggestions');
    const $subCategorySuggestionsList = $('#inventorySubCategorySuggestionsList');
    const $clearSubCategoryBtn = $('#inventoryClearSubCategoryBtn');
    const $descriptionField = $('#inventoryPropertyDescription');
    const $unitField = $('#inventoryUnit');
    const $quantityField = $('#inventoryQuantity');
    const $unitCostField = $('#inventoryUnitCost');
    const $totalCostField = $('#inventoryTotalCost');
    const $dateAcquiredField = $('#inventoryDateAcquired');
    const $usefulLifeValueField = $('#inventoryUsefulLifeValue');
    const $usefulLifeUnitField = $('#inventoryUsefulLifeUnit');
    const $initialLocationIdField = $('#inventoryInitialLocationId');
    const $initialLocationInputField = $('#inventoryInitialLocationInput');
    const $clearLocationBtn = $('#inventoryClearLocationBtn');
    const $initialLocationList = $('#inventoryInitialLocationList');
    const $locationSuggestions = $('#inventoryLocationSuggestions');
    const $locationSuggestionsList = $('#inventoryLocationSuggestionsList');
    const $toggleNewLocationBtn = $('#inventoryToggleNewLocationBtn');
    const $newLocationForm = $('#inventoryNewLocationForm');
    const $newLocationNameField = $('#inventoryInitialLocationName');
    const $newLocationTypeField = $('#inventoryInitialLocationType');
    const $newLocationParentField = $('#inventoryInitialLocationParent');
    const $addLocationBtn = $('#inventoryAddLocationBtn');
    const $initialCustodianField = $('#inventoryInitialCustodian');
    const $serialsList = $('#inventorySerialsList');

    const $itemDescription = $('#inventoryItemDescription');
    const $itemSource = $('#inventoryItemSource');
    const $itemAccepted = $('#inventoryItemAccepted');
    const $itemSupplier = $('#inventoryItemSupplier');
    const $itemAccountableOfficer = $('#inventoryAccountableOfficer');

    const $viewModal = $('#inventoryViewModal');
    const $successModal = $('#inventorySuccessModal');
    const $successMessage = $('#inventorySuccessMessage');
    const $feedbackModal = $('#inventoryFeedbackModal');
    const $feedbackTitle = $('#inventoryFeedbackTitle');
    const $feedbackSubtitle = $('#inventoryFeedbackSubtitle');
    const $feedbackMessage = $('#inventoryFeedbackMessage');
    const $feedbackIcon = $('#inventoryFeedbackIcon');
    const $feedbackClose = $('#inventoryFeedbackClose');
    const $viewPropertyNo = $('#inventoryViewPropertyNo');
    const $viewSource = $('#inventoryViewSource');
    const $viewDateAcquired = $('#inventoryViewDateAcquired');
    const $viewDescription = $('#inventoryViewDescription');
    const $viewCategory = $('#inventoryViewCategory');
    const $viewSubCategory = $('#inventoryViewSubCategory');
    const $viewQuantity = $('#inventoryViewQuantity');
    const $viewUnitCost = $('#inventoryViewUnitCost');
    const $viewTotalCost = $('#inventoryViewTotalCost');
    const $viewOfficer = $('#inventoryViewOfficer');
    const $viewUsefulLife = $('#inventoryViewUsefulLife');
    const $viewSerials = $('#inventoryViewSerials');
    const $viewRemarks = $('#inventoryViewRemarks');
    const $viewDocumentBadge = $('#inventoryViewDocumentBadge');
    const $viewDocumentMeta = $('#inventoryViewDocumentMeta');
    const $viewDocumentExtra = $('#inventoryViewDocumentExtra');
    const $viewPoNo = $('#inventoryViewPoNo');
    const $viewIaNo = $('#inventoryViewIaNo');
    const $viewPrNo = $('#inventoryViewPrNo');
    const $viewSupplier = $('#inventoryViewSupplier');

    let fetchTimeout = null;
    let currentStoreUrl = null;
    let appliedDateFrom = null;
    let appliedDateTo = null;
    let currentLocationSearchTerm = '';

    const escapeHtml = (value = '') => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const escapeAttr = (value = '') => escapeHtml(value);

    const normalizeLocationText = (value = '') => String(value || '')
        .trim()
        .replace(/\s+/g, ' ')
        .toLowerCase();

    const normalizeText = (value = '') => String(value || '')
        .trim()
        .replace(/\s+/g, ' ')
        .toLowerCase();

    const parseUsefulLife = (value = '') => {
        const raw = String(value || '').trim();
        if (!raw) {
            return { amount: '', unit: 'years' };
        }

        const match = raw.match(/(\d+(?:\.\d+)?)/);
        if (!match) {
            return { amount: '', unit: 'years' };
        }

        const isMonths = /\b(month|months|mo|mos|mth|mths)\b/i.test(raw);
        return {
            amount: match[1],
            unit: isMonths ? 'months' : 'years',
        };
    };

    const getLocationLabel = (location) => String(location?.label || location?.name || '').trim();

    const getLocationById = (locationId) => {
        const target = String(locationId || '').trim();
        if (!target) {
            return null;
        }

        return locations.find((location) => String(location.id || '') === target) || null;
    };

    const getLocationByInput = (inputValue) => {
        const normalized = normalizeLocationText(inputValue);
        if (!normalized) {
            return null;
        }

        return locations.find((location) => {
            const label = normalizeLocationText(getLocationLabel(location));
            const name = normalizeLocationText(location?.name || '');
            return normalized === label || normalized === name;
        }) || null;
    };

    const getCategoryById = (categoryId) => {
        const target = String(categoryId || '').trim();
        if (!target) {
            return null;
        }

        return categories.find((category) => String(category.id ?? '') === target) || null;
    };

    const getCategoryByInput = (inputValue) => {
        const normalized = normalizeText(inputValue);
        if (!normalized) {
            return null;
        }

        return categories.find((category) => normalizeText(category?.name) === normalized) || null;
    };

    const getSubCategoryById = (parentId, childId) => {
        const parent = getCategoryById(parentId);
        if (!parent) {
            return null;
        }

        return parent.children?.find((child) => String(child.id ?? '') === String(childId ?? '')) || null;
    };

    const getSubCategoryByInput = (parentId, inputValue) => {
        const parent = getCategoryById(parentId);
        if (!parent) {
            return null;
        }

        const normalized = normalizeText(inputValue);
        if (!normalized) {
            return null;
        }

        return parent.children?.find((child) => normalizeText(child?.name) === normalized) || null;
    };

    const renderParentLocationOptions = (selectedParentId = null) => {
        if (!$newLocationParentField.length) {
            return;
        }

        const selected = selectedParentId == null ? '' : String(selectedParentId);
        const options = ['<option value="">No parent location</option>'];

        locations.forEach((location) => {
            const value = String(location.id ?? '');
            if (!value) {
                return;
            }

            const label = escapeHtml(location.label || location.name || value);
            const selectedAttr = selected !== '' && value === selected ? ' selected' : '';
            options.push(`<option value="${escapeAttr(value)}"${selectedAttr}>${label}</option>`);
        });

        $newLocationParentField.html(options.join(''));
    };

    const renderLocationSuggestions = (entries = [], activeId = null) => {
        if (!$locationSuggestions.length || !$locationSuggestionsList.length) {
            return;
        }

        if (!entries.length) {
            $locationSuggestionsList.empty();
            $locationSuggestions.addClass('hidden');
            return;
        }

        const active = activeId == null ? '' : String(activeId);
        const html = entries.slice(0, 8).map((location) => {
            const id = String(location.id ?? '');
            const isActive = active !== '' && id === active;
            const classes = isActive
                ? 'border-emerald-600 bg-emerald-600 text-white shadow-sm'
                : 'border-emerald-900/15 bg-white text-emerald-900 hover:border-emerald-400 hover:bg-emerald-100/60';

            return `<button type="button" class="js-location-suggestion inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold transition ${classes}" data-location-id="${escapeAttr(id)}">${escapeHtml(getLocationLabel(location) || location.name || id)}</button>`;
        }).join('');

        $locationSuggestionsList.html(html);
        $locationSuggestions.removeClass('hidden');
    };

    const renderCategorySuggestions = (entries = [], activeId = null) => {
        if (!$categorySuggestions.length || !$categorySuggestionsList.length) {
            return;
        }

        if (!entries.length) {
            $categorySuggestionsList.empty();
            $categorySuggestions.addClass('hidden');
            return;
        }

        const active = activeId == null ? '' : String(activeId);
        const html = entries.slice(0, 5).map((category) => {
            const id = String(category.id ?? '');
            const isActive = active !== '' && id === active;
            const classes = isActive
                ? 'border-emerald-600 bg-emerald-600 text-white shadow-sm'
                : 'border-emerald-900/15 bg-white text-emerald-900 hover:border-emerald-400 hover:bg-emerald-100/60';

            return `<button type="button" class="js-category-suggestion inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold transition ${classes}" data-category-id="${escapeAttr(id)}">${escapeHtml(category.name || id)}</button>`;
        }).join('');

        $categorySuggestionsList.html(html);
        $categorySuggestions.removeClass('hidden');
    };

    const renderSubCategorySuggestions = (entries = [], activeId = null) => {
        if (!$subCategorySuggestions.length || !$subCategorySuggestionsList.length) {
            return;
        }

        if (!entries.length) {
            $subCategorySuggestionsList.empty();
            $subCategorySuggestions.addClass('hidden');
            return;
        }

        const active = activeId == null ? '' : String(activeId);
        const html = entries.slice(0, 5).map((category) => {
            const id = String(category.id ?? '');
            const isActive = active !== '' && id === active;
            const classes = isActive
                ? 'border-emerald-600 bg-emerald-600 text-white shadow-sm'
                : 'border-emerald-900/15 bg-white text-emerald-900 hover:border-emerald-400 hover:bg-emerald-100/60';

            return `<button type="button" class="js-subcategory-suggestion inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold transition ${classes}" data-subcategory-id="${escapeAttr(id)}">${escapeHtml(category.name || id)}</button>`;
        }).join('');

        $subCategorySuggestionsList.html(html);
        $subCategorySuggestions.removeClass('hidden');
    };

    const syncCategoryClearButton = () => {
        if (!$clearCategoryBtn.length) {
            return;
        }

        const hasValue = String($categoryInputField.val() || '').trim() !== ''
            || String($categoryIdField.val() || '').trim() !== '';
        $clearCategoryBtn.toggleClass('hidden', !hasValue);
    };

    const syncSubCategoryClearButton = () => {
        if (!$clearSubCategoryBtn.length) {
            return;
        }

        const hasValue = String($subCategoryInputField.val() || '').trim() !== ''
            || String($subCategoryIdField.val() || '').trim() !== '';
        $clearSubCategoryBtn.toggleClass('hidden', !hasValue);
    };

    const setSubCategoryEnabled = (enabled) => {
        if (!$subCategoryInputField.length) {
            return;
        }

        $subCategoryInputField.prop('disabled', !enabled);
        if (enabled) {
            $subCategoryInputField
                .removeClass('bg-gray-100 text-gray-600 cursor-not-allowed')
                .addClass('bg-white text-gray-700');
            $subCategoryInputField.attr('placeholder', 'Search and select sub-category...');
        } else {
            $subCategoryInputField
                .removeClass('bg-white text-gray-700')
                .addClass('bg-gray-100 text-gray-600 cursor-not-allowed');
            $subCategoryInputField.attr('placeholder', 'Select category first');
        }
    };

    const renderCategoryOptions = (selectedCategory = null, searchTerm = '', syncInput = true) => {
        if (!$categoryList.length || !$categoryInputField.length || !$categoryIdField.length) {
            return;
        }

        const selected = selectedCategory == null ? '' : String(selectedCategory);
        const normalizedSearch = normalizeText(searchTerm);
        const options = [];
        let selectedLabel = '';

        const filtered = categories.filter((category) => {
            const name = String(category?.name || '');
            const searchable = normalizeText(name);
            if (normalizedSearch && !searchable.includes(normalizedSearch) && String(category.id) !== selected) {
                return false;
            }
            return true;
        });

        const limited = filtered.slice(0, 5);

        limited.forEach((category) => {
            const label = String(category?.name || '').trim();
            if (!label) {
                return;
            }
            options.push(`<option value="${escapeAttr(label)}"></option>`);
            if (selected && String(category.id) === selected) {
                selectedLabel = label;
            }
        });

        $categoryList.html(options.join(''));
        renderCategorySuggestions(normalizedSearch ? limited : [], selected);

        if (selected) {
            $categoryIdField.val(selected);
            if (syncInput && selectedLabel) {
                $categoryInputField.val(selectedLabel);
            }
        }

        syncCategoryClearButton();
    };

    const renderSubCategoryOptions = (parentId, selectedChild = null, searchTerm = '', syncInput = true) => {
        if (!$subCategoryList.length || !$subCategoryInputField.length || !$subCategoryIdField.length) {
            return;
        }

        const parent = getCategoryById(parentId);
        if (!parent) {
            $subCategoryList.empty();
            $subCategoryIdField.val('');
            if (syncInput) {
                $subCategoryInputField.val('');
            }
            renderSubCategorySuggestions([]);
            setSubCategoryEnabled(false);
            syncSubCategoryClearButton();
            return;
        }

        setSubCategoryEnabled(true);

        const selected = selectedChild == null ? '' : String(selectedChild);
        const normalizedSearch = normalizeText(searchTerm);
        const options = [];
        let selectedLabel = '';

        const children = Array.isArray(parent.children) ? parent.children : [];
        const filtered = children.filter((child) => {
            const name = String(child?.name || '');
            const searchable = normalizeText(name);
            if (normalizedSearch && !searchable.includes(normalizedSearch) && String(child.id) !== selected) {
                return false;
            }
            return true;
        });

        const limited = filtered.slice(0, 5);

        limited.forEach((child) => {
            const label = String(child?.name || '').trim();
            if (!label) {
                return;
            }
            options.push(`<option value="${escapeAttr(label)}"></option>`);
            if (selected && String(child.id) === selected) {
                selectedLabel = label;
            }
        });

        $subCategoryList.html(options.join(''));
        renderSubCategorySuggestions(normalizedSearch ? limited : [], selected);

        if (selected) {
            const match = children.find((child) => String(child.id ?? '') === selected);
            if (match) {
                $subCategoryIdField.val(selected);
                if (syncInput && selectedLabel) {
                    $subCategoryInputField.val(selectedLabel);
                }
            } else {
                $subCategoryIdField.val('');
                if (syncInput) {
                    $subCategoryInputField.val('');
                }
            }
        } else {
            $subCategoryIdField.val('');
            if (syncInput) {
                $subCategoryInputField.val('');
            }
        }

        syncSubCategoryClearButton();
    };

    const applyCategorySelection = (parentId = null, childId = null) => {
        const parent = getCategoryById(parentId);
        const parentValue = parent ? String(parent.id) : '';
        $categoryIdField.val(parentValue);
        if ($categoryInputField.length) {
            $categoryInputField.val(parent?.name || '');
        }
        renderCategoryOptions(parentValue || null, '', false);
        renderSubCategoryOptions(parentValue || null, childId, '', true);
    };

    const syncLocationClearButton = () => {
        if (!$clearLocationBtn.length) {
            return;
        }

        const hasValue = String($initialLocationInputField.val() || '').trim() !== ''
            || String($initialLocationIdField.val() || '').trim() !== '';
        $clearLocationBtn.toggleClass('hidden', !hasValue);
    };

    const renderLocationOptions = (selectedLocation = null, searchTerm = '', syncInput = true) => {
        if (!$initialLocationList.length || !$initialLocationInputField.length || !$initialLocationIdField.length) {
            return;
        }

        const selected = selectedLocation == null ? '' : String(selectedLocation);
        const options = [];
        const normalizedSearch = normalizeLocationText(searchTerm);
        let selectedLabel = '';
        const matchedEntries = [];

        locations.forEach((location) => {
            const value = String(location.id ?? '');
            if (!value) {
                return;
            }

            const rawLabel = getLocationLabel(location);
            const searchable = `${rawLabel} ${location.name || ''}`.toLowerCase();
            if (normalizedSearch && !searchable.includes(normalizedSearch) && value !== selected) {
                return;
            }

            matchedEntries.push(location);

            const label = escapeHtml(rawLabel || value);
            options.push(`<option value="${label}"></option>`);

            if (selected !== '' && value === selected) {
                selectedLabel = rawLabel;
            }
        });

        $initialLocationList.html(options.join(''));
        renderLocationSuggestions(matchedEntries, selected);

        if (selected) {
            $initialLocationIdField.val(selected);
            if (syncInput) {
                $initialLocationInputField.val(selectedLabel || $initialLocationInputField.val());
            }
        }

        syncLocationClearButton();
    };

    const toggleNewLocationForm = (open) => {
        if (!$newLocationForm.length) {
            return;
        }

        $newLocationForm.toggleClass('hidden', !open);

        if ($toggleNewLocationBtn.length) {
            $toggleNewLocationBtn.html(open
                ? '<i class="fas fa-times text-[10px]"></i> Cancel New Location'
                : '<i class="fas fa-plus text-[10px]"></i> Add New Location');
        }

        if (open) {
            $initialLocationIdField.val('');
            $initialLocationInputField.val('');
            renderParentLocationOptions(null);
            renderLocationOptions(null, '', false);
            $newLocationNameField.trigger('focus');
        } else {
            $newLocationNameField.val('');
            if ($newLocationTypeField.length) {
                $newLocationTypeField.val('other');
            }
            if ($newLocationParentField.length) {
                $newLocationParentField.val('');
            }

            syncLocationClearButton();
        }
    };

    const getCurrentPageFromUrl = () => {
        const params = new URLSearchParams(window.location.search);
        const page = Number.parseInt(params.get(pageQueryKey) || '1', 10);
        return Number.isFinite(page) && page > 0 ? page : 1;
    };

    const syncCurrentPageToUrl = (page, replace = false) => {
        const nextUrl = new URL(window.location.href);
        if (page <= 1) {
            nextUrl.searchParams.delete(pageQueryKey);
        } else {
            nextUrl.searchParams.set(pageQueryKey, String(page));
        }

        const nextState = { inventoryPage: page };
        if (replace) {
            window.history.replaceState(nextState, '', nextUrl.toString());
        } else {
            window.history.pushState(nextState, '', nextUrl.toString());
        }
    };

    $categoryInputField.on('input', function () {
        const typedValue = String($(this).val() || '');
        const matched = getCategoryByInput(typedValue);
        $categoryIdField.val(matched ? String(matched.id) : '');
        renderCategoryOptions(matched ? matched.id : null, typedValue, false);

        if (matched) {
            renderSubCategoryOptions(matched.id, null, '', true);
        } else {
            renderSubCategoryOptions(null, null, '', true);
        }
    });

    $categoryInputField.on('blur', function () {
        const matched = getCategoryByInput($(this).val());
        if (matched) {
            $categoryIdField.val(String(matched.id));
            $(this).val(matched.name || '');
            renderCategoryOptions(matched.id, '', true);
            renderSubCategoryOptions(matched.id, $subCategoryIdField.val() || null, '', true);
            return;
        }

        if (!String($(this).val() || '').trim()) {
            $categoryIdField.val('');
            renderCategoryOptions(null, '', false);
            renderSubCategoryOptions(null, null, '', true);
            return;
        }

        renderCategoryOptions(null, $(this).val(), false);
    });

    $clearCategoryBtn.on('click', function () {
        $categoryIdField.val('');
        $categoryInputField.val('');
        renderCategoryOptions(null, '', false);
        renderSubCategoryOptions(null, null, '', true);
        $categoryInputField.trigger('focus');
    });

    $categorySuggestionsList.on('click', '.js-category-suggestion', function () {
        const categoryId = String($(this).data('categoryId') || '');
        if (!categoryId) {
            return;
        }

        applyCategorySelection(categoryId, null);
    });

    $subCategoryInputField.on('input', function () {
        const parentId = $categoryIdField.val();
        if (!parentId) {
            renderSubCategoryOptions(null, null, '', true);
            return;
        }

        const typedValue = String($(this).val() || '');
        const matched = getSubCategoryByInput(parentId, typedValue);
        $subCategoryIdField.val(matched ? String(matched.id) : '');
        renderSubCategoryOptions(parentId, matched ? matched.id : null, typedValue, false);
    });

    $subCategoryInputField.on('blur', function () {
        const parentId = $categoryIdField.val();
        if (!parentId) {
            renderSubCategoryOptions(null, null, '', true);
            return;
        }

        const matched = getSubCategoryByInput(parentId, $(this).val());
        if (matched) {
            $subCategoryIdField.val(String(matched.id));
            $(this).val(matched.name || '');
            renderSubCategoryOptions(parentId, matched.id, '', true);
            return;
        }

        if (!String($(this).val() || '').trim()) {
            $subCategoryIdField.val('');
            renderSubCategoryOptions(parentId, null, '', true);
            return;
        }

        renderSubCategoryOptions(parentId, null, $(this).val(), false);
    });

    $clearSubCategoryBtn.on('click', function () {
        const parentId = $categoryIdField.val();
        $subCategoryIdField.val('');
        $subCategoryInputField.val('');
        renderSubCategoryOptions(parentId || null, null, '', false);
        $subCategoryInputField.trigger('focus');
    });

    $subCategorySuggestionsList.on('click', '.js-subcategory-suggestion', function () {
        const parentId = $categoryIdField.val();
        const subCategoryId = String($(this).data('subcategoryId') || '');
        if (!parentId || !subCategoryId) {
            return;
        }

        renderSubCategoryOptions(parentId, subCategoryId, '', true);
    });

    $initialLocationInputField.on('input', function () {
        const typedValue = String($(this).val() || '');
        currentLocationSearchTerm = typedValue;
        const matched = getLocationByInput(typedValue);
        $initialLocationIdField.val(matched ? String(matched.id) : '');
        renderLocationOptions(matched ? matched.id : null, currentLocationSearchTerm, false);
    });

    $initialLocationInputField.on('blur', function () {
        const matched = getLocationByInput($(this).val());
        if (matched) {
            $initialLocationIdField.val(String(matched.id));
            $(this).val(getLocationLabel(matched));
            renderLocationOptions(matched.id, '', true);
            return;
        }

        if (!String($(this).val() || '').trim()) {
            $initialLocationIdField.val('');
            currentLocationSearchTerm = '';
            renderLocationOptions(null, '', false);
            return;
        }

        renderLocationOptions(null, $(this).val(), false);
    });

    $clearLocationBtn.on('click', function () {
        $initialLocationIdField.val('');
        $initialLocationInputField.val('');
        currentLocationSearchTerm = '';
        renderLocationOptions(null, '', false);
        $initialLocationInputField.trigger('focus');
    });

    $locationSuggestionsList.on('click', '.js-location-suggestion', function () {
        const locationId = String($(this).data('locationId') || '');
        const location = getLocationById(locationId);
        if (!location) {
            return;
        }

        $initialLocationIdField.val(String(location.id));
        $initialLocationInputField.val(getLocationLabel(location));
        currentLocationSearchTerm = '';
        renderLocationOptions(location.id, '', true);
    });

    $toggleNewLocationBtn.on('click', function () {
        const isOpen = !$newLocationForm.hasClass('hidden');
        toggleNewLocationForm(!isOpen);
    });

    $addLocationBtn.on('click', function () {
        const locationName = String($newLocationNameField.val() || '').trim().replace(/\s+/g, ' ');
        if (!locationName) {
            $createErrors.text('Please enter a location name before adding.').removeClass('hidden');
            $newLocationNameField.trigger('focus');
            return;
        }

        if (!routes.addLocation) {
            $createErrors.text('Add location endpoint is not configured.').removeClass('hidden');
            return;
        }

        const payload = {
            location_name: locationName,
            location_type: String($newLocationTypeField.val() || 'other'),
            parent_location_id: String($newLocationParentField.val() || '').trim(),
        };

        $createErrors.addClass('hidden').empty();
        const $button = $(this);
        const originalLabel = $button.html();
        $button.prop('disabled', true).addClass('opacity-70 cursor-not-allowed').html('<i class="fas fa-spinner fa-spin"></i> Adding...');

        $.ajax({
            url: routes.addLocation,
            method: 'POST',
            data: payload,
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                Accept: 'application/json',
            },
            success: (response) => {
                const location = response?.data?.location;
                if (location && location.id) {
                    const exists = locations.some((entry) => String(entry.id) === String(location.id));
                    if (!exists) {
                        locations.push(location);
                    }

                    $initialLocationIdField.val(String(location.id));
                    $initialLocationInputField.val(getLocationLabel(location));
                    currentLocationSearchTerm = '';
                    renderLocationOptions(location.id, currentLocationSearchTerm, true);
                    renderParentLocationOptions(null);
                    toggleNewLocationForm(false);
                }
            },
            error: (xhr) => {
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    const messages = Object.values(xhr.responseJSON.errors).flat();
                    $createErrors.html(messages.map((msg) => `<div>${msg}</div>`).join('')).removeClass('hidden');
                } else {
                    $createErrors.text(xhr.responseJSON?.message || 'Unable to add location right now.').removeClass('hidden');
                }
            },
            complete: () => {
                $button.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed').html(originalLabel);
            },
        });
    });

    const toggleModal = ($modal, open) => {
        const $panel = $modal.find('.modal-panel').first();

        if (open) {
            $modal.removeClass('hidden');
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    $modal.scrollTop(0);
                    if ($panel.length) {
                        $panel.scrollTop(0);
                        $panel.find('.overflow-y-auto, .overflow-y-scroll').scrollTop(0);
                    }
                    $modal.removeClass('opacity-0');
                    if ($panel.length) {
                        $panel.removeClass('opacity-0 scale-95 translate-y-2');
                    }
                });
            });
            $('body').addClass('overflow-hidden');
            return;
        }

        $modal.addClass('opacity-0');
        if ($panel.length) {
            $panel.addClass('opacity-0 scale-95 translate-y-2');
        }

        setTimeout(() => {
            $modal.addClass('hidden');

            if ($modal.is($createModal)) {
                $createForm[0].reset();
                $createErrors.addClass('hidden').empty();
                currentStoreUrl = null;
                applyCategorySelection(null, null);
                currentLocationSearchTerm = '';
                $initialLocationIdField.val('');
                $initialLocationInputField.val('');
                renderLocationOptions(null, currentLocationSearchTerm, false);
                renderParentLocationOptions(null);
                toggleNewLocationForm(false);
                $totalCostField.val('0.00');
                renderInspectionSerials([]);
                $itemAccountableOfficer.text('—');
                $initialCustodianField.val('');
            }

            if ($createModal.hasClass('hidden') && $viewModal.hasClass('hidden') && $successModal.hasClass('hidden') && $feedbackModal.hasClass('hidden')) {
                $('body').removeClass('overflow-hidden');
            }
        }, 300);
    };

    const showSuccessModal = (message) => {
        if ($successMessage.length) {
            $successMessage.text(message || 'PQS record created successfully.');
        }

        toggleModal($successModal, true);
    };

    const showFeedbackModal = ({
        title = 'Notice',
        subtitle = 'Please review this message.',
        message = 'Something needs your attention.',
        tone = 'warning',
        buttonLabel = 'OK',
    } = {}) => {
        if ($feedbackTitle.length) {
            $feedbackTitle.text(title);
        }
        if ($feedbackSubtitle.length) {
            $feedbackSubtitle.text(subtitle);
        }
        if ($feedbackMessage.length) {
            $feedbackMessage.text(message);
        }
        if ($feedbackClose.length) {
            $feedbackClose.text(buttonLabel);
            $feedbackClose.removeClass('bg-amber-500 hover:bg-amber-600 bg-rose-600 hover:bg-rose-700 bg-blue-600 hover:bg-blue-700');
        }

        if ($feedbackIcon.length) {
            $feedbackIcon.removeClass('bg-amber-100 text-amber-700 bg-rose-100 text-rose-700 bg-blue-100 text-blue-700');
        }

        if (tone === 'error') {
            $feedbackIcon.html('<i class="fas fa-triangle-exclamation"></i>').addClass('bg-rose-100 text-rose-700');
            $feedbackClose.addClass('bg-rose-600 hover:bg-rose-700');
        } else if (tone === 'info') {
            $feedbackIcon.html('<i class="fas fa-circle-info"></i>').addClass('bg-blue-100 text-blue-700');
            $feedbackClose.addClass('bg-blue-600 hover:bg-blue-700');
        } else {
            $feedbackIcon.html('<i class="fas fa-circle-exclamation"></i>').addClass('bg-amber-100 text-amber-700');
            $feedbackClose.addClass('bg-amber-500 hover:bg-amber-600');
        }

        toggleModal($feedbackModal, true);
    };

    $(document).on('click', '[data-close-modal]', function () {
        toggleModal($(this).closest('.fixed'), false);
    });

    $(document).on('click', function (event) {
        const $target = $(event.target);
        if ($target.data('closeModal') !== undefined) {
            toggleModal($target.closest('.fixed'), false);
        }
    });

    $(document).on('keydown', (event) => {
        if (event.key === 'Escape') {
            if (!$createModal.hasClass('hidden')) toggleModal($createModal, false);
            if (!$viewModal.hasClass('hidden')) toggleModal($viewModal, false);
            if (!$successModal.hasClass('hidden')) toggleModal($successModal, false);
            if (!$feedbackModal.hasClass('hidden')) toggleModal($feedbackModal, false);
        }
    });

    const renderInspectionSerials = (serials = []) => {
        if (! $serialsList.length) {
            return;
        }

        const normalized = Array.isArray(serials)
            ? serials.map((value) => String(value || '').trim()).filter(Boolean)
            : [];

        if (!normalized.length) {
            $serialsList.html('<li class="text-sm text-gray-400 italic">No serial numbers recorded.</li>');
            return;
        }

        $serialsList.html(normalized.map((serial) => `
            <li class="flex items-center gap-2 text-sm text-gray-700">
                <i class="fas fa-circle text-[6px] text-gray-400"></i>
                <span>${escapeHtml(serial)}</span>
            </li>
        `).join(''));
    };

    const recalculateTotalCost = () => {
        const quantity = Number($quantityField.val() || 0);
        const unitCost = Number($unitCostField.val() || 0);
        const total = quantity * unitCost;
        $totalCostField.val(total.toFixed(2));
    };

    $quantityField.on('input', recalculateTotalCost);
    $unitCostField.on('input', recalculateTotalCost);

    const renderEmptyRow = (message) => {
        $itemsBody.html(`
            <tr>
                <td colspan="8" class="px-5 py-20 text-center text-gray-500">
                    <i class="fas fa-folder-open text-5xl text-gray-300 mb-4"></i>
                    <p class="font-medium text-lg">${message}</p>
                    <p class="text-sm">Try adjusting your filters or switch to another status tab.</p>
                </td>
            </tr>
        `);
    };

    const buildActionButtons = (item) => {
        const createStateAttrs = item.can_create ? '' : 'disabled aria-disabled="true"';
        const viewStateAttrs = item.property_no ? '' : 'disabled aria-disabled="true"';
        const showUrl = routes.show?.replace('__ID__', item.ia_item_id);
        const storeUrl = routes.store?.replace('__ID__', item.ia_item_id);
        const showUrlAttr = showUrl ?? '';
        const storeUrlAttr = storeUrl ?? '';

        return `
            <div class="flex flex-col gap-2 sm:flex-row sm:justify-center">
                <button type="button" class="js-view-pqs inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-xs font-semibold text-gray-600 transition-all hover:border-gray-300 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-60"
                    data-item-id="${item.ia_item_id}"
                    data-show-url="${showUrlAttr}"
                    ${viewStateAttrs}>
                    <i class="fas fa-eye"></i>
                    View Record
                </button>
                <button type="button" class="js-create-pqs inline-flex cursor-pointer items-center gap-2 rounded-xl bg-[#1a3a2d] px-4 py-2.5 text-xs font-bold text-white shadow-md transition-all hover:-translate-y-0.5 hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0 disabled:hover:shadow-md"
                    data-item-id="${item.ia_item_id}"
                    data-show-url="${showUrlAttr}"
                    data-store-url="${storeUrlAttr}"
                    ${createStateAttrs}>
                    <i class="fas fa-barcode"></i>
                    Create PQS
                </button>
            </div>
        `;
    };

    const renderPagination = () => {
        const totalItems = currentItems.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);

        if (currentPage > totalPages && totalPages > 0) {
            currentPage = totalPages;
        }

        if (totalItems === 0) {
            $paginationContainer.addClass('hidden');
            return;
        }
        
        $paginationContainer.removeClass('hidden');
        const start = (currentPage - 1) * itemsPerPage + 1;
        const end = Math.min(currentPage * itemsPerPage, totalItems);
        
        $pageInfo.html(`Showing <span class="font-semibold text-gray-900">${start}</span> to <span class="font-semibold text-gray-900">${end}</span> of <span class="font-semibold text-gray-900">${totalItems}</span> results`);
        $pageMeta.html(`Showing page ${currentPage} of ${Math.max(totalPages, 1)} <span class="text-gray-400">(${totalItems} total records)</span>`);
        
        let linksHtml = '';

        if (currentPage <= 1) {
            linksHtml += '<span aria-disabled="true" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-medium text-gray-400 shadow-sm cursor-default">&laquo; Prev</span>';
        } else {
            linksHtml += `<button type="button" data-page="${currentPage - 1}" class="inline-flex h-10 items-center rounded-xl border border-[#1a3a2d]/15 bg-white px-4 text-sm font-medium text-[#496255] shadow-sm transition hover:border-[#1a3a2d]/30 hover:bg-[#f5faf7] hover:text-[#1a3a2d]">&laquo; Prev</button>`;
        }

        const pageItems = [];
        for (let i = 1; i <= totalPages; i += 1) {
            const onBoundary = i === 1 || i === totalPages;
            const onWindow = i >= currentPage - 1 && i <= currentPage + 1;
            if (onBoundary || onWindow) {
                pageItems.push(i);
            }
        }

        let lastRendered = 0;
        pageItems.forEach((page) => {
            if (page - lastRendered > 1) {
                linksHtml += '<span aria-disabled="true" class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-gray-200 bg-[#f8faf9] px-3 text-sm font-medium text-gray-400 shadow-sm">...</span>';
            }

            if (page === currentPage) {
                linksHtml += `<span aria-current="page" class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-[#1a3a2d] bg-[#1a3a2d] px-3 text-sm font-semibold text-white shadow-[0_12px_24px_-14px_rgba(26,58,45,0.8)]">${page}</span>`;
            } else {
                linksHtml += `<button type="button" data-page="${page}" aria-label="Go to page ${page}" class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-gray-200 bg-white px-3 text-sm font-medium text-gray-600 shadow-sm transition hover:border-[#1a3a2d]/25 hover:bg-[#f5faf7] hover:text-[#1a3a2d]">${page}</button>`;
            }

            lastRendered = page;
        });

        if (currentPage < totalPages) {
            linksHtml += `<button type="button" data-page="${currentPage + 1}" class="inline-flex h-10 items-center rounded-xl border border-[#1a3a2d]/15 bg-white px-4 text-sm font-medium text-[#496255] shadow-sm transition hover:border-[#1a3a2d]/30 hover:bg-[#f5faf7] hover:text-[#1a3a2d]">Next &raquo;</button>`;
        } else {
            linksHtml += '<span aria-disabled="true" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-medium text-gray-400 shadow-sm cursor-default">Next &raquo;</span>';
        }
        
        $pageLinks.html(linksHtml);
    };

    $pageLinks.on('click', 'button[data-page]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const page = Number($(this).data('page'));
        if (page && page !== currentPage) {
            currentPage = page;
            syncCurrentPageToUrl(currentPage);
            renderItems();
        }
    });

    const renderItems = () => {
        const items = currentItems;
        if (!items.length) {
            renderEmptyRow('No accepted items found for the selected filters.');
            $pendingCount.text('0');
            $recordedCount.text('0');
            $paginationContainer.addClass('hidden');
            return;
        }

        const pending = items.filter((item) => item.can_create).length;
        const recorded = items.length - pending;
        $pendingCount.text(`${pending}`);
        $recordedCount.text(`${recorded}`);

        const paginatedItems = items.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);
        const rows = paginatedItems.map((item, index) => {
            const rawDescription = String(item.item_description ?? '').trim();
            const compactDescription = rawDescription.length > 58
                ? `${rawDescription.slice(0, 58).trimEnd()}...`
                : rawDescription;
            const description = escapeHtml(compactDescription || '—');
            const propertyNo = escapeHtml(item.property_no || '—');
            const category = escapeHtml(item.category ?? '—');
            const subCategory = escapeHtml(item.sub_category ?? '—');
            const unit = item.unit ? ` ${escapeHtml(item.unit)}` : '';
            const quantityValue = item.quantity != null ? escapeHtml(String(item.quantity)) : null;
            const quantityDisplay = quantityValue ? `${quantityValue}${unit}` : '—';

            const delay = Math.min(index * 35, 280);

            return `
                <tr class="border-b last:border-0 hover:bg-gray-50 js-reveal-animated" style="opacity: 0; transform: translateY(6px); transition: opacity 220ms ease ${delay}ms, transform 260ms ease ${delay}ms;">
                    <td class="px-5 py-4 align-top text-sm font-semibold text-gray-900">${propertyNo}</td>
                    <td class="px-5 py-4 align-top">
                        <div class="font-semibold text-gray-800">${description}</div>
                        <div class="text-xs text-gray-500 mt-1">${escapeHtml(item.status_name || 'View Record for details')}</div>
                    </td>
                    <td class="px-5 py-4 align-top text-gray-700">${category}</td>
                    <td class="px-5 py-4 align-top text-gray-700">${subCategory}</td>
                    <td class="px-5 py-4 align-top text-right font-semibold text-gray-800">${quantityDisplay}</td>
                    <td class="px-5 py-4 align-top text-right text-gray-700">${formatCurrency(item.unit_cost)}</td>
                    <td class="px-5 py-4 align-top text-right text-gray-800 font-semibold">${formatCurrency(item.total_cost)}</td>
                    <td class="px-5 py-4 align-top text-right">${buildActionButtons(item)}</td>
                </tr>
            `;
        });

        $itemsBody.html(rows.join(''));
        renderPagination();

        requestAnimationFrame(() => {
            $itemsBody.find('tr.js-reveal-animated').each(function() {
                this.style.opacity = '1';
                this.style.transform = 'translateY(0)';
            });
        });
    };

    const fetchItems = () => {
        const params = {
            search: $search.val(),
            state: $stateFilter.val(),
        };

        // Include date filters only if the date range has been applied by the user
        if (appliedDateFrom) params.date_from = appliedDateFrom;
        if (appliedDateTo) params.date_to = appliedDateTo;

        $itemsBody.html(`
            <tr>
                <td colspan="8" class="px-5 py-20 text-center text-gray-500">
                    <i class="fas fa-spinner fa-pulse mb-4 text-3xl text-gray-300"></i>
                    <p class="font-medium text-lg">Loading properties...</p>
                </td>
            </tr>
        `);

        $.ajax({
            url: routes.items,
            method: 'GET',
            data: params,
            success: (response) => {
                currentItems = response.data?.items ?? [];
                currentPage = getCurrentPageFromUrl();
                renderItems();
                syncCurrentPageToUrl(currentPage, true);
            },
            error: () => {
                renderEmptyRow('Unable to load items at this time. Please try again later.');
                $pendingCount.text('0');
                $recordedCount.text('0');
                $paginationContainer.addClass('hidden');
            },
        });
    };

    window.__refreshInventoryAssignment = () => {
        if (
            $('#inventoryCreateModal').length && !$('#inventoryCreateModal').hasClass('hidden')
            || $('#inventoryViewModal').length && !$('#inventoryViewModal').hasClass('hidden')
            || $('#inventorySuccessModal').length && !$('#inventorySuccessModal').hasClass('hidden')
            || $('#inventoryFeedbackModal').length && !$('#inventoryFeedbackModal').hasClass('hidden')
        ) {
            return;
        }

        fetchItems();
    };

    const debounceFetch = () => {
        clearTimeout(fetchTimeout);
        fetchTimeout = setTimeout(fetchItems, 350);
    };

    $search.on('input', debounceFetch);
    $stateFilter.on('change', fetchItems);

    const buildInventoryExportUrl = (baseUrl) => {
        if (!baseUrl) {
            return null;
        }

        const targetUrl = new URL(baseUrl, window.location.origin);
        const searchValue = String($search.val() || '').trim();
        const stateValue = String($stateFilter.val() || 'all').trim();

        if (searchValue) {
            targetUrl.searchParams.set('search', searchValue);
        }

        if (stateValue) {
            targetUrl.searchParams.set('state', stateValue);
        }

        if (appliedDateFrom) {
            targetUrl.searchParams.set('date_from', appliedDateFrom);
        }

        if (appliedDateTo) {
            targetUrl.searchParams.set('date_to', appliedDateTo);
        }

        return targetUrl.toString();
    };

    $printPdfBtn.on('click', function () {
        const url = buildInventoryExportUrl($(this).data('printUrl'));
        if (!url) {
            return;
        }

        window.open(url, '_blank');
    });

    $exportExcelBtn.on('click', function () {
        const url = buildInventoryExportUrl($(this).data('excelUrl'));
        if (!url) {
            return;
        }

        window.location.href = url;
    });

    window.addEventListener('popstate', () => {
        currentPage = getCurrentPageFromUrl();
        if (currentItems.length) {
            renderItems();
        }
    });

    $(document).on('click', '.inventory-tab', function() {
        const $tab = $(this);
        const tabKey = $tab.data('inventory-tab');
        
        $stateFilter.val(tabKey).trigger('change');
        
        $('.inventory-tab').each(function() {
            const $t = $(this);
            const activeClass = $t.data('activeClass') || '';
            const inactiveClass = $t.data('inactiveClass') || '';
            
            $t.removeClass(activeClass).removeClass('is-selected').addClass(inactiveClass);
        });
        
        const activeClass = $tab.data('activeClass') || '';
        const inactiveClass = $tab.data('inactiveClass') || '';
        $tab.removeClass(inactiveClass).addClass(activeClass).addClass('is-selected');
    });

    // Apply button for date range: only clicking this will apply the date filters
    $applyDateBtn.on('click', () => {
        const from = $dateFrom.val() || null;
        const to = $dateTo.val() || null;

        // Simple validation: if one is provided ensure the other is too
        if ((from && !to) || (!from && to)) {
            showFeedbackModal({
                title: 'Incomplete Date Range',
                subtitle: 'Both dates are required to filter records.',
                message: 'Please provide both start and end dates to apply the date range.',
                tone: 'warning',
            });
            return;
        }

        appliedDateFrom = from;
        appliedDateTo = to;

        fetchItems();
    });

    const populateCreateModal = (data) => {
        const item = data || {};
        const categoriesData = {
            parent_id: item.category_id || null,
            sub_id: item.sub_category_id || null,
        };

        applyCategorySelection(categoriesData.parent_id, categoriesData.sub_id);

        $('#inventoryIaItemId').val(item.ia_item_id);
        $descriptionField.val(item.property_record?.description || item.item_description || '');
        $unitField.val(item.unit || '');

        const acceptedQuantity = item.quantity || 1;
        $quantityField.attr('max', acceptedQuantity).val(acceptedQuantity);
        $unitCostField.val(item.unit_cost ?? 0);
        $totalCostField.val((acceptedQuantity * (item.unit_cost ?? 0)).toFixed(2));
        $dateAcquiredField.val(item.property_record?.date_acquired || todayIso());
        const usefulLife = item.property_record?.estimated_useful_life || '';
        const usefulLifeParts = parseUsefulLife(usefulLife);
        $usefulLifeValueField.val(usefulLifeParts.amount || '');
        $usefulLifeUnitField.val(usefulLifeParts.unit || 'years');
        const preferredLocationId = item.property_record?.current_location_id
            || item.recommended_initial_location_id
            || null;
        currentLocationSearchTerm = '';
        $initialLocationIdField.val(preferredLocationId ? String(preferredLocationId) : '');
        $initialLocationInputField.val('');
        renderLocationOptions(preferredLocationId, currentLocationSearchTerm, true);
        renderParentLocationOptions(null);
        toggleNewLocationForm(false);
        renderInspectionSerials(item.inspection_serial_numbers || []);

        $itemDescription.text(item.item_description ?? '—');
        $itemSource.text(`PO ${item.po_no ?? '—'} • IA ${item.ia_no ?? '—'}`);
        $itemAccepted.text(`${acceptedQuantity} accepted`);
        $itemSupplier.text(item.supplier_name ?? '—');
        $itemAccountableOfficer.text(item.accountable_officer_name ?? '—');
        $initialCustodianField.val(item.accountable_officer_name || 'No accountable officer linked');
    };

    const openCreateModal = (button) => {
        const showUrl = button.data('showUrl');
        currentStoreUrl = button.data('storeUrl');

        if (!showUrl || !currentStoreUrl) {
            showFeedbackModal({
                title: 'Configuration Missing',
                subtitle: 'Action cannot proceed right now.',
                message: 'Endpoints for this item are not configured.',
                tone: 'error',
            });
            return;
        }

        $.ajax({
            url: showUrl,
            method: 'GET',
            headers: { Accept: 'application/json' },
            success: (response) => {
                populateCreateModal(response.data);
                toggleModal($createModal, true);
            },
            error: () => {
                showFeedbackModal({
                    title: 'Load Failed',
                    subtitle: 'Unable to open create form.',
                    message: 'Unable to load item details.',
                    tone: 'error',
                });
            },
        });
    };

    const openViewModal = (button) => {
        const showUrl = button.data('showUrl');
        if (!showUrl) {
            return;
        }

        $.ajax({
            url: showUrl,
            method: 'GET',
            headers: { Accept: 'application/json' },
            success: (response) => {
                const data = response.data || {};
                if (!data.property_no && !data.property_record) {
                    showFeedbackModal({
                        title: 'No Record Yet',
                        subtitle: 'This item has not been recorded in PQS.',
                        message: 'No PQS record found for this item yet.',
                        tone: 'info',
                    });
                    return;
                }

                const propertyRecord = data.property_record || {};

                const propertyNumber = data.property_no || propertyRecord.property_no || '—';
                const unit = propertyRecord.unit || data.unit || '';
                const quantityRaw = propertyRecord.quantity ?? data.quantity ?? null;
                const quantityDisplay = quantityRaw != null
                    ? `${quantityRaw} ${unit}`.trim()
                    : (unit || '—');

                const sourceSegments = [];
                if (data.po_no) sourceSegments.push(`PO ${data.po_no}`);
                if (data.ia_no) sourceSegments.push(`IA ${data.ia_no}`);
                if (data.supplier_name) sourceSegments.push(data.supplier_name);
                $viewSource.text(sourceSegments.join(' • ') || '—');

                $viewPropertyNo.text(propertyNumber);
                $viewDateAcquired.text(propertyRecord.date_acquired || '—');
                $viewDescription.text(propertyRecord.description || data.item_description || '—');
                $viewCategory.text(data.category || '—');
                $viewSubCategory.text(data.sub_category || '—');
                $viewQuantity.text(quantityDisplay || '—');
                $viewPoNo.text(data.po_no || '—');
                $viewIaNo.text(data.ia_no || '—');
                $viewPrNo.text(data.pr_no || '—');
                $viewSupplier.text(data.supplier_name || '—');

                const unitCostValue = propertyRecord.unit_value ?? data.unit_cost ?? null;
                const totalCostValue = propertyRecord.total_value ?? data.total_cost ?? (quantityRaw != null && unitCostValue != null
                    ? Number(quantityRaw) * Number(unitCostValue)
                    : null);
                $viewUnitCost.text(unitCostValue != null ? formatCurrency(unitCostValue) : '—');
                $viewTotalCost.text(totalCostValue != null ? formatCurrency(totalCostValue) : '—');

                $viewOfficer.text(propertyRecord.accountable_officer_name || data.accountable_officer_name || '—');
                const usefulLife = propertyRecord.estimated_useful_life
                    || data.ics_record?.estimated_useful_life
                    || data.par_record?.estimated_useful_life
                    || '';
                $viewUsefulLife.text(usefulLife || '—');

                const serialRaw = propertyRecord.serial_number || '';
                const serials = serialRaw
                    ? serialRaw.split(/[\n,;]+/).map((serial) => serial.trim()).filter(Boolean)
                    : [];
                if (serials.length) {
                    $viewSerials.html(serials.map((serial) => `
                        <li class="flex items-center gap-2 text-sm text-gray-700">
                            <i class="fas fa-circle text-[6px] text-gray-400"></i>
                            <span>${escapeHtml(serial)}</span>
                        </li>
                    `).join(''));
                } else {
                    $viewSerials.html('<li class="text-sm text-gray-400 italic">No serial numbers recorded.</li>');
                }

                $viewRemarks.text(propertyRecord.remarks || 'No additional remarks recorded.');

                const badgeBaseClasses = 'inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold shadow-sm';
                let badgeClasses = 'bg-amber-100 text-amber-700';
                let badgeIcon = 'fa-hourglass-half';
                let badgeLabel = 'Pending Document';
                let docMetaText = 'No custodial document generated yet.';
                let docExtraText = 'Generate PQS record to create custodial documents automatically.';

                if (data.ics_record) {
                    badgeClasses = 'bg-emerald-100 text-emerald-700';
                    badgeIcon = 'fa-file-circle-check';
                    badgeLabel = data.ics_record.ics_no ? `ICS #${data.ics_record.ics_no}` : 'ICS Issued';
                    const quantityForDoc = data.ics_record.quantity ?? quantityRaw ?? 0;
                    const unitLabel = unit || 'unit(s)';
                    docMetaText = `${quantityForDoc} ${unitLabel} • ${formatCurrency(data.ics_record.total_cost)}`;
                    docExtraText = data.ics_record.estimated_useful_life
                        ? `Estimated useful life: ${data.ics_record.estimated_useful_life}`
                        : '';
                } else if (data.par_record) {
                    badgeClasses = 'bg-purple-100 text-purple-700';
                    badgeIcon = 'fa-file-contract';
                    badgeLabel = data.par_record.par_no ? `PAR #${data.par_record.par_no}` : 'PAR Issued';
                    const quantityForDoc = data.par_record.quantity ?? quantityRaw ?? 0;
                    const unitLabel = unit || 'unit(s)';
                    docMetaText = `${quantityForDoc} ${unitLabel} • ${formatCurrency(data.par_record.amount)}`;
                    docExtraText = data.par_record.date_acquired
                        ? `Acquired on ${data.par_record.date_acquired}`
                        : '';
                }

                $viewDocumentBadge
                    .attr('class', `${badgeBaseClasses} ${badgeClasses}`)
                    .html(`<i class="fas ${badgeIcon}"></i><span>${escapeHtml(badgeLabel)}</span>`);

                $viewDocumentMeta.text(docMetaText);
                if (docExtraText) {
                    $viewDocumentExtra.text(docExtraText).removeClass('hidden');
                } else {
                    $viewDocumentExtra.text('Generate PQS record to create custodial documents automatically.').removeClass('hidden');
                }

                toggleModal($viewModal, true);
            },
            error: () => {
                showFeedbackModal({
                    title: 'Load Failed',
                    subtitle: 'Unable to open the PQS record.',
                    message: 'Unable to load PQS record.',
                    tone: 'error',
                });
            },
        });
    };

    $(document).on('click', '.js-create-pqs', function () {
        if ($(this).is(':disabled')) {
            return;
        }
        openCreateModal($(this));
    });

    $(document).on('click', '.js-view-pqs', function () {
        if ($(this).is(':disabled')) {
            return;
        }
        openViewModal($(this));
    });

    $createForm.on('submit', function (event) {
        event.preventDefault();
        if (!currentStoreUrl) {
            showFeedbackModal({
                title: 'Configuration Missing',
                subtitle: 'Save action is not available.',
                message: 'No endpoint configured for saving this record.',
                tone: 'error',
            });
            return;
        }

        $createErrors.addClass('hidden').empty();

        if (!$newLocationForm.hasClass('hidden')) {
            const newLocationName = String($newLocationNameField.val() || '').trim();
            if (!newLocationName) {
                $createErrors.text('Please enter a new location name or close the new location form.').removeClass('hidden');
                $newLocationNameField.trigger('focus');
                return;
            }
        }

        const $submitBtn = $createForm.find('button[type="submit"]');
        $submitBtn.prop('disabled', true).addClass('opacity-70 cursor-not-allowed');

        $.ajax({
            url: currentStoreUrl,
            method: 'POST',
            data: $createForm.serialize(),
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                Accept: 'application/json',
            },
            success: (response) => {
                toggleModal($createModal, false);
                setTimeout(() => {
                    showSuccessModal(response.message || 'PQS record created successfully.');
                }, 320);

                const location = response?.data?.initial_location;
                if (location && location.id) {
                    const exists = locations.some((entry) => String(entry.id) === String(location.id));
                    if (!exists) {
                        locations.push(location);
                    }
                }

                renderLocationOptions(location?.id ?? null, currentLocationSearchTerm, true);
                renderParentLocationOptions(null);

                fetchItems();
            },
            error: (xhr) => {
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    const messages = Object.values(xhr.responseJSON.errors).flat();
                    $createErrors.html(messages.map((msg) => `<div>${msg}</div>`).join(''));
                    $createErrors.removeClass('hidden');
                } else if (xhr.responseJSON?.message) {
                    $createErrors.text(xhr.responseJSON.message).removeClass('hidden');
                } else {
                    $createErrors.text('Something went wrong while saving the record.').removeClass('hidden');
                }
            },
            complete: () => {
                $submitBtn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
            },
        });
    });

    // Initialize applied date filter from URL/initial inputs when both values are present.
    const initialDateFrom = String($dateFrom.val() || '').trim();
    const initialDateTo = String($dateTo.val() || '').trim();
    if (initialDateFrom && initialDateTo) {
        appliedDateFrom = initialDateFrom;
        appliedDateTo = initialDateTo;
    }

    // Initialize state
    applyCategorySelection(null, null);
    renderLocationOptions(null, currentLocationSearchTerm, false);
    renderParentLocationOptions(null);
    renderInspectionSerials([]);
    fetchItems();

    if (!window.__inventoryAssignmentAutoRefreshTimer) {
        window.__inventoryAssignmentAutoRefreshTimer = window.setInterval(() => {
            if (document.hidden) {
                return;
            }

            if (typeof window.__refreshInventoryAssignment === 'function') {
                window.__refreshInventoryAssignment();
            }
        }, 30000);
    }
});
