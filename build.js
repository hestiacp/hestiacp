// Build JS and CSS using esbuild and PostCSS
const esbuild = require('esbuild');
const postcss = require('postcss');
const fs = require('fs').promises;
const path = require('path');
const postcssConfig = require('./postcss.config.js');

// Esbuild JavaScript configuration
const esbuildConfig = {
	outdir: './web/js/dist',
	entryNames: '[dir]/[name].min',
	minify: true,
};

// Build JavaScript
async function buildJS() {
	const jsSrcPath = './web/js/src/';
	const jsEntries = await fs.readdir(jsSrcPath);
	const jsBuildPromises = jsEntries
		.filter((entry) => path.extname(entry) === '.js')
		.map((entry) => {
			const inputPath = path.join(jsSrcPath, entry);
			return esbuild
				.build({
					...esbuildConfig,
					entryPoints: [inputPath],
				})
				.then(() => {
					console.log('✅ JavaScript build completed for', inputPath);
				});
		});

	try {
		await Promise.all(jsBuildPromises);
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
	const themesSrcPath = './web/css/src/themes/';
	const cssEntries = await fs.readdir(themesSrcPath);

	const cssBuildPromises = cssEntries
		.filter((entry) => path.extname(entry) === '.css')
		.map(async (entry) => {
			const entryName = entry.replace('.css', '.min.css');
			const inputPath = path.join(themesSrcPath, entry);
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
