<?php

namespace Modules\Task\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Core\Support\TenantContext;
use Modules\Task\Models\Task;
use Modules\Task\Services\TaskPeople;

/**
 * Tasks (companies only): everyone keeps their own to-dos and moves their tasks along;
 * with "assign" a user gives tasks to others and follows them up; with
 * "manage" they see everyone's.
 */
class TaskController extends Controller implements HasMiddleware
{
    public function __construct(private TaskPeople $people) {}

    /**
     * @return list<Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware(function ($request, $next) {
                abort_unless(Task::isAvailableTo($request->user()), 403, 'টাস্ক ম্যানেজমেন্ট শুধু কোম্পানির জন্য (Task management is available to companies only)।');

                return $next($request);
            }),
        ];
    }

    public function index(Request $request): View
    {
        $user = Auth::user();
        $canManage = Task::canManage($user);
        $tab = in_array($request->input('tab'), ['mine', 'reported', 'all'], true) ? $request->input('tab') : 'mine';
        if ($tab === 'all' && ! $canManage) {
            $tab = 'mine';
        }

        $status = $request->input('status', 'open');

        $tasks = Task::query()
            ->visibleTo($user)
            ->when($tab === 'mine', fn ($query) => $query->where('assigned_to', $user->id))
            ->when($tab === 'reported', fn ($query) => $query->where('reported_by', $user->id)->where('assigned_to', '!=', $user->id))
            ->when($status === 'open', fn ($query) => $query->whereIn('status', Task::OPEN_STATUSES))
            ->when(in_array($status, Task::STATUSES, true), fn ($query) => $query->where('status', $status))
            ->when($tab !== 'mine' && $request->filled('assignee'), fn ($query) => $query->where('assigned_to', $request->integer('assignee')))
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($search) => $search->where('title', 'like', '%'.$request->input('q').'%')->orWhere('description', 'like', '%'.$request->input('q').'%')))
            ->with(['reporter:id,name', 'assignee:id,name'])
            ->orderByRaw("CASE status WHEN 'in_progress' THEN 0 WHEN 'todo' THEN 1 WHEN 'completed' THEN 2 ELSE 3 END")
            ->orderByRaw('deadline IS NULL')
            ->orderBy('deadline')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $counts = Task::query()->where('assigned_to', $user->id)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('task::tasks.index', [
            'tasks' => $tasks,
            'tab' => $tab,
            'status' => $status,
            'counts' => $counts,
            'canAssign' => Task::canAssign($user),
            'canManage' => $canManage,
            'people' => $this->people->assignable($this->companyId()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        Task::create($validated + [
            'company_id' => $this->companyId(),
            'shop_id' => app(TenantContext::class)->shopId(),
            'status' => 'todo',
            'reported_by' => Auth::id(),
        ]);

        return back()->with('status', 'টাস্ক যোগ হয়েছে');
    }

    public function edit(Task $task): View
    {
        abort_unless($task->canBeEditedBy(Auth::user()), 403);

        return view('task::tasks.edit', [
            'task' => $task,
            'canAssign' => Task::canAssign(Auth::user()),
            'people' => $this->people->assignable($this->companyId()),
        ]);
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        abort_unless($task->canBeEditedBy(Auth::user()), 403);

        $task->update($this->validated($request, $task));

        return redirect()->route('tasks.index', ['tab' => $task->isPersonal() ? 'mine' : 'reported'])->with('status', 'টাস্ক হালনাগাদ হয়েছে');
    }

    public function status(Request $request, Task $task): RedirectResponse
    {
        abort_unless($task->canChangeStatusBy(Auth::user()), 403);

        $validated = $request->validate(['status' => ['required', Rule::in(Task::STATUSES)]]);
        $task->update($validated);

        return back()->with('status', '"'.$task->title.'" — '.Task::statusLabels()[$task->status]['bn']);
    }

    public function destroy(Task $task): RedirectResponse
    {
        abort_unless($task->canBeEditedBy(Auth::user()), 403);

        $task->delete();

        return back()->with('status', 'টাস্ক মুছে ফেলা হয়েছে');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Task $task = null): array
    {
        $user = Auth::user();
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'assigned_to' => ['nullable', 'integer', Rule::in($this->people->assignable($this->companyId())->keys()->all())],
            'deadline' => ['nullable', 'date'],
        ]);

        $validated['assigned_to'] = (int) ($validated['assigned_to'] ?? 0) ?: ($task?->assigned_to ?? $user->id);

        $reassigned = ! $task || (int) $task->assigned_to !== $validated['assigned_to'];
        if ($reassigned && $validated['assigned_to'] !== (int) $user->id && ! Task::canAssign($user)) {
            abort(403, 'অন্যকে টাস্ক দেওয়ার অনুমতি নেই (You may not assign tasks to others)।');
        }

        return $validated;
    }

    private function companyId(): int
    {
        $companyId = app(TenantContext::class)->companyId();
        abort_unless($companyId, 403, 'কোনো দোকান নির্বাচন করা নেই (No shop selected)।');

        return (int) $companyId;
    }
}
