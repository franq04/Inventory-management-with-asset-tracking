@extends('layouts.app')

@section('title', 'Employee Dashboard')

@php
$statusStyles = [
    'Draft' => ['icon' => 'fas fa-pencil-alt', 'color' => 'gray'],
    'For Recommendation' => ['icon' => 'fas fa-user-check', 'color' => 'blue'],
    'Recommended' => ['icon' => 'fas fa-thumbs-up', 'color' => 'indigo'],
    'For Approval' => ['icon' => 'fas fa-hourglass-half', 'color' => 'amber'],
    'Approved' => ['icon' => 'fas fa-check-circle', 'color' => 'green'],
    'For Revision' => ['icon' => 'fas fa-edit', 'color' => 'orange'],
    'Rejected' => ['icon' => 'fas fa-times-circle', 'color' => 'red'],
    'Cancelled' => ['icon' => 'fas fa-ban', 'color' => 'gray'],
    'Completed' => ['icon' => 'fas fa-award', 'color' => 'emerald'],
    'default' => ['icon' => 'fas fa-question-circle', 'color' => 'gray'],
];

$logActionStyles = [
    'create' => 'green',
    'update' => 'blue',
    'delete' => 'red',
    'login' => 'purple',
    'logout' => 'gray',
    'default' => 'gray',
];
@endphp

@php
    // Ensure collections are defined to avoid calling methods on null
    $unreadNotifications = $unreadNotifications ?? collect();
    $myRequestsByStatus = $myRequestsByStatus ?? [];
    $myRecentRequests = $myRecentRequests ?? collect();
    $myRecentActivity = $myRecentActivity ?? collect();
    $myRequests = $myRequests ?? 0;
    $myTotalItems = $myTotalItems ?? 0;
    $pendingActions = $pendingActions ?? 0;
    $myTotalValue = $myTotalValue ?? 0;
@endphp

@section('content')
<div class="space-y-8">
    {{-- =============================================================== --}}
    {{-- 1. Welcome Header --}}
    {{-- =============================================================== --}}
    <div class="animate-card" style="animation-delay: 100ms;">
        <div class="bg-gradient-to-r from-[#1a3a2d] to-[#2d5a45] rounded-2xl shadow-xl p-8 text-white">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-extrabold">Welcome Back, {{ session('username') }}!</h2>
                    <p class="mt-2 text-gray-200">Here's an overview of your purchase requests and recent activity.</p>
                </div>
                <a href="{{ route('employee.purchase-requests.index') }}" class="inline-flex items-center gap-2 bg-white text-[#1a3a2d] px-6 py-3 rounded-xl font-bold shadow-lg hover:bg-gray-100 transition-all duration-200 hover:-translate-y-1">
                    <i class="fas fa-plus-circle"></i>
                    <span>New Request</span>
                </a>
            </div>
        </div>
    </div>

    {{-- =============================================================== --}}
    {{-- 2. Summary Statistics Cards --}}
    {{-- =============================================================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="animate-card bg-white p-6 rounded-2xl shadow-lg border-l-4 border-blue-500 hover:shadow-xl transition-all duration-300 hover:-translate-y-1" style="animation-delay: 200ms;">
            <div class="flex items-center gap-4">
                <div class="p-4 rounded-xl bg-blue-50 text-blue-600 flex-shrink-0">
                    <i class="fas fa-file-alt text-2xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-500 font-medium">My Requests</p>
                    <p class="text-3xl font-extrabold text-gray-800">{{ number_format($myRequests) }}</p>
                </div>
            </div>
        </div>

        <div class="animate-card bg-white p-6 rounded-2xl shadow-lg border-l-4 border-purple-500 hover:shadow-xl transition-all duration-300 hover:-translate-y-1" style="animation-delay: 250ms;">
            <div class="flex items-center gap-4">
                <div class="p-4 rounded-xl bg-purple-50 text-purple-600 flex-shrink-0">
                    <i class="fas fa-boxes text-2xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-500 font-medium">Total Items</p>
                    <p class="text-3xl font-extrabold text-gray-800">{{ number_format($myTotalItems) }}</p>
                </div>
            </div>
        </div>

        <div class="animate-card bg-white p-6 rounded-2xl shadow-lg border-l-4 border-green-500 hover:shadow-xl transition-all duration-300 hover:-translate-y-1" style="animation-delay: 300ms;">
            <div class="flex items-center gap-4">
                <div class="p-4 rounded-xl bg-green-50 text-green-600 flex-shrink-0">
                    <i class="fas fa-bell text-2xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-500 font-medium">Notifications</p>
                    <p class="text-3xl font-extrabold text-gray-800">{{ $unreadNotifications->count() }}</p>
                </div>
            </div>
        </div>

        <div class="animate-card bg-white p-6 rounded-2xl shadow-lg border-l-4 border-amber-500 hover:shadow-xl transition-all duration-300 hover:-translate-y-1" style="animation-delay: 350ms;">
            <div class="flex items-center gap-4">
                <div class="p-4 rounded-xl bg-amber-50 text-amber-600 flex-shrink-0">
                    <i class="fas fa-clock text-2xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-500 font-medium">Pending Actions</p>
                    <p class="text-3xl font-extrabold text-gray-800">{{ number_format($pendingActions) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- =============================================================== --}}
    {{-- 3. Total Value & Request Status Grid --}}
    {{-- =============================================================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Total Value Card --}}
        <div class="animate-card bg-white rounded-2xl shadow-lg p-6" style="animation-delay: 400ms;">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-800">Total Request Value</h3>
                <div class="bg-emerald-100 text-emerald-600 rounded-full h-12 w-12 flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-xl"></i>
                </div>
            </div>
            <p class="text-4xl font-extrabold text-[#1a3a2d]">₱{{ number_format($myTotalValue, 2) }}</p>
            <p class="text-sm text-gray-500 mt-2">Estimated cost of all your requests</p>
        </div>

        {{-- Request Status Breakdown --}}
        <div class="animate-card bg-white rounded-2xl shadow-lg p-6 lg:col-span-2" style="animation-delay: 450ms;">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Request Status Breakdown</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @forelse($myRequestsByStatus as $status => $count)
                    @php
                        $style = $statusStyles[$status] ?? $statusStyles['default'];
                    @endphp
                    <div class="flex items-center p-4 rounded-xl hover:bg-gray-50 transition-colors border border-gray-100">
                        <div class="bg-{{ $style['color'] }}-100 text-{{ $style['color'] }}-600 h-10 w-10 flex items-center justify-center rounded-lg mr-4 flex-shrink-0">
                            <i class="{{ $style['icon'] }}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-700 text-sm truncate">{{ $status }}</p>
                        </div>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($count) }}</p>
                    </div>
                @empty
                    <div class="col-span-2 text-center text-gray-500 py-8">
                        <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                        <p class="font-medium">No requests yet</p>
                        <p class="text-xs">Start by creating your first purchase request!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- =============================================================== --}}
    {{-- 4. Recent Requests & Activity --}}
    {{-- =============================================================== --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        {{-- My Recent Requests --}}
        <div class="animate-card bg-white rounded-2xl shadow-lg p-6" style="animation-delay: 500ms;">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-800">My Recent Requests</h3>
                <a href="{{ route('employee.purchase-requests.index') }}" class="text-sm text-[#1a3a2d] font-semibold hover:underline">View All →</a>
            </div>
            <div class="space-y-3">
                @forelse($myRecentRequests as $request)
                    @php
                        $statusStyle = $statusStyles[$request->status->status_name ?? 'default'] ?? $statusStyles['default'];
                    @endphp
                    <div class="border border-gray-100 rounded-xl p-4 hover:border-[#1a3a2d]/30 transition-all hover:shadow-md">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-800 text-sm">{{ $request->pr_no }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Purpose: {{ Str::limit($request->purpose ?? 'N/A', 50) }}
                                </p>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-{{ $statusStyle['color'] }}-100 text-{{ $statusStyle['color'] }}-700">
                                        <i class="{{ $statusStyle['icon'] }} text-[10px]"></i>
                                        {{ $request->status->status_name ?? 'Unknown' }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-xs text-gray-500">{{ $request->created_at->format('M d, Y') }}</p>
                                <button type="button" data-show-url="{{ route('employee.purchase-requests.show', $request->pr_no) }}" class="js-view-employee-pr text-xs text-[#1a3a2d] font-semibold mt-1 inline-block">View →</button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-500 py-12">
                        <i class="fas fa-file-alt fa-3x text-gray-300 mb-4"></i>
                        <p class="font-medium">No requests found</p>
                        <p class="text-xs">Your submitted requests will appear here.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- My Recent Activity --}}
        <div class="animate-card bg-white rounded-2xl shadow-lg p-6" style="animation-delay: 550ms;">
            <h3 class="text-xl font-bold text-gray-800 mb-4">My Recent Activity</h3>
            <div class="space-y-3 max-h-96 overflow-y-auto">
                @forelse($myRecentActivity as $log)
                    @php
                        $actionColor = $logActionStyles[strtolower($log->action)] ?? $logActionStyles['default'];
                    @endphp
                    <div class="border-l-4 border-{{ $actionColor }}-400 pl-4 py-2 hover:bg-gray-50 rounded transition-colors">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2 py-1 text-xs font-bold uppercase text-{{ $actionColor }}-800 bg-{{ $actionColor }}-100 rounded">
                                        {{ $log->action }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-700">{{ $log->description }}</p>
                            </div>
                            <p class="text-xs text-gray-500 flex-shrink-0">{{ \Carbon\Carbon::parse($log->log_time)->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-500 py-12">
                        <i class="fas fa-history fa-3x text-gray-300 mb-4"></i>
                        <p class="font-medium">No recent activity</p>
                        <p class="text-xs">Your actions will be logged here.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- =============================================================== --}}
    {{-- 5. Notifications Panel --}}
    {{-- =============================================================== --}}
    @if($unreadNotifications->count() > 0)
    <div class="animate-card bg-white rounded-2xl shadow-lg p-6" style="animation-delay: 600ms;">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-800">Recent Notifications</h3>
            <a href="{{ route('notifications.index') }}" class="text-sm text-[#1a3a2d] font-semibold hover:underline">View All →</a>
        </div>
        <div class="space-y-3">
            @foreach($unreadNotifications->take(5) as $notification)
                <div class="border border-amber-200 bg-amber-50/30 rounded-xl p-4 hover:bg-amber-50 transition-colors">
                    <div class="flex items-start gap-3">
                        <div class="bg-amber-100 text-amber-600 rounded-full h-10 w-10 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-800 text-sm">{{ $notification->data['title'] ?? 'Notification' }}</p>
                            <p class="text-xs text-gray-600 mt-1">{{ $notification->data['message'] ?? 'You have a new notification.' }}</p>
                            <p class="text-xs text-gray-500 mt-2">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

{{-- PR Details Modal - placed outside content to ensure proper z-index layering --}}
@include('employee.purchase_requests.partials.details-modal')

@push('scripts')
@vite('resources/js/employee-purchase-requests.js')
<script>
    window.purchaseRequestWorkflowConfig = {
        storeUrl: '{{ route("employee.purchase-requests.store") }}',
        showUrlTemplate: '{{ route("employee.purchase-requests.show", ["purchase_request" => "__PR__"]) }}',
        decisionUrlTemplate: '{{ route("employee.purchase-requests.items.decision", ["purchase_request_item" => "__PRI__"]) }}',
        fundAllocationLookupUrl: '{{ route("employee.fund-allocations.show", ["id" => "__ID__"]) }}',
        statuses: {
            forApproval: {{ \App\Models\Status::PR_FOR_APPROVAL }},
            declined: {{ \App\Models\Status::PR_CANCELLED }}
        }
    };

    // Duplicate the employee config expected by the details modal
    window.employeePrConfig = {
        storeUrl: '{{ route("employee.purchase-requests.store") }}',
        showUrlTemplate: '{{ route("employee.purchase-requests.show", ["purchase_request" => "__PR__"]) }}',
        decisionUrlTemplate: '{{ route("employee.purchase-requests.items.decision", ["purchase_request_item" => "__PRI__"]) }}',
        canSubmit: false,
        statuses: {
            pending: {{ \App\Models\Status::PR_FOR_RECOMMENDATION }},
            forApproval: {{ \App\Models\Status::PR_FOR_APPROVAL }},
            approved: {{ \App\Models\Status::PR_APPROVED }},
            declined: {{ \App\Models\Status::PR_CANCELLED }},
        },
    };
</script>
@endpush
@endsection
