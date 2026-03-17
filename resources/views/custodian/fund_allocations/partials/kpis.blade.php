<div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4 lg:min-w-[620px]">
    <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm">
        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/70">Fund Clusters</p>
        <p class="mt-2 text-3xl font-bold">{{ number_format($kpis['totalClusters']) }}</p>
    </div>
    <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm">
        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/70">Total Budget</p>
        <p class="mt-2 text-2xl font-bold">₱{{ number_format($kpis['totalBudget'], 2) }}</p>
    </div>
    <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm">
        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/70">Allocated</p>
        <p class="mt-2 text-2xl font-bold">₱{{ number_format($kpis['totalAllocated'], 2) }}</p>
    </div>
    <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm">
        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/70">Utilization</p>
        <p class="mt-2 text-3xl font-bold">{{ number_format($kpis['utilizationRate'], 1) }}%</p>
    </div>
</div>
