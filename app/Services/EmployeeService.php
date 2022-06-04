<?php

namespace App\Services;

use App\Contracts\Import;
use App\Contracts\Repository;
use App\Models\EmployeeScanner;
use App\Models\Scanner;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class EmployeeService implements Import
{
    const REQUIRED_HEADERS = [
        'SCANNER UID',
        'LAST NAME',
        'FIRST NAME',
        'REGULAR',
    ];

    private string $error = '';

    protected $header = [];

    public function __construct(
        private Repository $repository
    ) { }

    public function validate(UploadedFile $file): bool
    {
        $header = collect(self::REQUIRED_HEADERS)->every(fn ($header) => in_array($header, $this->headers((string) File::lines($file)->first())));

        if ($header) {
            $this->error = 'FILE UNSUPPORTED.';

            return false;
        }

        // $id = File::lines($file)->skip(1)->filter()->map(fn ($e) => str_getcsv($e)[$this->headers((string) File::lines($file)->first())['SCANNER ID']])->duplicates();

        // if ($id->isNotEmpty()) {
        //     $this->error = "DUPLICATE IDS DETECTED: {$id->values()->toJSON()}. PLEASE CHECK AGAIN.";

        //     return false;
        // }

        return true;
    }

    public function error(): string
    {
        return $this->error;
    }

    public function parse(UploadedFile $file): void
    {
        // $this->repository->truncate(fn ($query) => $query->whereUserId(auth()->id())->delete());

        File::lines($file)
            ->skip(1)
            ->filter()
            ->map(fn($e) => str_getcsv($e))
            ->map(fn ($e) => $this->repository->transformImportData($e, $this->headers((string) File::lines($file)->first())))
            ->chunk(1000)
            ->each(function ($chunk) {
                $this->repository->upsert($chunk->toArray(), upserter: function ($payload, $transformed) {
                    $this->repository->query()->upsert(collect($payload)->map(fn ($e) => collect($e)->except('scanner_uid', 'nameToJSON')->toArray())->replaceRecursive($transformed)->toArray(), ['name'], ['id']);
                });

                $keys = $chunk->map->{'scanner_uid'}->flatMap(fn ($e) => array_keys($e))->unique()->mapWithKeys(fn ($e) => [$e => Scanner::firstOrCreate(['name' => $e])->id])->toArray();

                $chunk->flatMap(function ($e) use ($keys) {
                    return collect($e['scanner_uid'])->map(function ($f, $k) use ($e, $keys) {
                        return [
                            'id' => str()->orderedUuid(),
                            'uid' => $f,
                            'scanner_id' => $keys[$k],
                            'employee_id' => $e['id'],
                        ];
                    })->toArray();
                })->chunk(1000)->each(fn ($chunk) => EmployeeScanner::upsert($chunk->toArray(), ['uid', 'scanner_id'], ['employee_id']));
            });

        // event(new EmployeesImported(auth()->user(), $file));
    }

    public function headers(string $line): array
    {
        return array_flip(explode(',', strtoupper(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $line))));
    }

    public function all()
    {
        return $this->repository->query()->whereHas('scanners', function (Builder $query) {
            $query->whereHas('users', function (Builder $query) {
                $query->where('user_id', auth()->id());
            });
        })->get();
    }

    public function offices()
    {
        return $this->repository
            ->query()
            ->select(['office', 'name'])
            ->get()
            ->map
            ->office
            ->unique()
            ->prepend('ALL')
            ->filter()
            ->values();
    }

    public function update(string $id, array $payload)
    {
        $this->repository->update($id = explode(',', $id), [
            'user_id' => auth()->id(),
            ...$payload,
            'nameToJSON' => 1,
        ], count($id) > 1
            ? collect(request()->only('office', 'regular', 'active'))
                ->filter(fn ($e) => $e == '*')
                ->keys()->push('biometrics_id', 'name', 'user_id')
                ->toArray()
            : []
        );
    }

    public function markInactive(Authenticatable $user)
    {
        $user->employees()->active()->whereDoesntHave('mainLogs', function ($q) {
            $q->whereDate('time', '>', today()->subMonth()->startOfMonth());
        })->update(['active' => 0]);
    }

    public function markActive(Authenticatable $user)
    {
        $user->employees()->active(0)->whereHas('mainLogs', function ($q) {
            $q->whereDate('time', '<', today()->subMonth()->startOfMonth());
        })->update(['active' => 1]);
    }

    public function loadLogs(Collection|array $employees, ?Carbon $from = null, ?Carbon $to = null): Collection
    {
        $from ??= today()->startOfMonth();

        $to ??= today()->endOfMonth();

        if (is_array($employees)) {
            $employees = $this->repository->find($employees);
        }

        $employees->filter->backedUp->map->load(['backupLogs' => fn ($q) => $q->whereBetween('time', [$from, $to])]);

        $employees->reject->backedUp->map->load(['mainLogs' => fn ($q) => $q->whereBetween('time', [$from, $to])]);

        return $employees;
    }
}
