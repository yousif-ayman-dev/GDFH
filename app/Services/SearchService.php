<?php

namespace App\Services;

use App\Models\FreelancerProfile;
use App\Models\Project;
use App\Models\Service;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SearchService
{
    /**
     * Search across all authorized entities.
     *
     * @return array{
     *     query: string,
     *     type: string,
     *     counts: array<string, int>,
     *     results: array<string, Collection>
     * }
     */
    public function search(User $user, ?string $term, string $type = 'all'): array
    {
        $term = trim((string) $term);

        if (empty($term)) {
            return [
                'query' => '',
                'type' => $type,
                'counts' => [
                    'projects' => 0,
                    'tasks' => 0,
                    'teams' => 0,
                    'services' => 0,
                    'freelancers' => 0,
                    'total' => 0,
                ],
                'results' => [
                    'projects' => collect(),
                    'tasks' => collect(),
                    'teams' => collect(),
                    'services' => collect(),
                    'freelancers' => collect(),
                ],
            ];
        }

        $projects = in_array($type, ['all', 'projects']) ? $this->searchProjects($user, $term) : collect();
        $tasks = in_array($type, ['all', 'tasks']) ? $this->searchTasks($user, $term) : collect();
        $teams = in_array($type, ['all', 'teams']) ? $this->searchTeams($user, $term) : collect();
        $services = in_array($type, ['all', 'services']) ? $this->searchServices($user, $term) : collect();
        $freelancers = in_array($type, ['all', 'freelancers']) ? $this->searchFreelancers($term) : collect();

        $counts = [
            'projects' => in_array($type, ['all', 'projects']) ? $projects->count() : $this->getProjectsQuery($user, $term)->count(),
            'tasks' => in_array($type, ['all', 'tasks']) ? $tasks->count() : $this->getTasksQuery($user, $term)->count(),
            'teams' => in_array($type, ['all', 'teams']) ? $teams->count() : $this->getTeamsQuery($user, $term)->count(),
            'services' => in_array($type, ['all', 'services']) ? $services->count() : $this->getServicesQuery($user, $term)->count(),
            'freelancers' => in_array($type, ['all', 'freelancers']) ? $freelancers->count() : $this->getFreelancersQuery($term)->count(),
        ];
        $counts['total'] = array_sum($counts);

        return [
            'query' => $term,
            'type' => $type,
            'counts' => $counts,
            'results' => [
                'projects' => $projects,
                'tasks' => $tasks,
                'teams' => $teams,
                'services' => $services,
                'freelancers' => $freelancers,
            ],
        ];
    }

    protected function getProjectsQuery(User $user, string $term): Builder
    {
        return Project::query()
            ->where(function (Builder $query) use ($user) {
                $query->where('owner_id', $user->id)
                    ->orWhereIn('visibility', ['public', 'marketplace'])
                    ->orWhereHas('members', function (Builder $q) use ($user) {
                        $q->where('user_id', $user->id);
                    })
                    ->orWhereHas('teams.members', function (Builder $q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })
            ->where(function (Builder $query) use ($term) {
                $query->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', "%{$term}%")
                    ->orWhere('category', 'like', "%{$term}%");
            });
    }

    public function searchProjects(User $user, string $term, int $limit = 20): Collection
    {
        return $this->getProjectsQuery($user, $term)
            ->with(['owner'])
            ->latest()
            ->take($limit)
            ->get();
    }

    protected function getTasksQuery(User $user, string $term): Builder
    {
        return Task::query()
            ->where(function (Builder $query) use ($user) {
                $query->where('created_by', $user->id)
                    ->orWhere('assigned_to', $user->id)
                    ->orWhereHas('project', function (Builder $pQuery) use ($user) {
                        $pQuery->where('owner_id', $user->id)
                            ->orWhereIn('visibility', ['public', 'marketplace'])
                            ->orWhereHas('members', function (Builder $q) use ($user) {
                                $q->where('user_id', $user->id);
                            })
                            ->orWhereHas('teams.members', function (Builder $q) use ($user) {
                                $q->where('user_id', $user->id);
                            });
                    });
            })
            ->where(function (Builder $query) use ($term) {
                $query->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
    }

    public function searchTasks(User $user, string $term, int $limit = 20): Collection
    {
        return $this->getTasksQuery($user, $term)
            ->with(['project', 'assignee'])
            ->latest()
            ->take($limit)
            ->get();
    }

    protected function getTeamsQuery(User $user, string $term): Builder
    {
        return Team::query()
            ->where(function (Builder $query) use ($user) {
                $query->where('owner_id', $user->id)
                    ->orWhereHas('members', function (Builder $q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })
            ->where(function (Builder $query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
    }

    public function searchTeams(User $user, string $term, int $limit = 20): Collection
    {
        return $this->getTeamsQuery($user, $term)
            ->with(['owner'])
            ->withCount('members')
            ->latest()
            ->take($limit)
            ->get();
    }

    protected function getServicesQuery(?User $user, string $term): Builder
    {
        return Service::query()
            ->where(function (Builder $query) use ($user) {
                $query->where('status', 'active');
                if ($user) {
                    $query->orWhere('user_id', $user->id);
                }
            })
            ->where(function (Builder $query) use ($term) {
                $query->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('category', 'like', "%{$term}%");
            });
    }

    public function searchServices(User $user, string $term, int $limit = 20): Collection
    {
        return $this->getServicesQuery($user, $term)
            ->with(['user'])
            ->latest()
            ->take($limit)
            ->get();
    }

    protected function getFreelancersQuery(string $term): Builder
    {
        return User::query()
            ->where(function (Builder $query) {
                $query->where('account_type', 'freelancer')
                    ->orWhereHas('freelancerProfile')
                    ->orWhereHas('services');
            })
            ->where(function (Builder $query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('username', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhereHas('freelancerProfile', function (Builder $q) use ($term) {
                        $q->where('title', 'like', "%{$term}%")
                            ->orWhere('bio', 'like', "%{$term}%")
                            ->orWhere('location', 'like', "%{$term}%");
                    });
            });
    }

    public function searchFreelancers(string $term, int $limit = 20): Collection
    {
        return $this->getFreelancersQuery($term)
            ->with(['freelancerProfile', 'services'])
            ->latest()
            ->take($limit)
            ->get();
    }
}
