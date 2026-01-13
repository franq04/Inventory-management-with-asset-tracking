<div class="relative" data-notif-root>
    <button data-notif-toggle class="relative flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
        <i class="fas fa-bell"></i>
        <span data-notif-count class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full hidden"></span>
    </button>
    <div data-notif-panel class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden z-30 hidden">
        <div class="px-4 py-3 border-b bg-gray-50 flex items-center justify-between">
            <h4 class="text-sm font-semibold text-gray-700">Notifications</h4>
            <button data-notif-mark-all class="text-xs font-semibold text-[#1a3a2d] hover:text-[#154529] transition">
                Mark all as read
            </button>
        </div>
        <div class="max-h-72 overflow-y-auto" data-notif-scroll>
            <ul data-notif-list class="divide-y divide-gray-100 hidden"></ul>
            <div data-notif-empty class="px-4 py-6 text-sm text-gray-500 text-center">
                <i class="fas fa-bell-slash text-2xl text-gray-300 mb-2"></i>
                <p>No notifications yet.</p>
            </div>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            window.notificationConfig = {
                listUrl: '{{ route('notifications.index') }}',
                markAllUrl: '{{ route('notifications.read-all') }}',
                markUrlTemplate: '{{ route('notifications.read', ['notification' => '__ID__']) }}'
            };
        </script>
    @endpush
@endonce
