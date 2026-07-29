<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Create Team') }}
      </h2>

      <a href="{{ route('teams.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
        Back to Teams
      </a>
    </div>
  </x-slot>

  <div class="py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
          <form method="POST" action="{{ route('teams.store') }}" class="space-y-4">
            @csrf

            <div>
              <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
              <input id="name" name="name" type="text" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
              @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
              <textarea id="description" name="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description') }}</textarea>
              @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>

            <div class="grid gap-4 md:grid-cols-2">
              <div>
                <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                <select id="type" name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                  <option value="permanent" {{ old('type', 'permanent') === 'permanent' ? 'selected' : '' }}>Permanent</option>
                  <option value="project_based" {{ old('type') === 'project_based' ? 'selected' : '' }}>Project based</option>
                </select>
                @error('type')
                  <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
              </div>

              <div>
                <label for="visibility" class="block text-sm font-medium text-gray-700">Visibility</label>
                <select id="visibility" name="visibility" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                  <option value="private" {{ old('visibility', 'private') === 'private' ? 'selected' : '' }}>Private</option>
                  <option value="public" {{ old('visibility') === 'public' ? 'selected' : '' }}>Public</option>
                </select>
                @error('visibility')
                  <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
              </div>
            </div>

            <div class="flex items-center justify-end gap-3">
              <a href="{{ route('teams.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
              <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Save Team</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
