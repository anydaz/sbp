<?php

namespace App\Console\Commands;

use App\Services\AccountBalanceService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CalculateAccountBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'balances:calculate
                            {--date= : Specific date to calculate balances for (Y-m-d format)}
                            {--rebuild : Rebuild existing balances for the month}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and store monthly account balances for financial reporting';

    protected $balanceService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(AccountBalanceService $balanceService)
    {
        parent::__construct();
        $this->balanceService = $balanceService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::now();
        $rebuild = $this->option('rebuild');

        $this->info("Calculating monthly account balances...");
        $this->info("Date: {$date->format('Y-m-d')}");

        try {
            if ($rebuild) {
                $periodIdentifier = $date->format('Y-m');
                $this->info("Rebuilding balances for period: {$periodIdentifier}");
                $this->balanceService->rebuildBalances($periodIdentifier);
            } else {
                $this->balanceService->calculateAndStoreBalances($date);
            }

            $this->info("✅ Account balances calculated successfully!");

            // Show summary
            $this->showBalanceSummary($date->format('Y-m'));

        } catch (\Exception $e) {
            $this->error("❌ Error calculating balances: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    protected function showBalanceSummary($periodIdentifier)
    {
        $balanceSheet = $this->balanceService->getBalanceSheetData($periodIdentifier);

        $this->newLine();
        $this->info("=== Balance Sheet Summary ===");
        $this->info("Period: {$periodIdentifier}");
        $this->newLine();

        $this->info("ASSETS:");
        foreach ($balanceSheet['assets']['accounts'] as $asset) {
            $balance = number_format($asset['balance'], 2);
            $this->line("  {$asset['account_code']} - {$asset['account_name']}: $" . $balance);
        }
        $this->info("Total Assets: $" . number_format($balanceSheet['assets']['total'], 2));

        $this->newLine();
        $this->info("LIABILITIES:");
        foreach ($balanceSheet['liabilities']['accounts'] as $liability) {
            $balance = number_format($liability['balance'], 2);
            $this->line("  {$liability['account_code']} - {$liability['account_name']}: $" . $balance);
        }
        $this->info("Total Liabilities: $" . number_format($balanceSheet['liabilities']['total'], 2));

        $this->newLine();
        $this->info("EQUITY:");
        foreach ($balanceSheet['equity']['accounts'] as $equity) {
            $balance = number_format($equity['balance'], 2);
            $this->line("  {$equity['account_code']} - {$equity['account_name']}: $" . $balance);
        }
        $this->info("Total Equity: $" . number_format($balanceSheet['equity']['total'], 2));

        $this->newLine();
        $this->info("TOTALS:");
        $this->info("Total Assets: $" . number_format($balanceSheet['totals']['total_assets'], 2));
        $this->info("Total Liabilities + Equity: $" . number_format($balanceSheet['totals']['total_liabilities_and_equity'], 2));

        if ($balanceSheet['totals']['is_balanced']) {
            $this->info("✅ Balance Sheet is balanced!");
        } else {
            $difference = $balanceSheet['totals']['total_assets'] - $balanceSheet['totals']['total_liabilities_and_equity'];
            $this->error("⚠️  Balance Sheet is NOT balanced! Difference: $" . number_format($difference, 2));
        }
    }
}
