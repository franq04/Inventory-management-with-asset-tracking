{{-- PR Details Modal Partial --}}
<div id="employeePrDetailsModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" data-close-modal></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="modal-panel relative bg-white w-full max-w-5xl rounded-2xl shadow-2xl flex flex-col max-h-[calc(100vh-2rem)] transition-all duration-300 ease-out opacity-0 scale-95 translate-y-2">
            {{-- Header --}}
            <div class="flex-shrink-0 flex items-center justify-between px-6 py-4 border-b bg-gradient-to-r from-[#1a3a2d] to-[#2d5a4a] text-white shadow-md">
                <div class="flex-1">
                    <h3 class="text-2xl font-bold tracking-tight">Purchase Request Details</h3>
                    <p class="text-sm text-white/90 mt-1" id="employeePrDetailsModalTitle"></p>
                </div>
                <button class="text-white/80 hover:text-white hover:bg-white/10 p-2 rounded-lg transition-all" data-close-modal>
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            {{-- Scrollable Body with Annex G-6 Format --}}
            <div class="flex-1 overflow-y-auto hide-scrollbar p-6 bg-gray-50">
                <div class="bg-white rounded-2xl border-2 border-gray-300 overflow-hidden" id="employeePrAnnexContent">
                    {{-- Annex G-6 content will be populated by JavaScript --}}
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex-shrink-0 z-10 flex items-center justify-end px-6 py-4 border-t bg-gray-50">
                <button class="px-6 py-3 rounded-lg bg-white border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-100 hover:border-gray-400 transition-all shadow-sm" data-close-modal>
                    <i class="fas fa-times mr-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>
