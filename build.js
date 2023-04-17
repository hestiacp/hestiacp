// Build JS and CSS using esbuild and PostCSS
import esbuild from 'esbuild';
import postcss from 'postcss';
import { promises as fs } from 'node:fs';
import path from 'node:path';
import postcssConfig from './postcss.config.js';

const esbuildConfig = {
	outfile: './web/js/dist/main.min.js',
	bundle: true,
	minify: true,
	sourcemap: true,
};

// Build JavaScript
async function buildJS() {
	const inputPath = './web/js/src/main.js';
	try {
		await esbuild.build({
			...esbuildConfig,
			entryPoints: [inputPath],
		});
		console.log('✅ JavaScript build completed for', inputPath);
	} catch (error) {
		console.error('❌ Error building JavaScript:', error);
		process.exit(1);
	}
}

// Process and build CSS
async function processCSS(inputFile, outputFile) {
	try {
		const css = await fs.readFile(inputFile);
		const result = await postcss(postcssConfig.plugins).process(css, {
			from: inputFile,
			to: outputFile,
		});
		await fs.writeFile(outputFile, result.css);
		console.log(`✅ CSS build completed for ${inputFile}`);
	} catch (error) {
		console.error(`❌ Error processing CSS for ${inputFile}:`, error);
		process.exit(1);
	}
}

// Build CSS files
async function buildCSS() {
	const themesSourcePath = './web/css/src/themes/';
	const cssEntries = await fs.readdir(themesSourcePath);

	const cssBuildPromises = cssEntries
		.filter((entry) => path.extname(entry) === '.css')
		.map(async (entry) => {
			const entryName = entry.replace('.css', '.min.css');
			const inputPath = path.join(themesSourcePath, entry);
			const outputPath = `./web/css/themes/${entryName}`;
			await processCSS(inputPath, outputPath);
		});

	await Promise.all(cssBuildPromises);
}

// Build all assets
async function build() {
	console.log('🚀 Building JS and CSS...');
	await buildJS();
	await buildCSS();
	console.log('🎉 Build completed.');
}

// Execute build
build();
