import fs from 'fs';
import path from 'path';

/**
 * Headless Chromium PDF Exporter
 * Renders HTML to PDF using installed Chrome / Edge or Puppeteer.
 *
 * Usage: node pdf-render.js <input_html_path> <output_pdf_path> [pageSize]
 */

async function renderPdf() {
    const args = process.argv.slice(2);
    if (args.length < 2) {
        console.error('Usage: node pdf-render.js <input_html_path> <output_pdf_path> [pageSize]');
        process.exit(1);
    }

    const inputPath = path.resolve(args[0]);
    const outputPath = path.resolve(args[1]);
    const pageSize = (args[2] || 'A4').toLowerCase() === 'letter' ? 'Letter' : 'A4';

    if (!fs.existsSync(inputPath)) {
        console.error(`Input file not found: ${inputPath}`);
        process.exit(1);
    }

    let puppeteer;
    try {
        puppeteer = await import('puppeteer');
    } catch {
        try {
            puppeteer = await import('puppeteer-core');
        } catch (e) {
            console.error('Puppeteer not installed:', e.message);
            process.exit(1);
        }
    }

    // Standard Windows browser locations fallback if puppeteer browser isn't downloaded
    const possiblePaths = [
        'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
        'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
        '/usr/bin/google-chrome',
        '/usr/bin/chromium-browser',
        '/usr/bin/chromium',
    ];

    let executablePath = null;
    for (const p of possiblePaths) {
        if (fs.existsSync(p)) {
            executablePath = p;
            break;
        }
    }

    const launchOptions = {
        headless: 'new',
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-gpu',
            '--disable-dev-shm-usage',
            '--font-render-hinting=none',
        ],
    };

    if (executablePath) {
        launchOptions.executablePath = executablePath;
    }

    const browser = await puppeteer.default.launch(launchOptions);

    try {
        const page = await browser.newPage();
        
        await page.setViewport({ width: 1280, height: 1024, deviceScaleFactor: 1 });
        await page.emulateMediaType('print');

        const htmlContent = fs.readFileSync(inputPath, 'utf8');
        await page.setContent(htmlContent, { waitUntil: ['load', 'networkidle0'], timeout: 30000 });

        // Wait for web fonts (Inter, Poppins, etc.) to finish rendering
        await page.evaluate(async () => {
            if (document.fonts) {
                await document.fonts.ready;
            }
        });

        await page.pdf({
            path: outputPath,
            format: pageSize === 'Letter' ? 'Letter' : 'A4',
            printBackground: true,
            margin: { top: '0px', right: '0px', bottom: '0px', left: '0px' },
            preferCSSPageSize: false,
        });

        console.log('PDF exported successfully to:', outputPath);
    } finally {
        await browser.close();
    }
}

renderPdf().catch((err) => {
    console.error('PDF rendering failed:', err);
    process.exit(1);
});
