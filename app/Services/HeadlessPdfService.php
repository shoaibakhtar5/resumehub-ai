<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf as DomPdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use Throwable;

class HeadlessPdfService
{
    /**
     * Render an HTML document into a binary PDF string using Headless Chromium.
     */
    public function renderHtmlToPdf(string $html, string $pageSize = 'a4'): string
    {
        $tempDir = storage_path('app/temp_pdf');
        if (!File::isDirectory($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $id = uniqid('pdf_', true);
        $inputHtmlPath = $tempDir . '/' . $id . '.html';
        $outputPdfPath = $tempDir . '/' . $id . '.pdf';

        try {
            File::put($inputHtmlPath, $html);

            // Strategy 1: Node.js Puppeteer script (Highest Precision)
            if ($this->renderViaNodeScript($inputHtmlPath, $outputPdfPath, $pageSize)) {
                return File::get($outputPdfPath);
            }

            // Strategy 2: Direct Chrome/Edge CLI (--headless --print-to-pdf)
            if ($this->renderViaChromeCli($inputHtmlPath, $outputPdfPath)) {
                return File::get($outputPdfPath);
            }

            // Strategy 3: DomPDF Fallback
            Log::warning('Headless Chromium unavailable, falling back to DomPDF.');
            return $this->renderViaDomPdf($html, $pageSize);

        } finally {
            if (File::exists($inputHtmlPath)) {
                File::delete($inputHtmlPath);
            }
            if (File::exists($outputPdfPath)) {
                File::delete($outputPdfPath);
            }
        }
    }

    private function renderViaNodeScript(string $htmlPath, string $pdfPath, string $pageSize): bool
    {
        $scriptPath = resource_path('scripts/pdf-render.js');
        if (!File::exists($scriptPath)) {
            return false;
        }

        $nodeBinary = $this->findNodeBinary();
        if (!$nodeBinary) {
            return false;
        }

        $process = Process::run([
            $nodeBinary,
            $scriptPath,
            $htmlPath,
            $pdfPath,
            $pageSize,
        ]);

        if ($process->successful() && File::exists($pdfPath) && File::size($pdfPath) > 0) {
            return true;
        }

        Log::warning('Node PDF rendering output failure', [
            'output' => $process->output(),
            'errorOutput' => $process->errorOutput(),
        ]);

        return false;
    }

    private function renderViaChromeCli(string $htmlPath, string $pdfPath): bool
    {
        $chromePath = $this->findChromeBinary();
        if (!$chromePath) {
            return false;
        }

        $process = Process::run([
            $chromePath,
            '--headless',
            '--disable-gpu',
            '--no-sandbox',
            '--window-size=1280,1024',
            '--force-device-scale-factor=1',
            '--print-to-pdf=' . $pdfPath,
            '--no-margins',
            '--hide-scrollbars',
            'file:///' . str_replace('\\', '/', $htmlPath),
        ]);

        return $process->successful() && File::exists($pdfPath) && File::size($pdfPath) > 0;
    }

    private function renderViaDomPdf(string $html, string $pageSize): string
    {
        return DomPdf::loadHTML($html)
            ->setPaper($pageSize === 'letter' ? 'letter' : 'a4')
            ->setOption('isRemoteEnabled', true)
            ->setOption('isHtml5ParserEnabled', true)
            ->output();
    }

    private function findNodeBinary(): ?string
    {
        $process = Process::run('node -v');
        if ($process->successful()) {
            return 'node';
        }

        return null;
    }

    private function findChromeBinary(): ?string
    {
        $candidates = [
            'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
            'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
            '/usr/bin/google-chrome',
            '/usr/bin/chromium-browser',
            '/usr/bin/chromium',
        ];

        foreach ($candidates as $candidate) {
            if (File::exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
