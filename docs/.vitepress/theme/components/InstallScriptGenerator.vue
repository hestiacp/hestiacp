<template>
	<div class="container">
		<div class="output-card">
			<h2 class="u-text-center">Installation instructions</h2>
			<p class="u-mb10">
				Log in to your server e.g.
				<code>ssh root@your.server</code> and download the installation script:
			</p>
			<div class="u-pos-relative u-mb10">
				<input
					type="text"
					class="form-control u-monospace"
					readonly
					value="wget https://raw.githubusercontent.com/hestiacp/hestiacp/release/install/hst-install.sh"
				/>
				<button
					type="button"
					class="button-positioned"
					@click="
						copyToClipboard(
							'wget https://raw.githubusercontent.com/hestiacp/hestiacp/release/install/hst-install.sh',
							$event.target,
						)
					"
					title="Copy to Clipboard"
				>
					Copy
				</button>
			</div>
			<p class="u-mb10">
				Check you are running as the <code>root</code> user, configure the options you want below,
				then run:
			</p>
			<div class="u-pos-relative u-mb10">
				<input type="text" class="form-control u-monospace" readonly :value="installCommand" />
				<button
					type="button"
					class="button-positioned"
					@click="copyToClipboard(installCommand, $event.target)"
					title="Copy to Clipboard"
				>
					Copy
				</button>
			</div>
		</div>
		<h2 class="u-text-center">Configure options</h2>
		<ul class="option-list">
			<li
				v-for="option in options"
				:key="option.flag"
				:class="{
					'option-item': true,
					'is-active': selectedOptions[option.flag].enabled,
					'is-clickable': isGroupClickable(option),
				}"
				@click="toggleGroup(option)"
			>
				<div class="form-check u-mb10">
					<input
						type="checkbox"
						class="form-check-input"
						:id="option.flag"
						v-model="selectedOptions[option.flag].enabled"
					/>
					<label :for="option.flag" @click.stop>{{ option.label || option.description }}</label>
				</div>
				<div v-if="selectedOptions[option.flag].enabled">
					<label
						v-if="option.type && option.type !== 'checkbox'"
						class="form-label"
						:for="`${option.flag}-input`"
					>
						{{ option.description }}
					</label>
					<p v-else>
						{{ option.description }}
					</p>
					<div v-if="option.type === 'text'">
						<input
							class="form-control"
							:type="option.type"
							:id="`${option.flag}-input`"
							v-model="selectedOptions[option.flag].value"
						/>
					</div>
					<div v-if="option.type === 'select'">
						<select
							class="form-select"
							:id="`${option.flag}-input`"
							v-model="selectedOptions[option.flag].value"
						>
							<option v-for="lang in languages" :key="lang.value" :value="lang.value">
								{{ lang.text }}
							</option>
						</select>
					</div>
				</div>
				<p v-else>{{ option.description }}</p>
			</li>
		</ul>
	</div>
</template>

<script setup>
import { ref, watchEffect } from "vue";

const { options, languages } = defineProps({
	options: {
		type: Array,
		required: true,
	},
	languages: {
		type: Array,
		required: true,
	},
});

// Initialize selectedOptions with default values
const selectedOptions = ref({});
options.forEach((option) => {
	selectedOptions.value[option.flag] = {
		enabled: option.default === "yes",
		value: option.default !== "yes" && option.default !== "no" ? option.default : null,
	};
});

// Methods to handle clicking the entire form group
const isGroupClickable = (option) => {
	return !option.type || !selectedOptions.value[option.flag].enabled;
};
const toggleGroup = (option) => {
	// Only toggle if option is a standard checkbox, or the option is unchecked
	if (!option.type || !selectedOptions.value[option.flag].enabled) {
		selectedOptions.value[option.flag].enabled = !selectedOptions.value[option.flag].enabled;
	}
};

const copyToClipboard = (text, button) => {
	navigator.clipboard.writeText(text).then(
		() => {
			button.textContent = "Copied!";
			setTimeout(() => {
				button.textContent = "Copy";
			}, 1000);
		},
		(err) => {
			console.error("Could not copy to clipboard:", err);
		},
	);
};

const installCommand = ref("bash hst-install.sh");

watchEffect(() => {
	let cmd = "bash hst-install.sh";
	for (const [key, { enabled, value }] of Object.entries(selectedOptions.value)) {
		const opt = options.find((o) => o.flag === key);

		if (!opt.type || opt.type === "checkbox") {
			if (enabled !== (opt.default === "yes")) {
				cmd += ` --${key}=${enabled ? "yes" : "no"}`;
			}
		} else if (enabled && value !== opt.default) {
			cmd += ` --${key}=${value}`;
		}
	}
	installCommand.value = cmd;
});
</script>

<style scoped>
h2 {
	font-size: 1.3em;
	font-weight: 600;
	margin-bottom: 20px;
}
.container {
	display: flex;
	flex-direction: column;
	margin: 0 auto;
	max-width: 1152px;
}
.output-card {
	background-color: var(--vp-c-bg-alt);
	border-radius: 10px;
	padding: 30px 40px;
	margin-top: 40px;
	margin-bottom: 30px;

	& .form-control {
		padding-right: 53px;
	}
}
.option-list {
	display: grid;
	grid-gap: 20px;
	margin-bottom: 50px;

	@media (min-width: 640px) {
		grid-template-columns: 1fr 1fr;
	}

	@media (min-width: 960px) {
		grid-template-columns: 1fr 1fr 1fr;
	}
}
.option-item {
	font-size: 0.9em;
	border-radius: 10px;
	border: 2px solid transparent;
	padding: 15px 20px;
	background-color: var(--vp-c-bg-alt);
	transition: border-color 0.2s;

	&:hover {
		border-color: var(--vp-button-brand-hover-bg);
	}

	&.is-active {
		border-color: var(--vp-button-brand-active-bg);
	}
}
.form-label {
	display: inline-block;
	padding-bottom: 5px;
}
.form-control {
	font-size: 0.9em;
	border: 1px solid var(--vp-c-border);
	border-radius: 4px;
	background-color: var(--vp-c-bg);
	width: 100%;
	padding: 5px 10px;

	&:hover {
		border-color: var(--vp-c-border-hover);
	}

	&:focus {
		border-color: var(--vp-c-brand);
	}
}
.form-select {
	appearance: auto;
	font-size: 0.9em;
	border: 1px solid var(--vp-c-border);
	border-radius: 4px;
	background-color: var(--vp-c-bg);
	padding: 5px 10px;
	width: 100%;

	&:hover {
		border-color: var(--vp-c-border-hover);
	}

	&:focus {
		border-color: var(--vp-c-brand);
	}
}
.form-check {
	position: relative;
	padding-left: 20px;
	margin-left: 3px;
	min-height: 24px;

	& label {
		font-weight: 600;
		display: block;

		&:hover {
			cursor: pointer;
		}
	}
}
.form-check-input {
	cursor: pointer;
	position: absolute;
	margin-top: 5px;
	margin-left: -20px;
}
.button-positioned {
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
.u-mb10 {
	margin-bottom: 10px !important;
}
.u-text-center {
	text-align: center !important;
}
.u-monospace {
	font-family: monospace !important;
}
.u-pos-relative {
	position: relative !important;
}
.is-clickable {
	cursor: pointer;
}
</style>
