@props(['size' => 'md', 'showText' => true, 'stacked' => false])

@php
  $logoFile = file_exists(public_path('images/my-logo.svg')) ? 'images/my-logo.svg' : 'images/my-logo.png';
  
  $imgHeight = match($size) {
    'lg' => 'h-16 sm:h-20',
    'sm' => 'h-10 sm:h-11',
    default => 'h-12 sm:h-14',
  };
@endphp

<div {{ $attributes->merge(['class' => $stacked ? 'inline-flex flex-col items-center text-center gap-2 group' : 'inline-flex items-center gap-3 group']) }}>
  {{-- Custom Uploaded Tasker Logo Icon --}}
  <div class="relative shrink-0 flex items-center justify-center p-1.5 rounded-2xl bg-white dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 shadow-sm transition-all duration-200 group-hover:scale-105 group-hover:shadow-md">
    <img src="{{ asset($logoFile) }}" class="{{ $imgHeight }} w-auto object-contain rounded-xl" alt="Tasker Logo">
  </div>

  @if($showText)
  <div class="min-w-0 text-start leading-tight space-y-0.5">
    <div class="flex items-center gap-1.5">
      <span class="block text-xl font-black tracking-tight text-slate-900 dark:text-white font-mono">Tasker</span>
      <span class="flex h-2 w-2 rounded-full bg-[rgb(var(--color-copper))] animate-pulse"></span>
    </div>
    <span class="block text-[11px] font-bold text-[rgb(var(--color-copper))] tracking-wide">منصة المشاريع والخدمات</span>
  </div>
  @endif
</div>
