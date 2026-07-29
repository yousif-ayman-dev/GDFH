<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ $team->name }}
      </h2>

      <div class="flex items-center gap-3">
        <a href="{{ route('teams.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
          Back to Teams
        </a>
        <a href="{{ route('teams.edit', $team) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
          Edit Team
        </a>
      </div>
    </div>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

      @if (session('success'))
      <div class="p-4 bg-green-100 text-green-800 rounded-lg">
        {{ session('success') }}
      </div>
      @endif

      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900">
        <p class="text-sm text-gray-500">{{ $team->description ?: 'No description provided.' }}</p>
        <div class="mt-4 flex gap-4 text-sm text-gray-600">
          <span>Type: {{ ucfirst($team->type) }}</span>
          <span>Visibility: {{ ucfirst($team->visibility) }}</span>
          <span>Slug: {{ $team->slug }}</span>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold">Members</h3>
        </div>

        <div class="mt-4 space-y-3">
          @forelse ($team->memberships as $membership)
            <div class="flex items-center justify-between rounded-lg border border-gray-200 p-3">
              <div>
                <p class="font-medium">{{ $membership->user?->name ?? 'Unknown user' }}</p>
                <p class="text-sm text-gray-500">Role: {{ ucfirst($membership->role) }} · Status: {{ ucfirst($membership->status) }}</p>
              </div>
            </div>
          @empty
            <p class="text-gray-500">No members yet.</p>
          @endforelse
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900">
        <form method="POST" action="{{ route('teams.destroy', $team) }}" onsubmit="return confirm('Delete this team?')">
          @csrf
          @method('DELETE')
          <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500">
            Delete Team
          </button>
        </form>
      </div>
    </div>
  </div>
</x-app-layout>
