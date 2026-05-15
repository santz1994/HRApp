<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ImportEmployeesJob;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EmployeeImport;

class ImportEmployeesCommand extends Command
{
    protected $signature = 'import:employees {file : Path to Excel file}';
    protected $description = 'Import employees from Excel file via Queue';

    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found: $filePath");
            return 1;
        }

        $this->info("Reading Excel file: $filePath");

        try {
            // Parse Excel dengan Maatwebsite yang sudah optimized
            $import = new EmployeeImport();
            Excel::import($import, $filePath);

            $data = $import->getEmployees();
            $totalRows = count($data);

            $this->info("Total rows to import: $totalRows");

            // Dispatch ke queue
            if ($totalRows > 0) {
                ImportEmployeesJob::dispatch($data, auth()->id() ?? 1, basename($filePath))
                    ->onQueue('default');

                $this->info("✓ Import job dispatched to queue");
                $this->info("Job will process in background with chunking");
            } else {
                $this->warn("No data found in file");
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }
}
