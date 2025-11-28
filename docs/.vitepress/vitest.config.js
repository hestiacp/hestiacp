// biome-ignore assist/source/organizeImports: Disable organize imports check in Biome
import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
	test: {
		environment: 'jsdom',
	},
	plugins: [vue()],
});
