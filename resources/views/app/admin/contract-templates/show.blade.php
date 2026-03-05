@extends('app.layouts.standalone')

@section('content')
<div class="flex min-h-screen w-full flex-col">
    <header class="shrink-0 border-b border-white/10 bg-[#1e293b] px-4 py-2 flex items-center justify-between">
        <a href="{{ route('app.admin.contract-templates.index') }}" target="_self" class="text-sm font-medium text-slate-400 hover:text-white transition-colors no-underline">
            ← Retour à la bibliothèque
        </a>
        <span class="text-xs text-slate-500">{{ $template->name }}</span>
    </header>
    <div class="flex-1 w-full overflow-auto">
        <div class="contract-document min-h-full w-full bg-white text-slate-800 p-8 sm:p-10 md:p-12">
            <style>
                .contract-document * { max-width: 100%; box-sizing: border-box; }
            </style>
            {!! $previewHtml !!}
        </div>
    </div>
</div>
@endsection
