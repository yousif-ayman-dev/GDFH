@props(['size' => 'md', 'showText' => true, 'stacked' => false])

@php
  $hasCustomLogo = file_exists(public_path('images/my-logo.svg')) || file_exists(public_path('images/my-logo.png'));
  $logoFile = file_exists(public_path('images/my-logo.svg')) ? 'images/my-logo.svg' : 'images/my-logo.png';
  
  $imgHeight = match($size) {
    'lg' => 'h-14 sm:h-16',
    'sm' => 'h-8 sm:h-9',
    default => 'h-10 sm:h-12',
  };
@endphp

<div {{ $attributes->merge(['class' => $stacked ? 'inline-flex flex-col items-center text-center gap-2 group' : 'inline-flex items-center gap-3 group']) }}>
  @if ($hasCustomLogo)
  <div class="relative shrink-0 flex items-center justify-center p-2 rounded-2xl bg-white/90 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 shadow-md transition-all duration-200 group-hover:scale-105">
    <img src="{{ asset($logoFile) }}" class="{{ $imgHeight }} w-auto object-contain rounded-xl" alt="Tasker Logo">
  </div>
  @else
  <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 text-white font-black text-xl shadow-md group-hover:scale-105 transition-transform">
    <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
  </div>
  @endif

  @if($showText)
  <div class="min-w-0 {{ $stacked ? 'text-center' : 'text-start' }} leading-tight space-y-0.5">
    <div class="flex items-center justify-center gap-1.5">
      <span class="block text-xl font-black tracking-tight text-slate-900 dark:text-white font-mono">Tasker</span>
      <span class="flex h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
    </div>
    <span class="block text-[11px] font-bold text-amber-500 tracking-wide">منصة المشاريع والخدمات</span>
  </div>
  @endif
</div>
