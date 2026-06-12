/**
 * Filters a "Visit Type" <select> based on the "Patient Category" selected
 * in another <select>. Each visit-type <option> must carry a
 * data-categories="1,2,3" attribute listing the patient category IDs it is
 * allowed for. An empty/missing data-categories means the visit type is
 * available for all categories.
 *
 * If the currently selected visit type becomes hidden by the filter, the
 * selection is cleared.
 */
function applyVisitTypeCategoryFilter(categorySelect, visitTypeSelect) {
    if (!categorySelect || !visitTypeSelect) {
        return;
    }

    function filter(resetIfHidden) {
        const selectedCategoryId = categorySelect.value;

        Array.from(visitTypeSelect.options).forEach(function (option) {
            if (!option.value) {
                return;
            }

            const allowed = (option.dataset.categories || '')
                .split(',')
                .map(function (id) { return id.trim(); })
                .filter(function (id) { return id !== ''; });

            const isAllowed = allowed.length === 0 || allowed.includes(selectedCategoryId);

            option.hidden = !isAllowed;
            option.disabled = !isAllowed;
        });

        if (resetIfHidden) {
            const selectedOption = visitTypeSelect.options[visitTypeSelect.selectedIndex];
            if (selectedOption && selectedOption.value && selectedOption.disabled) {
                visitTypeSelect.value = '';
                visitTypeSelect.dispatchEvent(new Event('change'));
            }
        }
    }

    // Re-filter (and clear an invalid selection) whenever the user changes the category.
    categorySelect.addEventListener('change', function () { filter(true); });

    // Filter on initial load without clearing an already-selected value
    // (e.g. an existing visit's category/visit type, even if since restricted).
    filter(false);
}
