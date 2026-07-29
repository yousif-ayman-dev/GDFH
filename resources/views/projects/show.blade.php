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

      @if ($errors->any())
      <div class="mb-6 p-4 bg-red-100 text-red-800 rounded-lg">
        <p class="font-semibold">
          Please fix the following errors:
        </p>

        <ul class="mt-2 list-disc list-inside text-sm">
          @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
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
              <p class="text-sm text-gray-500">Owner</p>
              <p class="mt-1 font-medium text-gray-900">
                {{ $project->owner->name }}
              </p>
              <p class="text-sm text-gray-500">
                {{ $project->owner->email }}
              </p>
            </div>

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

          {{-- Project Members --}}
          <div class="mt-10 pt-6 border-t">
            <div class="flex items-center justify-between gap-4">
              <div>
                <h3 class="text-lg font-semibold text-gray-900">
                  Project Members
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                  Add members and manage their roles and status.
                </p>
              </div>

              <span class="px-3 py-1 text-sm rounded-full bg-gray-100 text-gray-700">
                {{ $project->memberRecords->count() }}
                {{ $project->memberRecords->count() === 1 ? 'Member' : 'Members' }}
              </span>
            </div>

            {{-- Add Member --}}
            <div class="mt-6 p-4 border rounded-lg bg-gray-50">
              <h4 class="font-semibold text-gray-900">
                Add Member
              </h4>

              <form method="POST" action="{{ route('projects.members.store', $project) }}"
                class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                @csrf

                <div>
                  <label for="user_id" class="block text-sm font-medium text-gray-700">
                    User ID
                  </label>

                  <input id="user_id" name="user_id" type="number" min="1" value="{{ old('user_id') }}" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                  <label for="role" class="block text-sm font-medium text-gray-700">
                    Role
                  </label>

                  <select id="role" name="role" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="project_manager" @selected(old('role')==='project_manager' )>
                      Project Manager
                    </option>

                    <option value="team_leader" @selected(old('role')==='team_leader' )>
                      Team Leader
                    </option>

                    <option value="member" @selected(old('role', 'member' )==='member' )>
                      Member
                    </option>

                    <option value="viewer" @selected(old('role')==='viewer' )>
                      Viewer
                    </option>
                  </select>
                </div>

                <div class="flex items-end">
                  <button type="submit"
                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 transition">
                    Add Member
                  </button>
                </div>
              </form>
            </div>

            {{-- Members List --}}
            <div class="mt-6 space-y-4">

              @forelse ($project->memberRecords as $memberRecord)
              <div class="p-4 border rounded-lg">

                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

                  <div>
                    <p class="font-semibold text-gray-900">
                      {{ $memberRecord->user->name }}
                    </p>

                    <p class="text-sm text-gray-500">
                      {{ $memberRecord->user->email }}
                    </p>

                    <p class="mt-1 text-xs text-gray-400">
                      User ID: {{ $memberRecord->user_id }}
                    </p>
                  </div>

                  <form method="POST" action="{{ route('projects.members.update', [$project, $memberRecord]) }}"
                    class="flex flex-col sm:flex-row gap-3">
                    @csrf
                    @method('PATCH')

                    <select name="role"
                      class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

                      <option value="project_manager" @selected($memberRecord->role === 'project_manager')>
                        Project Manager
                      </option>

                      <option value="team_leader" @selected($memberRecord->role === 'team_leader')>
                        Team Leader
                      </option>

                      <option value="member" @selected($memberRecord->role === 'member')>
                        Member
                      </option>

                      <option value="viewer" @selected($memberRecord->role === 'viewer')>
                        Viewer
                      </option>
                    </select>

                    <select name="status"
                      class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

                      <option value="pending" @selected($memberRecord->status === 'pending')>
                        Pending
                      </option>

                      <option value="active" @selected($memberRecord->status === 'active')>
                        Active
                      </option>

                      <option value="suspended" @selected($memberRecord->status === 'suspended')>
                        Suspended
                      </option>

                      <option value="left" @selected($memberRecord->status === 'left')>
                        Left
                      </option>
                    </select>

                    <button type="submit"
                      class="inline-flex justify-center items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                      Update
                    </button>
                  </form>
                </div>

                <div class="mt-4 pt-4 border-t flex items-center justify-between gap-4">
                  <div class="text-sm text-gray-500">
                    Joined:
                    {{ $memberRecord->joined_at
                                                ? $memberRecord->joined_at->format('M d, Y')
                                                : 'Not specified' }}
                  </div>

                  <form method="POST" action="{{ route('projects.members.destroy', [$project, $memberRecord]) }}"
                    onsubmit="return confirm('Remove this member from the project?');">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800">
                      Remove Member
                    </button>
                  </form>
                </div>

              </div>
              @empty
              <div class="p-6 text-center border rounded-lg bg-gray-50">
                <p class="text-gray-500">
                  No members have been added to this project yet.
                </p>
              </div>
              @endforelse

            </div>
          </div>

          {{-- Danger Zone --}}
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
