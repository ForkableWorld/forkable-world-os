const fs = require('fs-extra');
const path = require('path');
const fg = require('fast-glob');

const directory = '../docs/'; // Set your target directory here.

async function main() {
  const imageFiles = await fg('**/*.+(png|jpg|jpeg|gif|svg)', { cwd: directory });
  const textFiles = await fg('**/*.+(md|html)', { cwd: directory });

  const imagesToKeep = new Set();
  const imagesToDiscard = new Set();

  for (const textFile of textFiles) {
    const content = await fs.readFile(path.join(directory, textFile), 'utf-8');
    const lines = content.split(/\r?\n/);

    for (const line of lines) {
      for (const imageFile of imageFiles) {
        const imageName = path.basename(imageFile);
        if (line.includes(imageName)) {
          imagesToKeep.add(imageFile);
        }
      }
    }
  }

  for (const image of imageFiles) {
    if (!imagesToKeep.has(image)) {
      console.log(`Deleting image: ${image}`);
      imagesToDiscard.add(image);
    }
  }
  console.log(`Total images: ${imageFiles.length}`);
  console.log(`Total images to keep: ${imagesToKeep.size}`);
  console.log(`Total images to discard: ${imagesToDiscard.size}`);
  for(const image of imagesToDiscard) {
    console.log(`Deleting image: ${image}`);
    await fs.remove(path.join(directory, image));
  }
}

main()
  .then(() => console.log('Finished deleting images.'))
  .catch((error) => console.error(`Error: ${error.message}`));
