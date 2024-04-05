import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
	test: {
		environment: 'jsdom',
	},
	plugins: [vue()],
});
