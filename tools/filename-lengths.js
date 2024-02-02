const fs = require('fs');
const path = require('path');

function listFilesInDirectory(dir, fileList = []) {
  const files = fs.readdirSync(dir);

  files.forEach(file => {
    const filePath = path.join(dir, file);
    if (fs.statSync(filePath).isDirectory()) {
      fileList = listFilesInDirectory(filePath, fileList);
    } else {
      fileList.push(filePath);
    }
  });

  return fileList;
}

function sortByPathLength(files) {
  return files.sort((a, b) => b.length - a.length);
}

const directoryPath = 'C:\\Users\\User\\OneDrive\\code\\decentralized-fda'; // Change this to your directory path
const allFiles = listFilesInDirectory(directoryPath);
const sortedFiles = sortByPathLength(allFiles);

console.log(sortedFiles);
