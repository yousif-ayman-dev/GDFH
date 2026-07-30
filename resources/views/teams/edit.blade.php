<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Edit Team') }}
      </h2>

      <a href="{{ route('teams.show', $team) }}" class="text-sm text-gray-600 hover:text-gray-900">
        Back to Team
      </a>
    </div>
  </x-slot>

  <div class="py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
          <form method="POST" action="{{ route('teams.update', $team) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PATCH')

            <div>
              <label for="name" class="block text-sm font-semibold text-[rgb(var(--color-text-primary))]">اسم الفريق</label>
              <input id="name" name="name" type="text" value="{{ old('name', $team->name) }}" required class="gdfh-input mt-2">
              @error('name')
                <p class="mt-2 text-sm text-[rgb(var(--color-error))]">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="description" class="block text-sm font-semibold text-[rgb(var(--color-text-primary))]">وصف الفريق</label>
              <textarea id="description" name="description" class="gdfh-input mt-2 min-h-[140px] resize-y">{{ old('description', $team->description) }}</textarea>
              @error('description')
                <p class="mt-2 text-sm text-[rgb(var(--color-error))]">{{ $message }}</p>
              @enderror
            </div>

            <div class="grid gap-4 md:grid-cols-2">
              <div>
                <label for="type" class="block text-sm font-semibold text-[rgb(var(--color-text-primary))]">النوع</label>
                <select id="type" name="type" class="gdfh-input mt-2">
                  <option value="permanent" {{ old('type', $team->type) === 'permanent' ? 'selected' : '' }}>دائم</option>
                  <option value="project_based" {{ old('type', $team->type) === 'project_based' ? 'selected' : '' }}>قائم على مشروع</option>
                </select>
                @error('type')
                  <p class="mt-2 text-sm text-[rgb(var(--color-error))]">{{ $message }}</p>
                @enderror
              </div>

              <div>
                <label for="visibility" class="block text-sm font-semibold text-[rgb(var(--color-text-primary))]">الظهور</label>
                <select id="visibility" name="visibility" class="gdfh-input mt-2">
                  <option value="private" {{ old('visibility', $team->visibility) === 'private' ? 'selected' : '' }}>خاص</option>
                  <option value="public" {{ old('visibility', $team->visibility) === 'public' ? 'selected' : '' }}>عام</option>
                </select>
                @error('visibility')
                  <p class="mt-2 text-sm text-[rgb(var(--color-error))]">{{ $message }}</p>
                @enderror
              </div>
            </div>

            <div class="rounded-2xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface-soft))] p-4">
              <label for="logo" class="block text-sm font-semibold text-[rgb(var(--color-text-primary))]">شعار الفريق</label>

              @if ($team->logo_path)
                <div class="mt-3 flex items-center gap-3 rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-3">
                  <img src="{{ Storage::disk('public')->url($team->logo_path) }}" alt="{{ $team->name }}" class="h-14 w-14 rounded-xl object-cover">
                  <div>
                    <p class="text-sm font-semibold text-[rgb(var(--color-text-primary))]">الشعار الحالي</p>
                    <p class="text-xs text-[rgb(var(--color-text-secondary))]">يمكنك اختيار صورة جديدة لاستبداله.</p>
                  </div>
                </div>
              @endif

              <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/jpg,image/webp,image/gif"
                class="mt-4 block w-full text-sm text-[rgb(var(--color-text-secondary))] file:mr-4 file:rounded-lg file:border-0 file:bg-[rgb(var(--color-copper-soft))] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[rgb(var(--color-copper))] hover:file:bg-[rgb(var(--color-copper-soft))]">

              @error('logo')
                <p class="mt-2 text-sm text-[rgb(var(--color-error))]">{{ $message }}</p>
              @enderror
            </div>

            <div class="flex items-center justify-end gap-3">
              <a href="{{ route('teams.show', $team) }}" class="text-sm text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-text-primary))]">إلغاء</a>
              <button type="submit" class="gdfh-btn gdfh-btn-brand">تحديث الفريق</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
