<?php

namespace App\Services;

class ThermalPrinterService
{
    /**
     * Membuat teks untuk printer thermal
     */
    public function createText(array $lines)
    {
        $text = [
            $this->initializePrinter(),
        ];

        foreach ($lines as $line) {
            if (isset($line['type']) && $line['type'] === 'qrcode') {
                $text[] = $this->formatQRCode($line);
            } else if (isset($line['type']) && $line['type'] === 'image') {
                $text[] = $this->formatImage($line['path'], $line['align'] ?? 'center');
            } else {
                $text[] = $this->formatLine($line);
            }
        }

        $text[] = $this->feedPaper();
        $text[] = $this->cutPaper();

        return implode('', $text);
    }

    private function formatLine(array $line)
    {
        $formattedLine = '';

        if (isset($line['align'])) {
            $formattedLine .= $this->align($line['align']);
        }

        if (isset($line['style'])) {
            $formattedLine .= $this->style($line['style']);
        }

        $formattedLine .= $line['text'] . "\n";

        if (isset($line['style'])) {
            $formattedLine .= $this->resetStyle();
        }

        return $formattedLine;
    }

    private function initializePrinter()
    {
        return "\x1B\x40";  // Initialize printer
    }

    private function align($alignment)
    {
        switch ($alignment) {
            case 'center':
                return "\x1B\x61\x01";  // Center alignment
            case 'right':
                return "\x1B\x61\x02";  // Right alignment
            default:
                return "\x1B\x61\x00";  // Left alignment
        }
    }

    private function style($style)
    {
        switch ($style) {
            case 'double':
                return "\x1B\x21\x30";  // Double-height, double-width
            default:
                return "\x1B\x21\x00";  // Normal text
        }
    }

    private function resetStyle()
    {
        return "\x1B\x21\x00";  // Reset to normal text
    }

    private function feedPaper()
    {
        return "\n\n\n";  // Feed paper
    }

    private function cutPaper()
    {
        return "\x1B\x69";  // Cut paper
    }

    private function formatQRCode(array $qrParams)
    {
        $data = $qrParams['data'] ?? '';
        $size = $qrParams['size'] ?? 6;
        $eccLevel = $qrParams['eccLevel'] ?? 49; // Default M (49)
        $align = $qrParams['align'] ?? 'center';

        // Validasi parameter
        $size = max(1, min(16, $size)); // Batasi ukuran antara 1-16
        $eccLevel = max(48, min(51, $eccLevel)); // Batasi error correction level antara 48-51 (L,M,Q,H)

        $qrCode = '';

        // Set alignment
        $qrCode .= $this->align($align);

        // QR Code model
        $model = 2; // Model 2 adalah yang paling umum digunakan

        // Command untuk mencetak QR Code
        $qrCode .= "\x1D\x28\x6B"; // GS ( k - QR Code command
        $qrCode .= chr(strlen($data) + 3); // pL - Panjang data + 3
        $qrCode .= chr((strlen($data) + 3) >> 8); // pH - Byte tinggi panjang (untuk data panjang)
        $qrCode .= "\x31\x50\x30"; // cn fn m (31=type, 50=store data, 30=auto encoding)
        $qrCode .= $data;

        // Set ukuran dot QR Code
        $qrCode .= "\x1D\x28\x6B\x03\x00\x31\x43" . chr($size);

        // Set error correction level
        $qrCode .= "\x1D\x28\x6B\x03\x00\x31\x45" . chr($eccLevel);

        // Cetak QR Code
        $qrCode .= "\x1D\x28\x6B\x03\x00\x31\x51\x30";

        // Feed setelah QR Code
        $qrCode .= "\n";

        return $qrCode;
    }
    private function formatImage($path, $align = 'center')
    {
        $img = imagecreatefrompng($path); // bisa juga jpg/gif
        $width = imagesx($img);
        $height = imagesy($img);

        // konversi ke hitam putih
        $threshold = 128;
        $data = '';
        for ($y = 0; $y < $height; $y++) {
            $rowBytes = ceil($width / 8);
            $rowData = '';
            for ($x = 0; $x < $width; $x += 8) {
                $byte = 0;
                for ($bit = 0; $bit < 8; $bit++) {
                    $pixelX = $x + $bit;
                    if ($pixelX < $width) {
                        $rgb = imagecolorat($img, $pixelX, $y);
                        $colors = imagecolorsforindex($img, $rgb);
                        $gray = ($colors['red'] + $colors['green'] + $colors['blue']) / 3;
                        if ($gray < $threshold) {
                            $byte |= (1 << (7 - $bit));
                        }
                    }
                }
                $rowData .= chr($byte);
            }

            // ESC * m nL nH d1...dk
            $nL = $rowBytes & 0xFF;
            $nH = ($rowBytes >> 8) & 0xFF;
            $data .= "\x1B*\x21" . chr($nL) . chr($nH) . $rowData . "\n";
        }

        imagedestroy($img);

        return $this->align($align) . $data;
    }
}
