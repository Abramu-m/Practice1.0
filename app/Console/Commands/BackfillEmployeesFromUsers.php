<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BackfillEmployeesFromUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hr:backfill-employees-from-users
        {--dry-run : Report what would be created without writing any records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Employee records for every user account that is not yet linked to an employee';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $creator = User::where('is_super', true)->orderBy('id')->first()
            ?? User::where('is_admin', true)->orderBy('id')->first();

        if (! $creator) {
            $this->error('No admin/super-admin user found to attribute created_by to.');

            return self::FAILURE;
        }

        $users = User::whereDoesntHave('employee')->orderBy('id')->get();

        $rows = [];

        foreach ($users as $user) {
            $status = $user->is_active ? 'active' : 'inactive';

            $rows[] = [$user->id, $user->name, $user->role, $user->email, $status];

            if ($dryRun) {
                continue;
            }

            Employee::create([
                'user_id' => $user->id,
                'first_name' => $user->first_name,
                'middle_name' => $user->middle_name,
                'last_name' => $user->last_name,
                'gender' => $user->gender,
                'date_of_birth' => $user->date_of_birth?->year >= 1900 ? $user->date_of_birth : null,
                'phone' => $user->phone,
                'email' => $user->email,
                'address' => $user->address,
                'job_title' => Str::title(str_replace('_', ' ', $user->role)),
                'date_joined' => $user->created_at?->toDateString(),
                'status' => $status,
                'created_by' => $creator->id,
            ]);
        }

        if ($rows === []) {
            $this->info('No unlinked users found — nothing to do.');

            return self::SUCCESS;
        }

        $this->table(['User ID', 'Name', 'Role', 'Email', 'Employee status'], $rows);

        $verb = $dryRun ? 'would be created' : 'created';
        $this->info(count($rows) . " employee record(s) {$verb}, linked to the matching user account.");
        $this->comment('basic_salary, job_title, department and bank/statutory numbers are placeholders — review and complete each employee record in HR > Employees.');

        if ($dryRun) {
            $this->comment('Dry run — no records were written.');
        }

        return self::SUCCESS;
    }
}
