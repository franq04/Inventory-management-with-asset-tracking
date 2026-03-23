<div class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-300" data-category-modal>
    <div class="absolute inset-0 bg-black/70 backdrop-blur" data-modal-overlay></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="modal-panel relative w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl transition-all duration-300 ease-out opacity-0 scale-95 translate-y-2">
            <div class="flex items-center justify-between px-6 py-4 border-b bg-gradient-to-r from-[#1a3a2d] to-[#2d5a4a] text-white">
                <div class="flex-1">
                    <h3 class="text-lg font-bold" data-modal-title>Add Category</h3>
                    <p class="text-sm text-white/80" data-modal-subtitle>Register a new category</p>
                </div>
                <button type="button" class="text-white/70 hover:text-white hover:bg-white/10 p-2 rounded-lg transition" data-close-modal>
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form method="POST" class="p-6 space-y-4" data-category-form>
                @csrf
                <input type="hidden" name="mode" value="create" data-modal-mode>
                <input type="hidden" name="parent_id" value="" data-modal-parent>

                <div>
                    <label for="categoryIdField" class="block text-sm font-semibold text-gray-700">Category ID</label>
                    <input id="categoryIdField" name="cat_id" type="text" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]" placeholder="e.g., CAT-2001">
                    <p id="categoryIdFieldError" class="mt-1 hidden text-xs font-medium text-red-600"></p>
                </div>

                <div>
                    <label for="categoryNameField" class="block text-sm font-semibold text-gray-700">Category Name</label>
                    <input id="categoryNameField" name="cat_name" type="text" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]" placeholder="Enter category name">
                    <p id="categoryNameFieldError" class="mt-1 hidden text-xs font-medium text-red-600"></p>
                </div>

                <div>
                    <label for="categoryDescriptionField" class="block text-sm font-semibold text-gray-700">Description <span class="text-xs text-gray-400">(optional)</span></label>
                    <textarea id="categoryDescriptionField" name="description" rows="3" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]" placeholder="Add a brief description"></textarea>
                    <p id="categoryDescriptionFieldError" class="mt-1 hidden text-xs font-medium text-red-600"></p>
                </div>

                <div class="hidden" data-parent-indicator>
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-700">
                        Adding a subcategory under <span class="font-semibold" data-parent-name></span>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" class="px-4 py-2 text-sm font-semibold text-gray-600 rounded-lg border border-gray-200 hover:bg-gray-100 transition" data-close-modal>Cancel</button>
                    <button type="submit" data-no-global-loading="true" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white rounded-lg bg-[#1a3a2d] hover:bg-[#1a3a2d]/90 shadow transition" data-submit-button>
                        <i class="fas fa-save"></i>
                        <span data-submit-label>Save Category</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>