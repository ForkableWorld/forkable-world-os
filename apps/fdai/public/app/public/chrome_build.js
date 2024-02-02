const fs = require('fs');
const path = require('path');
const axios = require('axios');
const cheerio = require('cheerio');

const downloadFile = async (url, filePath) => {
    const dir = path.dirname(filePath);
    if (!fs.existsSync(dir)) {
        console.log(`Creating directory: ${dir}`);
        fs.mkdirSync(dir, { recursive: true });
    }

    console.log(`Downloading ${url}...`);
    const writer = fs.createWriteStream(filePath);
    const response = await axios({
        url,
        method: 'GET',
        responseType: 'stream'
    });

    response.data.pipe(writer);
    return new Promise((resolve, reject) => {
        writer.on('finish', () => {
            console.log(`Downloaded and saved to ${filePath}`);
            resolve();
        });
        writer.on('error', reject);
    });
};

const generateSafeFilename = (url) => {
    const urlObj = new URL(url);
    let baseName = path.basename(urlObj.pathname);
    if (urlObj.search) {
        // Handling query strings
        baseName += '_' + encodeURIComponent(urlObj.search).replace(/%/g, '');
    }
    return baseName;
};

const processHTML = async (htmlFilePath) => {
    console.log(`Processing HTML file: ${htmlFilePath}`);
    const htmlContent = fs.readFileSync(htmlFilePath, 'utf8');
    const $ = cheerio.load(htmlContent);

    // Handle script tags
    const scriptTags = $('script[src]');
    for (let i = 0; i < scriptTags.length; i++) {
        const src = scriptTags[i].attribs.src;
        if (src.startsWith('http')) {
            const filename = generateSafeFilename(src);
            const localPath = `./local-libs/${filename}`;
            try {
                await downloadFile(src, localPath);
                $(scriptTags[i]).attr('src', localPath);
            } catch (error) {
                console.error(`Error downloading ${src}: ${error.message}`);
            }
        }
    }

    // Handle link tags for CSS
    const linkTags = $('link[rel="stylesheet"]');
    for (let i = 0; i < linkTags.length; i++) {
        const href = linkTags[i].attribs.href;
        if (href && href.startsWith('http')) {
            const filename = generateSafeFilename(href);
            const localPath = `./local-libs/${filename}`;
            try {
                await downloadFile(href, localPath);
                $(linkTags[i]).attr('href', localPath);
            } catch (error) {
                console.error(`Error downloading ${href}: ${error.message}`);
            }
        }
    }

    const modifiedHtml = $.html();
    const newHtmlFilePath = './chrome_index.html';
    fs.writeFileSync(newHtmlFilePath, modifiedHtml);
    console.log(`Created modified HTML file: ${newHtmlFilePath}`);
};

processHTML('./index.html').catch(error => {
    console.error(`An error occurred: ${error.message}`);
});
