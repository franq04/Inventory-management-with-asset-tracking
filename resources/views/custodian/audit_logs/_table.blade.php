<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse ($logs as $log)
        @php
            // Dynamic Action Badges
            $actionLower = strtolower($log->action ?? '');
            $badgeColor = 'bg-gray-100 text-gray-700';
            $borderColor = 'border-gray-200';
            $icon = 'fa-bolt';
            $iconBg = 'bg-gray-500';
            
            if (str_contains($actionLower, 'create') || str_contains($actionLower, 'add')) {
                $badgeColor = 'bg-emerald-100 text-emerald-700';
                $borderColor = 'border-emerald-200';
                $icon = 'fa-plus-circle';
                $iconBg = 'bg-emerald-500';
            } elseif (str_contains($actionLower, 'update') || str_contains($actionLower, 'edit')) {
                $badgeColor = 'bg-blue-100 text-blue-700';
                $borderColor = 'border-blue-200';
                $icon = 'fa-pencil-alt';
                $iconBg = 'bg-blue-500';
            } elseif (str_contains($actionLower, 'delete') || str_contains($actionLower, 'remove')) {
                $badgeColor = 'bg-red-100 text-red-700';
                $borderColor = 'border-red-200';
                $icon = 'fa-trash-alt';
                $iconBg = 'bg-red-500';
            } elseif (str_contains($actionLower, 'login')) {
                $badgeColor = 'bg-purple-100 text-purple-700';
                $borderColor = 'border-purple-200';
                $icon = 'fa-sign-in-alt';
                $iconBg = 'bg-purple-500';
            } elseif (str_contains($actionLower, 'logout')) {
                $badgeColor = 'bg-orange-100 text-orange-700';
                $borderColor = 'border-orange-200';
                $icon = 'fa-sign-out-alt';
                $iconBg = 'bg-orange-500';
            } elseif (str_contains($actionLower, 'status')) {
                $badgeColor = 'bg-sky-100 text-sky-700';
                $borderColor = 'border-sky-200';
                $icon = 'fa-exchange-alt';
                $iconBg = 'bg-sky-500';
            }
        @endphp
        
        <div class="bg-white border-2 {{ $borderColor }} rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden">
            <div class="p-6">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div class="flex items-center gap-3 flex-1">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg shadow-md">
                            {{ strtoupper(substr($log->account->username ?? 'S', 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-900 text-lg">{{ $log->account->username ?? 'System' }}</h3>
                            @if($log->account?->role)
                            <span class="inline-block text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full mt-1">
                                {{ ucfirst(str_replace('_', ' ', $log->account->role)) }}
                            </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-full {{ $iconBg }} flex items-center justify-center shadow-md">
                            <i class="fa-solid {{ $icon }} text-white text-lg"></i>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold uppercase tracking-wide {{ $badgeColor }} shadow-sm">
                        {{ $log->action }}
                    </span>
                </div>
                
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-4">
                    <p class="text-gray-700 text-sm leading-relaxed">{{ $log->description }}</p>
                </div>
                
                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <div class="flex items-center gap-2 text-gray-600">
                        <i class="far fa-clock text-sm"></i>
                        <div>
                            <div class="text-sm font-semibold">{{ optional($log->log_time)->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-500">{{ optional($log->log_time)->format('h:i:s A') }} <span class="text-[10px]">(PHT)</span></div>
                        </div>
                    </div>
                    
                    @if($log->table_name)
                    <div class="flex items-center gap-2 text-gray-500 text-xs">
                        <i class="fas fa-database"></i>
                        <span>{{ $log->table_name }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full bg-white border border-gray-100 rounded-2xl shadow-lg p-16 text-center">
            <i class="fas fa-history text-6xl text-gray-300 mb-4"></i>
            <p class="font-bold text-xl text-gray-700 mb-2">No audit logs found.</p>
            <p class="text-sm text-gray-500">Try adjusting your search filters or date range.</p>
        </div>
    @endforelse
</div>

@if ($logs->hasPages())
<div class="flex justify-center mt-8 pagination-links">
    {{ $logs->links() }}
</div>
@endif