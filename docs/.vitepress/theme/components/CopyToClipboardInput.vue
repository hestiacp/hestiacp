<template>
	<div class="CopyToClipboardInput" v-bind="$attrs">
		<input type="text" class="CopyToClipboardInput-input" readonly :value="value" />
		<button
			type="button"
			class="CopyToClipboardInput-button"
			@click="copyToClipboard"
			title="Copy to Clipboard"
		>
			Copy
		</button>
	</div>
</template>

<script setup>
import { ref } from "vue";

defineProps({
	value: {
		type: String,
		required: true,
	},
});

const copyToClipboard = (event) => {
	const inputValue = event.target.previousSibling.value;
	navigator.clipboard.writeText(inputValue).then(
		() => {
			event.target.textContent = "Copied!";
			setTimeout(() => {
				event.target.textContent = "Copy";
			}, 1000);
		},
		(err) => {
			console.error("Could not copy to clipboard:", err);
		},
	);
};
</script>

<style scoped>
.CopyToClipboardInput {
	position: relative;
}
.CopyToClipboardInput-input {
	font-size: 0.9em;
	font-family: monospace;
	border: 1px solid var(--vp-c-border);
	border-radius: 4px;
	background-color: var(--vp-c-bg);
	width: 100%;
	padding: 8px 13px;
	padding-right: 53px;

	&:hover {
		border-color: var(--vp-c-border-hover);
	}

	&:focus {
		border-color: var(--vp-c-brand);
	}
}
.CopyToClipboardInput-button {
	position: absolute;
	top: 1px;
	right: 1px;
	bottom: 1px;
	border-top-right-radius: 3px;
	border-bottom-right-radius: 3px;
	color: var(--vp-c-brand);
	font-weight: 600;
	padding: 6px 10px;
	background-color: var(--vp-c-bg);
}
</style>
