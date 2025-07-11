<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateTargetOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-target-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update target prices of orders every 15 minutes from their start_time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();

        $orders = Order::whereNotNull('start_time')->get();

        foreach ($orders as $order) {
            $startTime = Carbon::parse($order->start_time);
            $lastUpdate = $order->last_price_updated_at ? Carbon::parse($order->last_price_updated_at) : null;

            $diffMinutes = $startTime->diffInMinutes($now);

            // Якщо минуло кратно 15 хвилин з start_time
            if ($diffMinutes % 15 === 0) {
                // Перевіримо, чи вже не оновлено в цю хвилину
                if (!$lastUpdate || $lastUpdate->lt($now->copy()->subMinutes(14))) {
                    $oldPrice = $order->target_price;
                    $newPrice = $this->calculateNewPrice($order);

                    $order->target_price = $newPrice;
                    $order->last_price_updated_at = $now;
                    $order->save();

                    Log::info("Updated Order ID {$order->id} from {$oldPrice} to {$newPrice} at {$now}");
                }
            }
        }

        $this->info('Checked and updated target orders if needed.');
    }

    /**
     * Calculate new price — replace with your actual logic
     */
    private function calculateNewPrice(Order $order): float
    {
        // Example: increase by 1%
        return round($order->target_price * 1.01, 2);
    }
}
