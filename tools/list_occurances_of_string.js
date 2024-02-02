const fs = require('fs');
const path = require('path');

// Usage
const directoryPath = 'C:\\Users\\User\\OneDrive\\code\\decentralized-fda'; // Replace with your directory path
const substring = 'Correlation'; // Replace with the substring you're looking for

const getAllFiles = (dirPath, arrayOfFiles) => {
  let files = fs.readdirSync(dirPath);

  arrayOfFiles = arrayOfFiles || [];

  files.forEach((file) => {
    if (fs.statSync(dirPath + "/" + file).isDirectory()) {
      arrayOfFiles = getAllFiles(dirPath + "/" + file, arrayOfFiles);
    } else {
      arrayOfFiles.push(path.join(dirPath, "/", file));
    }
  });

  return arrayOfFiles;
};

const findVariations = (dir, substring) => {
  const allFiles = getAllFiles(dir);
  const variations = new Set();

  allFiles.forEach((file) => {
    const filename = path.basename(file);
    if (filename.includes(substring)) {
      variations.add(filename);
    }
  });

  return Array.from(variations);
};


const variations = findVariations(directoryPath, substring);
console.log(variations);
