const { spawn } = require('child_process');
const fs = require('fs');
const path = require('path');
const { exec } = require('child_process');

// Function to convert PDF to images
async function convertPDFToImages(pdfPath) {
    return new Promise((resolve, reject) => {
        const outputPattern = path.join('output_images', `${path.basename(pdfPath, '.pdf')}-%04d.png`);
        const command = `magick -density 300 ${pdfPath} ${outputPattern}`;
        
        exec(command, (error, stdout, stderr) => {
            if (error) {
                reject(`Error converting PDF: ${stderr}`);
            } else {
                resolve(outputPattern);
            }
        });
    });
}

// Function to process image files
async function processImage(imagePath) {
    return new Promise((resolve, reject) => {
        const output = [];
        const tesseract = spawn('tesseract', [imagePath, 'stdout', '-l', 'eng']);

        tesseract.stdout.on('data', (data) => {
            output.push(data.toString());
        });

        tesseract.stderr.on('data', (data) => {
            console.error(`stderr: ${data}`);
        });

        tesseract.on('close', (code) => {
            if (code === 0) {
                resolve(output.join(''));
            } else {
                reject(`Tesseract process exited with code ${code}`);
            }
        });
    });
}

// Directory containing PDF files
const pdfDir = './invoices/';
const imageOutputDir = './output_images/';

// Ensure output directory exists
if (!fs.existsSync(imageOutputDir)){
    fs.mkdirSync(imageOutputDir);
}

// Process each PDF file
fs.readdir(pdfDir, async (err, files) => {
    if (err) {
        console.error('Error reading directory:', err);
        return;
    }

    for (const file of files) {
        const filePath = path.join(pdfDir, file);
        try {
            const imagePattern = await convertPDFToImages(filePath);
            const imageFiles = fs.readdirSync(imageOutputDir).filter(imgFile => imgFile.includes(path.basename(file, '.pdf')));

            for (const imageFile of imageFiles) {
                const imagePath = path.join(imageOutputDir, imageFile);
                const text = await processImage(imagePath);
                fs.writeFileSync(`./output/${path.basename(imageFile, '.png')}.txt`, text);
            }
        } catch (error) {
            console.error(`Error processing file ${file}:`, error);
        }
    }
});