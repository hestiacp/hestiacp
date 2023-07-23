/* eslint-env node */
/* eslint-disable no-console */

// Build JS and CSS using esbuild and PostCSS
import { promises as fs } from 'node:fs';
import path from 'node:path';
import browserslist from 'browserslist';
import esbuild from 'esbuild';
import * as lightningcss from 'lightningcss';

// Packages to build but exclude from bundle
const externalPackages = ['chart.js/auto', 'alpinejs/dist/cdn.min.js'];

// Build main bundle
async function buildJS() {
	const inputPath = './web/js/src/index.js';
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
			const outputPath = getOutputPath(pkg);
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

function getOutputPath(pkg) {
	let pkgName;

	if (pkg.startsWith('alpinejs')) {
		pkgName = 'alpinejs';
	} else {
		pkgName = pkg.replace(/\//g, '-');
	}

	return `./web/js/dist/${pkgName}.min.js`;
}

// Process a CSS file
async function processCSS(inputFile, outputFile) {
	try {
		await ensureDir(path.dirname(outputFile));
		const css = await fs.readFile(inputFile);
		const bundle = await lightningcss.bundleAsync({
			filename: inputFile,
			sourceMap: true,
			code: Buffer.from(css),
			minify: true,
			targets: lightningcss.browserslistToTargets(browserslist()),
			drafts: { customMedia: true, nesting: true },
			visitor: {
				Url: (node) => {
					// Fix relative paths for webfonts
					if (node.url.startsWith('../webfonts/')) {
						return {
							url: node.url.replace('../webfonts/', '/webfonts/'),
							loc: node.loc,
						};
					}
					return node;
				},
			},
			resolver: {
				resolve(specifier, from) {
					if (!specifier.endsWith('.css')) {
						specifier += '.css';
					}
					if (specifier.startsWith('node:')) {
						return `node_modules/${specifier.replace('node:', '')}`;
					}
					return `${path.dirname(from)}/${specifier}`;
				},
			},
		});
		await fs.writeFile(outputFile, bundle.code);
		await fs.writeFile(`${outputFile}.map`, bundle.map);
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

// Ensure a directory exists
async function ensureDir(dir) {
	try {
		await fs.mkdir(dir, { recursive: true });
	} catch (error) {
		if (error.code !== 'EEXIST') {
			throw error;
		}
	}
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
