<?php

$jsFile = 'resources/js/inventory-assignment.js';
$jsCode = file_get_contents($jsFile);

// 1. Add pagination variables and containers
$searchString = "const \$stateFilter = $('#inventoryState');";
$replaceString = "const \$stateFilter = $('#inventoryState');\n    const \$paginationContainer = $('#paginationContainer');\n    const \$pageLinks = $('#pageLinks');\n    const \$pageInfo = $('#pageInfo');\n    let currentItems = [];\n    let currentPage = 1;\n    const itemsPerPage = 8;\n";
$jsCode = str_replace($searchString, $replaceString, $jsCode);

// 2. Add fetchItems storage
$searchString2 = "renderItems(response.data?.items ?? []);";
$replaceString2 = "currentItems = response.data?.items ?? [];\n                currentPage = 1;\n                renderItems();";
$jsCode = str_replace($searchString2, $replaceString2, $jsCode);

// 3. Change renderItems signature and add slicing + pagination UI logic
$searchString3 = "const renderItems = (items) => {";
$replaceString3 = <<<EOT
const renderPagination = () => {
        const totalItems = currentItems.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        
        if (totalItems <= itemsPerPage) {
            \$paginationContainer.addClass('hidden');
            return;
        }
        
        \$paginationContainer.removeClass('hidden');
        const start = (currentPage - 1) * itemsPerPage + 1;
        const end = Math.min(currentPage * itemsPerPage, totalItems);
        
        \$pageInfo.html(`Showing <span class="font-semibold text-gray-900">\${start}</span> to <span class="font-semibold text-gray-900">\${end}</span> of <span class="font-semibold text-gray-900">\${totalItems}</span> results`);
        
        let linksHtml = '';
        
        // Prev button
        linksHtml += `
            <a href="#" data-page="\${currentPage > 1 ? currentPage - 1 : 1}" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 \${currentPage === 1 ? 'pointer-events-none opacity-50' : ''}">
                <span class="sr-only">Previous</span>
                <i class="fas fa-chevron-left h-4 w-4 text-center"></i>
            </a>
        `;
        
        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            // Simple logic: show all for now (or truncate if too many, but typically < 10 pages)
            if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                if (i === currentPage) {
                    linksHtml += `<a href="#" data-page="\${i}" class="relative z-10 inline-flex items-center bg-[#1a3a2d] px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1a3a2d]">\${i}</a>`;
                } else {
                    linksHtml += `<a href="#" data-page="\${i}" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">\${i}</a>`;
                }
            } else if (i === currentPage - 3 || i === currentPage + 3) {
                linksHtml += `<span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300">...</span>`;
            }
        }
        
        // Next button
        linksHtml += `
            <a href="#" data-page="\${currentPage < totalPages ? currentPage + 1 : totalPages}" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 \${currentPage === totalPages ? 'pointer-events-none opacity-50' : ''}">
                <span class="sr-only">Next</span>
                <i class="fas fa-chevron-right h-4 w-4 text-center"></i>
            </a>
        `;
        
        \$pageLinks.html(linksHtml);
    };

    \$pageLinks.on('click', 'a', function(e) {
        e.preventDefault();
        const page = parseInt(\$(this).data('page'));
        if (page && page !== currentPage) {
            currentPage = page;
            renderItems();
        }
    });

    const renderItems = () => {
        const items = currentItems;
EOT;
$jsCode = str_replace($searchString3, $replaceString3, $jsCode);

// 4. Update the actual rows rendering inside renderItems to slice the array
// We need to replace `const rows = items.map((item, index) => {`
// with `const paginatedItems = items.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage); const rows = paginatedItems.map((item, index) => {`
$searchString4 = "const rows = items.map((item, index) => {";
$replaceString4 = "const paginatedItems = items.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);\n        const rows = paginatedItems.map((item, index) => {";
$jsCode = str_replace($searchString4, $replaceString4, $jsCode);

// 5. Add call to renderPagination() before setting $itemsBody.html(rows.join(''));
$searchString5 = "\$itemsBody.html(rows.join(''));";
$replaceString5 = "\$itemsBody.html(rows.join(''));\n        renderPagination();";
$jsCode = str_replace($searchString5, $replaceString5, $jsCode);

// 6. Update rendering counting so it still uses the whole dataset instead of page
// wait, we don't need to change that since we mapped `items = currentItems;` and `pending = items.filter(...)`. That operates on `currentItems`.
// BUT what if `items` is empty? 
$searchString6 = "\$pendingCount.text(`\${pending}`);"; // wait, earlier we changed it.
// Actually, earlier I made it just replace "$pendingCount.text(`${pending} Pending`);"
// Let's verify what it looks like now.

file_put_contents($jsFile, $jsCode);
echo "JS Patched.\n";

$bladeFile = 'resources/views/custodian/inventory/index.blade.php';
$bladeCode = file_get_contents($bladeFile);

// add pagination DOM inside the wrapper
$bladeSearch = "</table>\n            </div>\n        </div>\n    </div>";
$bladeReplace = "</table>\n            </div>\n\n            <!-- Pagination -->\n            <div id=\"paginationContainer\" class=\"hidden border-t border-gray-100 bg-[#fbfcfb] px-5 py-4\">\n                <div class=\"flex flex-col items-center justify-between gap-4 sm:flex-row\">\n                    <p id=\"pageInfo\" class=\"text-sm text-gray-700\">\n                        Showing <span class=\"font-semibold text-gray-900\">1</span> to <span class=\"font-semibold text-gray-900\">10</span> of <span class=\"font-semibold text-gray-900\">0</span> results\n                    </p>\n                    <nav id=\"pageLinks\" class=\"isolate inline-flex -space-x-px rounded-md shadow-sm\" aria-label=\"Pagination\">\n                    </nav>\n                </div>\n            </div>\n\n        </div>\n    </div>";
$bladeCode = str_replace($bladeSearch, $bladeReplace, $bladeCode);

file_put_contents($bladeFile, $bladeCode);
echo "Blade Patched.\n";

?>

