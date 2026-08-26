<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class LabelService
{
    /**
     * Generate PDF Label.
     */
    public function generateLabelPdf(Shipment $shipment): string
    {
        Log::info("LabelService: Generating PDF label for shipment ID #{$shipment->id}");
        $data = $this->getLabelData($shipment);
        $pdf = Pdf::loadView('admin.shipping.label', $data);
        $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
        return $pdf->output();
    }

    /**
     * Get data array for the label.
     */
    public function getLabelData(Shipment $shipment): array
    {
        $order = $shipment->order;
        $order->loadMissing('orderDetails.product');
        
        $shipperName = Setting::getVal('warehouse_name') ?: Setting::getVal('web_name') ?: 'Fadilah Digital Printing';
        $shipperPhone = Setting::getVal('web_phone') ?: '081234567890';
        $shipperAddress = Setting::getVal('warehouse_address') ?: Setting::getVal('web_address') ?: 'Sleman, D.I. Yogyakarta';
        
        // Calculate total weight of the shipment
        $totalWeight = 0;
        foreach ($order->orderDetails as $detail) {
            $product = $detail->product;
            $weight = ($product && $product->weight > 0) ? $product->weight : 100;
            $isSizeBased = $product ? in_array($product->calculation_type, ['custom_size', 'quantity_custom_size']) : false;
            
            if ($isSizeBased && isset($detail->custom_length) && isset($detail->custom_width)) {
                $area = $detail->custom_length * $detail->custom_width;
                $weight = (int) round($weight * $area);
            }
            $totalWeight += ($weight * $detail->qty);
        }

        // Convert weight to kg for readable format
        $totalWeightKg = $totalWeight / 1000;

        $logoPath = Setting::getVal('company_logo');
        $logoBase64 = null;
        if ($logoPath) {
            $path = storage_path('app/public/' . $logoPath);
            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $dataImg = file_get_contents($path);
                $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($dataImg);
            }
        }

        // Generate local SVG barcodes
        $trackingBarcode = $this->generateCode39Svg($shipment->tracking_number);
        $orderBarcode = $this->generateCode39Svg($order->invoice_number);
        
        return [
            'shipment' => $shipment,
            'order' => $order,
            'shipperName' => $shipperName,
            'shipperPhone' => $shipperPhone,
            'shipperAddress' => $shipperAddress,
            'totalWeightKg' => $totalWeightKg,
            'logoPath' => $logoPath,
            'logoBase64' => $logoBase64,
            'trackingBarcode' => $trackingBarcode,
            'orderBarcode' => $orderBarcode,
        ];
    }

    /**
     * Generate Code39 Barcode as inline base64 SVG image.
     */
    public function generateCode39Svg(string $code): string
    {
        $code = strtoupper($code);
        // Clean code to only contain valid Code39 characters
        $code = preg_replace('/[^0-9A-Z\-.\/+$%\s]/', '', $code);
        $text = '*' . $code . '*';
        
        $patterns = [
            '0' => '000110100', '1' => '100100001', '2' => '001100001', '3' => '101100000',
            '4' => '000110001', '5' => '100110000', '6' => '001110000', '7' => '000100101',
            '8' => '100100100', '9' => '001100100', 'A' => '100001001', 'B' => '001001001',
            'C' => '101001000', 'D' => '000011001', 'E' => '100011000', 'F' => '001011000',
            'G' => '000001101', 'H' => '100001100', 'I' => '001001100', 'J' => '000011100',
            'K' => '100000011', 'L' => '001000011', 'M' => '101000010', 'N' => '000010011',
            'O' => '100010010', 'P' => '001010010', 'Q' => '000000111', 'R' => '100000110',
            'S' => '001000110', 'T' => '000010110', 'U' => '110000001', 'V' => '011000001',
            'W' => '111000000', 'X' => '010010001', 'Y' => '110010000', 'Z' => '011010000',
            '-' => '010000101', '.' => '110000100', ' ' => '011000100', '*' => '010010100',
            '$' => '010101000', '/' => '010100010', '+' => '010001010', '%' => '000101010'
        ];

        $narrow = 1;
        $wide = 3;
        $gap = 1;
        
        $x = 0;
        $rects = [];
        
        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];
            if (!isset($patterns[$char])) continue;
            $pattern = $patterns[$char];
            
            for ($j = 0; $j < 9; $j++) {
                $bit = $pattern[$j];
                $width = ($bit === '1') ? $wide : $narrow;
                
                // Draw black bar (even index)
                if ($j % 2 === 0) {
                    $rects[] = "<rect x=\"{$x}\" y=\"0\" width=\"{$width}\" height=\"50\" fill=\"black\" />";
                }
                
                $x += $width;
            }
            $x += $gap;
        }
        
        $totalWidth = $x;
        $svg = "<svg width=\"100%\" height=\"100%\" viewBox=\"0 0 {$totalWidth} 50\" preserveAspectRatio=\"none\" xmlns=\"http://www.w3.org/2000/svg\">";
        $svg .= implode('', $rects);
        $svg .= "</svg>";
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
