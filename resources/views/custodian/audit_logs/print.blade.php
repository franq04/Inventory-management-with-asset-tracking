<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page {
                size: landscape;
                margin: 0.5in;
            }
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                margin: 0;
                background: #fff !important;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-before: always;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            thead {
                display: table-header-group;
            }
            tfoot {
                display: table-footer-group;
            }
        }
        .border-black {
            border-color: #000 !important;
        }
        body {
            font-family: 'Arial', sans-serif;
        }
    </style>
</head>
<body class="bg-white">
    <!-- Header Section with Logos -->
    <div class="mb-6 border-b-2 border-black pb-4">
        <div class="flex items-center justify-between">
            <img src="{{ asset('images/bpi-logo.png') }}" alt="Department of Agriculture" class="h-16 w-16 object-contain">
            <div class="flex-1 text-center">
                <h1 class="text-2xl font-bold uppercase">Audit Trail Report</h1>
                <p class="text-sm mt-1">System Activity Logs</p>
                @if($search || $dateFrom || $dateTo)
                <p class="text-xs text-gray-600 mt-2">
                    Filters Applied:
                    @if($search) Search: "{{ $search }}" @endif
                    @if($dateFrom) | From: {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} @endif
                    @if($dateTo) | To: {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }} @endif
                </p>
                @endif
            </div>
            <img src="{{ asset('images/pqslogo.png') }}" alt="Plant Quarantine Service" class="h-16 w-16 object-contain">
        </div>
    </div>

    @php
        $recordsPerPage = 20;
        $chunks = $logs->chunk($recordsPerPage);
        $totalRecords = $logs->count();
        
        // Group logs by user for better separation
        $lastUser = null;
    @endphp

    @foreach($chunks as $chunkIndex => $chunk)
        @if($chunkIndex > 0)
            <div class="page-break"></div>
            <!-- Repeat header on new page -->
            <div class="mb-4 text-center border-b border-black pb-2">
                <h2 class="text-lg font-bold">Audit Trail (continued)</h2>
                <p class="text-xs text-gray-600">Page {{ $chunkIndex + 1 }} of {{ $chunks->count() }}</p>
            </div>
        @endif

        <!-- Table -->
        <table class="w-full border-collapse text-xs">
            <thead>
                <tr class="bg-gray-100 border-black">
                    <th class="border border-black px-2 py-2 text-left font-semibold w-32">Timestamp (PHT)</th>
                    <th class="border border-black px-2 py-2 text-left font-semibold w-32">User</th>
                    <th class="border border-black px-2 py-2 text-left font-semibold w-24">Action</th>
                    <th class="border border-black px-2 py-2 text-left font-semibold">Description</th>
                </tr>
            </thead>
            <tbody>
                @forelse($chunk as $log)
                    @php
                        $currentUser = $log->account->username ?? 'System';
                        $showSeparator = $lastUser !== null && $lastUser !== $currentUser;
                        $lastUser = $currentUser;
                    @endphp
                    @if($showSeparator)
                    <tr class="bg-gray-200">
                        <td colspan="4" class="border border-black px-2 py-1 text-center">
                            <span class="text-xs font-semibold text-gray-600">— Different User —</span>
                        </td>
                    </tr>
                    @endif
                    <tr class="hover:bg-gray-50">
                        <td class="border border-black px-2 py-1.5 text-xs">
                            {{ optional($log->log_time)->format('M d, Y h:i:s A') }}
                        </td>
                        <td class="border border-black px-2 py-1.5 text-xs">
                            <strong>{{ $log->account->username ?? 'System' }}</strong>
                            @if($log->account?->role)
                            <br><span class="text-gray-500">{{ ucfirst(str_replace('_', ' ', $log->account->role)) }}</span>
                            @endif
                        </td>
                        <td class="border border-black px-2 py-1.5 text-xs font-semibold uppercase">
                            {{ $log->action }}
                        </td>
                        <td class="border border-black px-2 py-1.5 text-xs">{{ $log->description }}</td>
                    </tr>
                @empty
                <tr>
                    <td colspan="4" class="border border-black px-2 py-4 text-center text-gray-500">
                        No audit logs found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($chunkIndex === $chunks->count() - 1)
        <!-- Summary on last page -->
        <div class="mt-6 border-t-2 border-black pt-4">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm font-semibold">Total Records: {{ number_format($totalRecords) }}</p>
                    <p class="text-xs text-gray-600 mt-1">Report Generated: {{ now()->format('F d, Y h:i:s A') }} PHT</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-600">Prepared by:</p>
                    <div class="border-t border-black mt-8 pt-1 px-4">
                        <p class="text-xs font-semibold">{{ Auth::user()->full_name ?? 'System Administrator' }}</p>
                        <p class="text-xs text-gray-600">{{ Auth::user()->position->position_name ?? 'Administrator' }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endforeach

    <!-- Print Controls (screen only) -->
    <div class="fixed bottom-4 right-4 no-print flex gap-2">
        <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 flex items-center gap-2">
            <i class="fas fa-print"></i> Print
        </button>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>
