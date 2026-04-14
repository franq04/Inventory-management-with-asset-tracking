@extends('layouts.app')

@section('title', 'Custodian User Manual')

@section('content')
<div id="top" class="space-y-8 max-w-5xl mx-auto">
    <div class="bg-white rounded-xl shadow-md p-6 border border-emerald-100">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center mb-2">
                <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-book-open text-[#1a3a2d] text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">{{ $manualTitle }}</h1>
                    <p class="text-gray-500">Complete guide for Custodian workflows in the Asset Tracking System</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('custodian.user_manual.export') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-[#1a3a2d] text-white hover:bg-emerald-800 transition-colors text-sm font-semibold">
                    <i class="fas fa-download mr-2"></i>Export Manual
                </a>
                <button type="button" onclick="window.print()" class="inline-flex items-center px-4 py-2 rounded-lg bg-emerald-100 text-[#1a3a2d] hover:bg-emerald-200 transition-colors text-sm font-semibold">
                    <i class="fas fa-print mr-2"></i>Print
                </button>
            </div>
        </div>

        <p class="text-gray-600 leading-relaxed mt-3">
            {{ $manualIntro }}
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 border border-emerald-100">
        <h2 class="text-lg font-bold text-gray-800 mb-4"><i class="fas fa-list-ol text-[#1a3a2d] mr-2"></i>Table of Contents</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($sections as $sectionIndex => $section)
                <a href="#section-{{ $sectionIndex + 1 }}" class="flex items-center p-3 rounded-lg hover:bg-emerald-50 transition-colors group">
                    <span class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center mr-3 group-hover:bg-emerald-200 transition-colors">
                        <span class="text-[#1a3a2d] font-bold text-sm">{{ $sectionIndex + 1 }}</span>
                    </span>
                    <span class="text-gray-700 group-hover:text-[#1a3a2d] transition-colors">{{ $section['category'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    @foreach($sections as $sectionIndex => $section)
        <div id="section-{{ $sectionIndex + 1 }}" class="bg-white rounded-xl shadow-md overflow-hidden border border-emerald-100">
            <div class="bg-[#1a3a2d] px-6 py-4">
                <h2 class="text-xl font-bold text-white">
                    <i class="fas fa-folder-open mr-2"></i>{{ $sectionIndex + 1 }}. {{ $section['category'] }}
                </h2>
            </div>

            <div class="p-6 space-y-5">
                <p class="text-gray-600 leading-relaxed">{{ $section['intro'] }}</p>

                @foreach($section['items'] as $step)
                    <div id="{{ $step['id'] }}" class="space-y-3">
                        <div class="flex items-start space-x-4 bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <span class="flex-shrink-0 w-8 h-8 bg-[#1a3a2d] rounded-full flex items-center justify-center text-white font-bold text-sm">{{ $step['number'] }}</span>
                            <div class="min-w-0">
                                <h3 class="font-semibold text-gray-800">{{ $step['title'] }}</h3>
                                <p class="text-gray-600 text-sm mt-1">{{ $step['description'] }}</p>
                            </div>
                        </div>

                        <div class="border-2 border-dashed border-emerald-200 rounded-xl p-2 bg-white">
                            <img src="{{ $step['image_url'] }}" alt="{{ $step['title'] }} screenshot" class="block mx-auto rounded-lg" style="height: 520px; width: auto;">
                        </div>

                        @if(!empty($step['elements']))
                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 pt-2">
                                @foreach($step['elements'] as $element)
                                    <div class="border border-gray-200 rounded-lg p-4 bg-white hover:shadow-md transition-shadow">
                                        <div class="flex items-center gap-3 mb-3">
                                            <span class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center text-[#1a3a2d]">
                                                <i class="{{ $element['icon'] }}"></i>
                                            </span>
                                            <h4 class="font-semibold text-gray-800">{{ $element['title'] }}</h4>
                                        </div>

                                        <div class="mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-[#1a3a2d]">
                                            {{ $element['chip'] }}
                                        </div>

                                        <p class="text-sm text-gray-600 leading-relaxed">{{ $element['description'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach

                <div class="pt-2">
                    <a href="#top" class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors text-sm">
                        <i class="fas fa-arrow-up mr-2"></i>Back to Top
                    </a>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
