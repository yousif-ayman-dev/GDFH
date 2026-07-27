<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between gap-4">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ $project->title }}
      </h2>

      <div class="flex items-center gap-3">
        <a href="{{ route('projects.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
          Back to Projects
        </a>

        <a href="{{ route('projects.edit', $project) }}"
          class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
          Edit Project
        </a>
      </div>
    </div>
  </x-slot>

  <div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

      @if (session('success'))
      <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg">
        {{ session('success') }}
      </div>
      @endif

      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">

          <div class="flex flex-wrap items-center gap-3 mb-6">
            <span class="px-3 py-1 text-sm rounded-full bg-gray-100 text-gray-700">
              {{ ucfirst($project->status) }}
            </span>

            <span class="px-3 py-1 text-sm rounded-full bg-gray-100 text-gray-700">
              {{ ucfirst($project->visibility) }}
            </span>

            @if ($project->category)
            <span class="px-3 py-1 text-sm rounded-full bg-gray-100 text-gray-700">
              {{ $project->category }}
            </span>
            @endif
          </div>

          <div>
            <h3 class="text-lg font-semibold text-gray-900">
              Description
            </h3>

            <p class="mt-2 text-gray-600 whitespace-pre-line">
              {{ $project->description }}
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">

            <div>
              <p class="text-sm text-gray-500">Budget Type</p>
              <p class="mt-1 font-medium text-gray-900">
                {{ $project->budget_type
                                    ? ucfirst($project->budget_type)
                                    : 'Not specified' }}
              </p>
            </div>

            <div>
              <p class="text-sm text-gray-500">Currency</p>
              <p class="mt-1 font-medium text-gray-900">
                {{ strtoupper($project->currency) }}
              </p>
            </div>

            <div>
              <p class="text-sm text-gray-500">Minimum Budget</p>
              <p class="mt-1 font-medium text-gray-900">
                {{ $project->budget_min !== null
                                    ? number_format($project->budget_min, 2)
                                    : 'Not specified' }}
              </p>
            </div>

            <div>
              <p class="text-sm text-gray-500">Maximum Budget</p>
              <p class="mt-1 font-medium text-gray-900">
                {{ $project->budget_max !== null
                                    ? number_format($project->budget_max, 2)
                                    : 'Not specified' }}
              </p>
            </div>

            <div>
              <p class="text-sm text-gray-500">Start Date</p>
              <p class="mt-1 font-medium text-gray-900">
                {{ $project->start_date
                                    ? $project->start_date->format('M d, Y')
                                    : 'Not specified' }}
              </p>
            </div>

            <div>
              <p class="text-sm text-gray-500">Deadline</p>
              <p class="mt-1 font-medium text-gray-900">
                {{ $project->deadline
                                    ? $project->deadline->format('M d, Y')
                                    : 'Not specified' }}
              </p>
            </div>

          </div>

          <div class="mt-10 pt-6 border-t">
            <h3 class="text-lg font-semibold text-gray-900">
              Danger Zone
            </h3>

            <p class="mt-1 text-sm text-gray-500">
              Deleting this project cannot be undone.
            </p>

            <form method="POST" action="{{ route('projects.destroy', $project) }}" class="mt-4"
              onsubmit="return confirm('Are you sure you want to delete this project?');">
              @csrf
              @method('DELETE')

              <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 transition">
                Delete Project
              </button>
            </form>
          </div>

        </div>
      </div>

    </div>
  </div>
</x-app-layout>
