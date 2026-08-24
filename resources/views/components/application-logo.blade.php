@props(['size' => 'md', 'showText' => true, 'stacked' => false])

<div {{ $attributes->merge(['class' => $stacked ? 'inline-flex flex-col items-center text-center gap-2 group' : 'inline-flex items-center gap-3 group']) }}>
  {{-- Tasker Official Handshake Emblem --}}
  <div class="relative shrink-0 flex items-center justify-center">
    <svg viewBox="0 0 200 200" class="{{ $size === 'lg' ? 'h-16' : ($size === 'sm' ? 'h-8' : 'h-10') }} w-auto" fill="none" xmlns="http://www.w3.org/2000/svg">
      <!-- Dark Navy T with flared arches -->
      <path d="M 22 20 C 45 20, 58 35, 68 45 L 82 45 L 82 140 C 82 148, 92 154, 100 154 C 108 154, 118 148, 118 140 L 118 45 L 132 45 C 142 35, 155 20, 178 20 C 152 20, 136 32, 126 40 L 74 40 C 64 32, 48 20, 22 20 Z" class="fill-[#0D223A] dark:fill-white" fill-rule="evenodd" />
      <path d="M 76 40 L 124 40 L 124 140 L 76 140 Z" class="fill-[#0D223A] dark:fill-white" />

      <!-- Left Blue Figure (#2B58A8) -->
      <circle cx="50" cy="74" r="14" fill="#2B58A8" />
      <path d="M 32 175 C 32 120, 58 112, 76 132 L 100 152 L 88 162 L 68 146 C 54 135, 46 142, 46 175 Z" fill="#2B58A8" />

      <!-- Right Orange Figure (#F38400) -->
      <circle cx="150" cy="74" r="14" fill="#F38400" />
      <path d="M 168 175 C 168 120, 142 112, 124 132 L 100 152 L 112 162 L 132 146 C 146 135, 154 142, 154 175 Z" fill="#F38400" />
    </svg>
  </div>

  @if($showText)
  <div class="min-w-0 text-start leading-tight">
    <span class="block text-base font-black tracking-widest uppercase text-[rgb(var(--color-text-primary))] font-mono">TASKER</span>
    <span class="block text-[9px] font-semibold text-[rgb(var(--color-text-secondary))] tracking-tight">Find Talent. Get Work Done.</span>
  </div>
  @endif
</div>
