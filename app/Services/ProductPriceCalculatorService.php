<?php

namespace App\Services;

use App\Models\Product;

class ProductPriceCalculatorService
{
    /**
     * Calculate price details for a product.
     *
     * @param Product $product
     * @param array $params
     * @return array
     */
    public function calculate(Product $product, array $params = []): array
    {
        $qty = (int) ($params['qty'] ?? 1);
        $length = (float) ($params['custom_length'] ?? 0);
        $width = (float) ($params['custom_width'] ?? 0);

        // Standard inputs validation fallback
        if ($qty < 1) $qty = 1;
        if ($length < 0) $length = 0;
        if ($width < 0) $width = 0;

        $basePrice = $product->base_price;
        $pricePerSquareMeter = $product->price_per_square_meter;

        // Apply discount to base price
        $finalBasePrice = $basePrice;
        if ($product->discount_percent > 0) {
            $finalBasePrice -= ($finalBasePrice * ($product->discount_percent / 100));
        }
        if ($product->discount_flat > 0) {
            $finalBasePrice -= $product->discount_flat;
        }
        $finalBasePrice = max(0, (int) $finalBasePrice);

        // Apply discount to price per square meter
        $finalPricePerM2 = $pricePerSquareMeter;
        if ($product->discount_percent > 0) {
            $finalPricePerM2 -= ($finalPricePerM2 * ($product->discount_percent / 100));
        }
        if ($product->discount_flat > 0) {
            $finalPricePerM2 -= $product->discount_flat;
        }
        $finalPricePerM2 = max(0, (int) $finalPricePerM2);

        // Variant modifiers
        $modifier = 0;
        if (!empty($params['ukuran'])) {
            $v = $product->variants()->where('variant_type', 'ukuran')->where('variant_name', $params['ukuran'])->first();
            if ($v) $modifier += $v->price_modifier;
        }
        if (!empty($params['bahan'])) {
            $v = $product->variants()->where('variant_type', 'bahan')->where('variant_name', $params['bahan'])->first();
            if ($v) $modifier += $v->price_modifier;
        }
        if (!empty($params['finishing'])) {
            $v = $product->variants()->where('variant_type', 'finishing')->where('variant_name', $params['finishing'])->first();
            if ($v) $modifier += $v->price_modifier;
        }

        $unitPrice = 0;
        $subtotal = 0;
        $area = 0.0;

        switch ($product->calculation_type) {
            case 'fixed':
            case 'quantity':
                $unitPrice = $finalBasePrice + $modifier;
                $subtotal = $unitPrice * $qty;
                break;
            case 'custom_size':
            case 'quantity_custom_size':
                $area = $length * $width;
                $unitPrice = (int) round($area * $finalPricePerM2) + $modifier;
                $subtotal = $unitPrice * $qty;
                break;
        }

        return [
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
            'area' => $area,
            'final_price_per_m2' => $finalPricePerM2,
        ];
    }
}
