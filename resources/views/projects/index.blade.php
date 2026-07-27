<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('My Projects') }}
      </h2>

      <a href="{{ route('projects.create') }}"
        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
        Create Project
      </a>
    </div>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

      @if (session('success'))
      <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">
        {{ session('success') }}
      </div>
      @endif

      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">

          @forelse ($projects as $project)
          <div class="py-4 border-b last:border-b-0">
            <div class="flex items-start justify-between gap-4">

              <div>
                <h3 class="text-lg font-semibold">
                  <a href="{{ route('projects.show', $project) }}" class="hover:underline">
                    {{ $project->title }}
                  </a>
                </h3>

                @if ($project->category)
                <p class="mt-1 text-sm text-gray-500">
                  {{ $project->category }}
                </p>
                @endif

                <div class="mt-2 flex gap-3 text-sm text-gray-600">
                  <span>
                    Status: {{ ucfirst($project->status) }}
                  </span>

                  <span>
                    Visibility: {{ ucfirst($project->visibility) }}
                  </span>
                </div>
              </div>

              <a href="{{ route('projects.edit', $project) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                Edit
              </a>

            </div>
          </div>
          @empty
          <div class="py-8 text-center text-gray-500">
            You don't have any projects yet.
          </div>
          @endforelse

        </div>
      </div>

      @if ($projects->hasPages())
      <div class="mt-6">
        {{ $projects->links() }}
      </div>
      @endif

    </div>
  </div>
</x-app-layout>
