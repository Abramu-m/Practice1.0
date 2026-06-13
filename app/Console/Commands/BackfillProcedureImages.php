<?php

namespace App\Console\Commands;

use App\Models\InvestigationTemplateResult;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackfillProcedureImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'investigations:backfill-procedure-images
        {--dry-run : Report counts without copying files or writing records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy legacy {legacy_medcom_database}.procedures.pimage result images into storage and link them into investigation_template_results.form_data[images]';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $source = rtrim(config('database.legacy_procedure_uploads_path'), '/\\');

        if (! is_dir($source)) {
            $this->error("Legacy uploads directory not found: {$source}");

            return self::FAILURE;
        }

        $rows = DB::table(config('database.legacy_medcom_database') . '.procedures')
            ->whereNotNull('pimage')
            ->where('pimage', '!=', '')
            ->get(['pl_id', 'pimage']);

        $stats = [
            'rows_checked' => 0,
            'placeholder_skipped' => 0,
            'no_template_result' => 0,
            'source_file_missing' => 0,
            'already_linked' => 0,
            'images_linked' => 0,
        ];

        $seen = [];

        foreach ($rows as $row) {
            $stats['rows_checked']++;

            $basename = basename(trim($row->pimage));
            if (! preg_match('/^[^.].*\.(png|jpe?g|gif|bmp|pdf)$/i', $basename)) {
                $stats['placeholder_skipped']++;
                continue;
            }

            $sourcePath = $source . DIRECTORY_SEPARATOR . $basename;
            if (! is_file($sourcePath)) {
                $stats['source_file_missing']++;
                continue;
            }

            $result = InvestigationTemplateResult::where('investigation_id', $row->pl_id)->first();
            if (! $result) {
                $stats['no_template_result']++;
                continue;
            }

            $destPath = 'investigation_results/legacy_' . $row->pl_id . '_' . $basename;

            $formData = $result->form_data ?? [];
            $images = $formData['images'] ?? [];

            if (isset($seen[$destPath]) || in_array($destPath, $images, true)) {
                $stats['already_linked']++;
                continue;
            }
            $seen[$destPath] = true;

            if (! $dryRun) {
                Storage::disk('public')->put($destPath, file_get_contents($sourcePath));
                $images[] = $destPath;
                $formData['images'] = $images;
                $result->form_data = $formData;
                $result->save();
            }

            $stats['images_linked']++;
        }

        $this->table(['Metric', 'Value'], [
            ['Source directory', $source],
            ['procedures rows with pimage', $stats['rows_checked']],
            ['Skipped — placeholder pimage (no real file)', $stats['placeholder_skipped']],
            ['Skipped — no investigation_template_results row', $stats['no_template_result']],
            ['Skipped — source file not found', $stats['source_file_missing']],
            ['Skipped — already linked', $stats['already_linked']],
            ['Images ' . ($dryRun ? 'that would be linked' : 'linked'), $stats['images_linked']],
        ]);

        if ($dryRun) {
            $this->comment('Dry run — no files copied, no records updated.');
        }

        return self::SUCCESS;
    }
}
