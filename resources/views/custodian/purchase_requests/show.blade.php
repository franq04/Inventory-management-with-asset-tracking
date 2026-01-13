@extends('layouts.app')

@section('title', 'Purchase Request Details')

@section('content')
<div class="bg-white rounded-2xl shadow-lg p-6">
    <h2 class="text-2xl font-bold text-[#1a3a2d] mb-4">Purchase Request {{ $purchaseRequest->pr_no }}</h2>
    <p class="text-gray-600">This page is primarily served for AJAX consumers. Please return to the <a href="{{ route('custodian.requests.index') }}" class="text-[#1a3a2d] underline">purchase request queue</a>.</p>
</div>
@endsection
