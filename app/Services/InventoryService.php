<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    /**
     * Increase stock and record movement.
     */
    public function stockIn(int $productId, int $qty, string $reason = 'purchase', ?int $referenceId = null): Product
    {
        if ($qty <= 0) throw new InvalidArgumentException('Quantity must be positive.');
        return DB::transaction(function () use ($productId, $qty, $reason, $referenceId) {
            $product = Product::lockForUpdate()->findOrFail($productId);
            $product->stock = ($product->stock ?? 0) + $qty;
            $product->save();

            StockMovement::create([
                'product_id'   => $product->id,
                'type'         => 'in',
                'quantity'     => $qty,
                'reason'       => $reason,
                'reference_id' => $referenceId,
            ]);

            return $product;
        });
    }

    /**
     * Decrease stock and record movement.
     */
    public function stockOut(int $productId, int $qty, string $reason = 'sale', ?int $referenceId = null): Product
    {
        if ($qty <= 0) throw new InvalidArgumentException('Quantity must be positive.');
        return DB::transaction(function () use ($productId, $qty, $reason, $referenceId) {
            $product = Product::lockForUpdate()->findOrFail($productId);
            $current = (int)($product->stock ?? 0);
            if ($current < $qty) {
                throw new InvalidArgumentException('Not enough stock.');
            }
            $product->stock = $current - $qty;
            $product->save();

            StockMovement::create([
                'product_id'   => $product->id,
                'type'         => 'out',
                'quantity'     => $qty,
                'reason'       => $reason,
                'reference_id' => $referenceId,
            ]);

            return $product;
        });
    }
}
