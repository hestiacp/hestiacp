/* eslint-env node */
/* eslint-disable no-console */

// Build JS and CSS using esbuild and PostCSS
import { promises as fs } from 'node:fs';
import path from 'node:path';
import esbuild from 'esbuild';
import postcss from 'postcss';
import postcssConfig from './postcss.config.js';

// Packages to build but exclude from bundle
const externalPackages = ['chart.js/auto'];

// Build main bundle
async function buildJS() {
	const inputPath = './web/js/src/main.js';
	try {
		await esbuild.build({
			entryPoints: [inputPath],
			outfile: './web/js/dist/main.min.js',
			bundle: true,
			minify: true,
			sourcemap: true,
			external: externalPackages,
		});
		console.log('✅ JavaScript build completed for', inputPath);
	} catch (error) {
		console.error('❌ Error building JavaScript:', error);
		process.exit(1);
	}
}

// Build external packages
async function buildExternalJS() {
	try {
		const buildPromises = externalPackages.map(async (pkg) => {
			const outputPath = `./web/js/dist/${pkg.replace('/', '-')}.min.js`;
			await esbuild.build({
				entryPoints: [pkg],
				outfile: outputPath,
				bundle: true,
				minify: true,
				format: 'esm',
			});
			console.log(`✅ Dependency build completed for ${pkg}`);
		});

		await Promise.all(buildPromises);
	} catch (error) {
		console.error('❌ Error building external packages:', error);
		process.exit(1);
	}
}

// Process a CSS file
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

// Build CSS
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
	await buildExternalJS();
	await buildCSS();
	console.log('🎉 Build completed.');
}

// Execute build
build();
