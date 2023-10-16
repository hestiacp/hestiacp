<template>
	<div class="CopyToClipboardInput" v-bind="$attrs">
		<input
			type="text"
			class="CopyToClipboardInput-input"
			:value="value"
			@focus="selectText"
			readonly
		/>
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
defineProps({
	value: {
		type: String,
		required: true,
	},
});

const selectText = (event) => {
	const inputElement = event.target;
	inputElement.select();
	inputElement.removeEventListener("focus", selectText);
};

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
	display: flex;
}
.CopyToClipboardInput-input {
	font-size: 0.9em;
	font-family: monospace;
	flex-grow: 1;
	border: 1px solid var(--vp-c-border);
	border-top-left-radius: 4px;
	border-bottom-left-radius: 4px;
	background-color: var(--vp-c-bg);
	padding: 8px 13px;

	&:focus {
		border-color: var(--vp-button-brand-bg);
	}
}
.CopyToClipboardInput-button {
	font-size: 14px;
	border-top-right-radius: 4px;
	border-bottom-right-radius: 4px;
	color: var(--vp-button-brand-text);
	min-width: 73px;
	font-weight: 600;
	padding: 5px 10px;
	background-color: var(--vp-button-brand-bg);
	transition: background-color 0.25s;

	&:hover {
		background-color: var(--vp-button-brand-hover-bg);
	}
}
</style>
