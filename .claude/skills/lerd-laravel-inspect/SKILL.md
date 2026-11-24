---
name: lerd-laravel-inspect
description: Look inside a Laravel app on lerd before reading its code - routes and their controllers, middleware, models with columns, casts and relations both ways, policies and gates, Blade views, Artisan commands, the resolved config (credentials masked), container bindings, database schema, migrations status, scheduled tasks, events and listeners, broadcast channels, queues and failed jobs, storage files, cache stores and keys. Use for "which controller handles /x", "what does the web group run", "what columns/relations does Order have", "who points at this model", "which policy guards Invoice", "what is config('services.x')", "what do I get when I type-hint Y", "is the migration run", "what listens to this event", "is anything waiting on the queue", "why did the job fail".
---

# laravel-inspect

These tools boot the app and report what it has registered, packages and discovered listeners included, so they answer faster and more truly than grepping. Every tool takes `site`, a name from lerd's `site` list. A site that is not a Laravel app answers so, rather than failing.

## Start here

`laravel-inspect_overview`: Laravel and PHP versions, environment, drivers, installed ecosystem packages, the app's composer namespaces, storage disks and cache stores, and counts. Use it first on an app you do not know.

## HTTP

- `laravel-inspect_routes` (`filter`, `vendor`): method, URI, name, action (`Class@method`, or `Closure at file:line`), middleware. Filter matches any field, so pass a URI or route name to find its controller.
- `laravel-inspect_middleware` (`filter`, `vendor`): the global stack, each group (`web`, `api`) with what it runs in order, and the aliases routes use (`auth`, `throttle`) with their classes. `kind` is `global`, `group` or `alias`; `name` is the group or alias.
- `laravel-inspect_channels` (`filter`, `vendor`): broadcast channels such as `user.{id}` and the closure or channel class that authorizes them.

## Data

- `laravel-inspect_models` (`name`, `filter`, `counts`, `vendor`, `references`): without `name`, every model under `app/` with table, connection and relation names. With `name` (`Order` or a full class), its attributes (type, nullable, default, cast, fillable, hidden, appended), relations with their related class, observers, events, policy and row count; `references: true` adds `referenced_by`, the relations on other models that point at it.
- `laravel-inspect_db-tables` (`filter`, `counts`, `connection`): tables with size; `counts` adds row estimates from the database's statistics.
- `laravel-inspect_db-table` (`name`, `connection`): columns, indexes, foreign keys and the exact row count.
- `laravel-inspect_migrations` (`connection`): every migration with its file, pending first, with batch.

## Code

- `laravel-inspect_views` (`filter`, `vendor`): every Blade view by the name `view()` takes (`emails.invoice.paid`, `namespace::name` for namespaced ones) with its file. Without `vendor`, the app's own and the package views it overrides in `resources/views/vendor`.
- `laravel-inspect_commands` (`filter`, `vendor`): every Artisan command with its usage (arguments and options), description and class. Check here before writing a one-off script: the command may exist.
- `laravel-inspect_events` and `laravel-inspect_listeners` (`filter`, `vendor`): events with their listeners, and the reverse, with whether a listener is queued and on which queue.
- `laravel-inspect_policies` (`filter`, `vendor`): one list. `kind: policy` rows give each model's policy, registered or found by Laravel's naming, with the abilities it defines, sorted by name; `kind: gate` rows give each `Gate::define` ability and the closure or class that decides it.
- `laravel-inspect_bindings` (`filter`, `vendor`): what the service container resolves each bound abstract to (the concrete class, or a closure's place) and whether it is a singleton. This answers "what do I get when I type-hint this interface", which neither grep nor an LSP can.
- `laravel-inspect_schedule` (`vendor`): scheduled tasks with cron expression, next run, flags, and `target`, the command class, job class or closure each one runs. Only a closure a package schedules counts as vendor; a command or job counts as the app's.
- `laravel-inspect_config` (`key`, `filter`): the config as the app resolved it, after `.env` and the project's `env_provider`, flattened to dot keys. `key` narrows to one key or subtree (`mail`, `queue.connections.redis`). Credentials are masked as `••••••` with `masked: true`: keys named like one, and any value equal to a secret environment variable. Never try to unmask them.

## Runtime

- `laravel-inspect_queues` (`filter`): jobs waiting now on the queues the app uses (the default connection, queues its queued classes and Horizon name, failed jobs' queues): pending, delayed, reserved and total.
- `laravel-inspect_failed-jobs` (`limit`): latest failed jobs with job class, queue and the exception's first line.
- `laravel-inspect_cache` and `laravel-inspect_cache-get` (`store`, `key`): the cache stores with how much each holds, which Redis database each shares with queues or sessions, whether Laravel's config/route/event/view caches are built, and one key's value.
- `laravel-inspect_storage-list` (`disk`, `path`, `search`, `cursor`) and `laravel-inspect_storage-read` (`disk`, `path`): one page (500) of one folder at a time, folders first, and a file's text (or an image as a data URL) up to 1 MB. `search` narrows the folder on the server: a substring on local disks, a name prefix on S3; pass `next` back as `cursor` for the next page. Disk names come from the overview, as do database connections (`connection`, default when left out).

## Filters, vendor and source

Every `filter` ignores case and takes several terms separated by `|`, matching any of them: `filter: "Order|Invoice"`.

`vendor` is off by default: answers hold the app's own rows. Turn it on only when the question is about a package, and narrow to one with its namespace: `routes` with `vendor: true, filter: "Horizon"`. Rows of `routes`, `middleware`, `channels`, `models`, `policies`, `views`, `commands`, `bindings`, `events`, `listeners` and `schedule` carry `source`: the composer package the code ships in (`laravel/framework`, `laravel/sanctum`), `lerd` for lerd's own debug adapter, and null for the app's own. Lists put the app's rows first.

## What these tools do not do

Deleting files, clearing a cache store, forgetting a key and clearing Laravel's caches exist only as buttons in the site's **Laravel** tab in lerd's dashboard, behind a confirmation, so the user decides. When one of those is the fix, tell the user what to clear and why. Clearing a Redis cache store runs FLUSHDB on its whole database, and `laravel-inspect_cache` says what else lives there.

Config values, disks and cache stores come back with their credentials masked. What you read from a file (`storage-read`), a cache key (`cache-get`) or a job's exception (`failed-jobs`) comes back as it is, and can hold a secret: do not repeat one back to the user or into code. By default the overview, Database, Storage, Cache and Queues show only what the app uses or its defaults; a project lists more under `laravel_inspect: {disks: [...], cache_stores: [...], queues: [...], connections: [...]}` in `.lerd.yaml`.

## Elsewhere in lerd

Logs: lerd's `logs` tool. N+1, slow queries and exceptions per request: lerd's `request` lenses. Environment and dependency checks: `diag` `site_doctor`. Running Artisan: lerd's `exec`.
