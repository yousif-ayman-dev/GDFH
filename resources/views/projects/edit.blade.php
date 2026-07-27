<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between gap-4">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Edit Project') }}
      </h2>

      <a href="{{ route('projects.show', $project) }}" class="text-sm text-gray-600 hover:text-gray-900">
        Back to Project
      </a>
    </div>
  </x-slot>

  <div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">

          @if ($errors->any())
          <div class="mb-6 p-4 bg-red-100 text-red-800 rounded-lg">
            <ul class="list-disc list-inside">
              @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
          @endif

          <form method="POST" action="{{ route('projects.update', $project) }}">
            @csrf
            @method('PUT')

            <div>
              <x-input-label for="title" value="Project Title" />

              <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                :value="old('title', $project->title)" required autofocus />

              <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>

            <div class="mt-6">
              <x-input-label for="description" value="Description" />

              <textarea id="description" name="description" rows="5" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $project->description) }}</textarea>

              <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <div class="mt-6">
              <x-input-label for="category" value="Category" />

              <x-text-input id="category" name="category" type="text" class="mt-1 block w-full"
                :value="old('category', $project->category)" />

              <x-input-error :messages="$errors->get('category')" class="mt-2" />
            </div>

            <div class="mt-6">
              <x-input-label for="visibility" value="Visibility" />

              <select id="visibility" name="visibility" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="private" @selected(old('visibility', $project->visibility) === 'private')
                  >
                  Private
                </option>

                <option value="marketplace" @selected(old('visibility', $project->visibility) === 'marketplace')
                  >
                  Marketplace
                </option>
              </select>

              <x-input-error :messages="$errors->get('visibility')" class="mt-2" />
            </div>

            <div class="mt-6">
              <x-input-label for="budget_type" value="Budget Type" />

              <select id="budget_type" name="budget_type"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="" @selected(old('budget_type', $project->budget_type) === null || old('budget_type',
                  $project->budget_type) === '')
                  >
                  No budget type
                </option>

                <option value="fixed" @selected(old('budget_type', $project->budget_type) === 'fixed')
                  >
                  Fixed
                </option>

                <option value="hourly" @selected(old('budget_type', $project->budget_type) === 'hourly')
                  >
                  Hourly
                </option>
              </select>

              <x-input-error :messages="$errors->get('budget_type')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
              <div>
                <x-input-label for="budget_min" value="Minimum Budget" />

                <x-text-input id="budget_min" name="budget_min" type="number" min="0" step="0.01"
                  class="mt-1 block w-full" :value="old('budget_min', $project->budget_min)" />

                <x-input-error :messages="$errors->get('budget_min')" class="mt-2" />
              </div>

              <div>
                <x-input-label for="budget_max" value="Maximum Budget" />

                <x-text-input id="budget_max" name="budget_max" type="number" min="0" step="0.01"
                  class="mt-1 block w-full" :value="old('budget_max', $project->budget_max)" />

                <x-input-error :messages="$errors->get('budget_max')" class="mt-2" />
              </div>
            </div>

            <div class="mt-6">
              <x-input-label for="currency" value="Currency" />

              <x-text-input id="currency" name="currency" type="text" maxlength="3" class="mt-1 block w-full uppercase"
                :value="old('currency', $project->currency)" required />

              <x-input-error :messages="$errors->get('currency')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
              <div>
                <x-input-label for="start_date" value="Start Date" />

                <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" :value="old(
                                        'start_date',
                                        $project->start_date
                                            ? $project->start_date->format('Y-m-d')
                                            : ''
                                    )" />

                <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
              </div>

              <div>
                <x-input-label for="deadline" value="Deadline" />

                <x-text-input id="deadline" name="deadline" type="date" class="mt-1 block w-full" :value="old(
                                        'deadline',
                                        $project->deadline
                                            ? $project->deadline->format('Y-m-d')
                                            : ''
                                    )" />

                <x-input-error :messages="$errors->get('deadline')" class="mt-2" />
              </div>
            </div>

            <div class="mt-8 flex items-center justify-end gap-4">
              <a href="{{ route('projects.show', $project) }}" class="text-sm text-gray-600 hover:text-gray-900">
                Cancel
              </a>

              <x-primary-button>
                Save Changes
              </x-primary-button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</x-app-layout>
