const fs = require('fs').promises;
const path = require('path');

const search_term = "User Correlation";
const replace_term = "User Variable Relationship";
const target_directory = "C:/Users/User/OneDrive/code/decentralized-fda"; // Replace with actual path
const allowed_extensions = ['.md', '.php', '.ts', '.js', '.html', '.css', '.txt', '.json', '.xml', '.yaml', '.yml', '.sh'];
const excluded_dirs = [".git", ".svn", ".idea"];

function generateCaseVariations(term) {
  const lowerCase = term.toLowerCase();
  const upperCase = term.toUpperCase();
  const titleCase = term.replace(/\w\S*/g, w => w.replace(/^\w/, c => c.toUpperCase()));
  const camelCase = lowerCase.charAt(0).toLowerCase() + titleCase.slice(1).replace(/\s/g, '');
  const specificCamelCase = camelCase.charAt(0).toUpperCase() + camelCase.slice(1);
  const snakeCase = lowerCase.replace(/ /g, '_');
  const kebabCase = lowerCase.replace(/ /g, '-');
  const screamingSnakeCase = upperCase.replace(/ /g, '_');
  return [lowerCase, titleCase, camelCase, specificCamelCase, snakeCase, kebabCase, upperCase, screamingSnakeCase];
}

async function renameEntity(entityPath, searchVariations, replaceVariations) {

  const entityName = path.basename(entityPath);
  let newName = entityName;

  searchVariations.forEach((variation, index) => {
    let split = newName.split(variation);
    newName = split.join(replaceVariations[index]);
  });

  if (newName !== entityName) {
    const newPath = path.join(path.dirname(entityPath), newName);
    await fs.rename(entityPath, newPath);
    console.log(`Renamed: ${entityPath} -> ${newPath}`);
    return newPath;
  }
  return entityPath;
}

async function replaceInFile(filePath, searchVariations, replaceVariations) {
  let content = await fs.readFile(filePath, 'utf8');
  const originalContent = content;

  searchVariations.forEach((variation, index) => {
    content = content.split(variation).join(replaceVariations[index]);
  });

  if (content !== originalContent) {
    await fs.writeFile(filePath, content, 'utf8');
    console.log(`Modified: ${filePath}`);
  } else {
    console.log(`No changes: ${filePath}`);
  }
}

function isIgnored(filePath, ignorePatterns, excludedDirs, rootDir) {
  const relativePath = path.relative(rootDir, filePath);
  return excludedDirs.some(dir => relativePath.startsWith(dir + path.sep)) ||
    ignorePatterns.some(pattern => relativePath === pattern || relativePath.startsWith(pattern + path.sep));
}

async function processDirectory(directory, searchVariations, replaceVariations, allowedExtensions, ignorePatterns, excludedDirs, rootDir) {
  const files = await fs.readdir(directory, { withFileTypes: true });

  for (const file of files) {
    const filePath = path.join(directory, file.name);

    if (isIgnored(filePath, ignorePatterns, excludedDirs, rootDir)) {
      continue;
    }

    if (file.isDirectory()) {
      await processDirectory(filePath, searchVariations, replaceVariations, allowedExtensions, ignorePatterns, excludedDirs, rootDir);
      await renameEntity(filePath, searchVariations, replaceVariations);
    } else {
      const extension = path.extname(file.name).toLowerCase();
      if (allowedExtensions.includes(extension)) {
        await replaceInFile(filePath, searchVariations, replaceVariations);
      }
      await renameEntity(filePath, searchVariations, replaceVariations);
    }
  }
}

async function startProcessing() {
  const searchVariations = generateCaseVariations(search_term);
  const replaceVariations = generateCaseVariations(replace_term);
  const ignorePatterns = await collectGitignorePatterns(target_directory);

  await processDirectory(target_directory, searchVariations, replaceVariations, allowed_extensions, ignorePatterns, excluded_dirs, target_directory);
}

async function readGitignore(directory) {
  const gitignorePath = path.join(directory, '.gitignore');
  try {
    const gitignoreContent = await fs.readFile(gitignorePath, 'utf8');
    return gitignoreContent.split(/\r?\n/).filter(line => line && !line.startsWith('#')).map(line => line.trim());
  } catch (err) {
    return [];
  }
}

async function collectGitignorePatterns(startPath) {
  let patterns = [];
  let currentPath = startPath;

  while (true) {
    const parentPatterns = await readGitignore(currentPath);
    patterns = [...patterns, ...parentPatterns];

    const parentPath = path.dirname(currentPath);
    if (parentPath === currentPath) {
      break;
    }
    currentPath = parentPath;
  }

  return patterns;
}


startProcessing().catch(err => console.error(err));
