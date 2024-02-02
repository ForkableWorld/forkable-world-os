import * as fs from 'fs';
import * as path from 'path';
import * as dotenv from 'dotenv';
import { PathOrFileDescriptor } from "fs";

const JSON_PROMPT: string = `Generate a JSON file for this directory in this Laravel app called the Decentralized FDA. \n` +
	`The JSON for the folder should have the following properties: \n` +
	` - 'name': the name of the directory, \n` +
	` - 'image': a suitable image for the directory, \n` +
	` - 'fontAwesome': a suitable font awesome icon for the directory, \n` +
	` - 'relativePath': the relative path of the directory from the project root, \n` +
	` - 'URL': a URL for accessing the directory, \n` +
	` - 'displayName': a display name for the directory, \n` +
	` - 'description': a description explaining the general purpose of the directory, \n` +
	` - 'files': an array of objects, each representing a file in the directory. \n` +
	`Each object in the 'files' array should have the following properties: \n` +
	` - 'name': the name of the file, \n` +
	` - 'image': a suitable image for the file, \n` +
	` - 'fontAwesome': a suitable font awesome icon for the file, \n` +
	` - 'relativePath': the relative path of the file from the project root, \n` +
	` - 'URL': a URL for accessing the file the file in the GitHub repository https://github.com/curedao/decentralized-fda/tree/develop/apps/dfda-1/relativePath, \n` +
	` - 'displayName': a display name for the file, \n` +
	` - 'description': a description explaining the general purpose of the file. \n`


const README_PROMPT: string = "Generate a README for this directory in this Laravel app called the Decentralized FDA. " +
	"The README should first describe the general purpose the directory. " +
	"This should be followed by a list of the files in the directory. " +
	"Each file name in this list should be hyperlinked to the file and followed by a description of the purpose and usage of the file. "
// import { Configuration, OpenAIApi } from 'openai';

dotenv.config();
const { Configuration, OpenAIApi } = require("openai");

const configuration = new Configuration({
																					apiKey: process.env.OPENAI_API_KEY,
																				});
const openai = new OpenAIApi(configuration);

// Pseudocode for getting a response from ChatGPT API
async function getChatGptResponse(context: string): Promise<string> {

	//const models = await openai.listModels();
	const completion = await openai.createChatCompletion({
		model: "gpt-3.5-turbo",
		temperature: 0.1,
		messages: [
		 {
			 role: "user",
			 content: context,
		 },
		],
	}).catch((err: any) => {
		console.log(`Could not get a response from the ChatGPT API.\nError: ${err}\n for context:\n ${context}`);
		throw err;
	});
	let content: string = completion.data.choices[0].message.content;
	console.log(content);
	return content;
}

function readReadmeFileInThisDirectory(): string {
	// Read the readme file in this directory
	const readme = fs.readFileSync(path.join(__dirname, 'README.md'), 'utf-8');
	return readme;
}

function extractPHPDetails(filePath: string): { functions: string[], properties: string[] } {
	const fileContent = fs.readFileSync(filePath, 'utf-8');

	// Regular expression to match PHP function names. This simple pattern might not cover all possible function definitions.
	const functionRegex = /function\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*\(/g;
	let functionMatch;
	let functions: string[] = [];

	while ((functionMatch = functionRegex.exec(fileContent)) !== null) {
		functions.push(functionMatch[1]);
	}

	// Regular expression to match PHP property names. This will match public, protected, and private properties.
	const propertyRegex = /(public|protected|private)\s+(static\s+)?\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/g;
	let propertyMatch;
	let properties: string[] = [];

	while ((propertyMatch = propertyRegex.exec(fileContent)) !== null) {
		properties.push(propertyMatch[3]);
	}

	return { functions, properties };
}

async function generateJSON(directory: string): Promise<void> {
	const files = fs.readdirSync(directory);
	let context = '';
	let relativePaths: string[] = [];
	let absDirectoryPath = path.resolve(directory);
	let jsonPath: PathOrFileDescriptor = path.join(absDirectoryPath, 'directory-info.json');

	for (const file of files) {
		const filePath = path.join(directory, file);
		const relativeFilePath = path.relative(process.cwd(), filePath);
		if (fs.lstatSync(filePath).isDirectory()) {
			await generateJSON(filePath);
		} else {
			const extension = path.extname(file);
			if (extension === '.php') {
				relativePaths.push(relativeFilePath);
				const content = fs.readFileSync(filePath, 'utf-8');
				const relativePath = path.relative(__dirname, filePath);
				const { functions, properties } = extractPHPDetails(filePath);
				context += `File: ${file}\n${file} Functions: ${functions.join(', ')}\n${file} Properties: ${properties.join(', ')}` + ',\n';
				//context += content + '\n';
			}
		}
	}

	if (context) {
		if(fs.existsSync(jsonPath)){
			return;
		}
		context = JSON_PROMPT +
			`Here's a list of the paths to all the files in the directory: \n` + context + '\n' +
			`The name of the directory is: ${path.basename(directory)}. \n` +
			`The relative path of the directory is: ${path.relative(process.cwd(), directory)}. \n`;
		console.log(context);
		const json = await getChatGptResponse(context);
		fs.writeFileSync(jsonPath, json);
		//let text: string = readme.toString();
		//text = text.replace('](app/', '](app/');
	}
}

async function generateReadme(directory: string): Promise<void> {
	const files = fs.readdirSync(directory);

	for (const file of files) {
		const filePath = path.join(directory, file);
		if (fs.lstatSync(filePath).isDirectory()) {
			await generateReadme(filePath);
		} else if (path.basename(filePath) === 'directory-info.json') {
			// Read and parse the JSON file
			const directoryInfo = JSON.parse(fs.readFileSync(filePath, 'utf-8'));
			let readmeContent = `# ${directoryInfo.name}\n\n`;

			// Add a description for the directory (if you have one in the json)
			// readmeContent += `**Description:** ${directoryInfo.description}\n\n`;

			readmeContent += `**Relative Path:** ${directoryInfo.relativePath}\n\n`;

			readmeContent += `## Files\n\n`;
			for (const file of directoryInfo.files) {
				readmeContent += `- [${file.name}](${file.link})\n`;
				readmeContent += `  - **Path:** ${file.path}\n`;
				readmeContent += `  - **Description:** ${file.description}\n\n`;
			}

			// Write the content to the README.md file
			fs.writeFileSync(path.join(directory, 'README.md'), readmeContent);
		}
	}
}

generateJSON('app')
	.then(r => console.log(r))
	.then(function() {
		generateReadme('app').then(r => console.log('README files generated.'));
	});
