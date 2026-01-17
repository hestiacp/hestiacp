<template>
	<div class="InstallBuilder">
		<div class="container">
			<div class="output-card">
				<h2 class="u-text-center">Installation instructions</h2>
				<p class="u-mb10">
					Log in to your server e.g.
					<code>ssh root@your.server</code> then download the installation script:
				</p>
				<CopyToClipboardInput
					class="u-mb10"
					value="wget https://raw.githubusercontent.com/hestiacp/hestiacp/release/install/hst-install.sh"
				/>
				<p class="u-mb10">
					Check you are running as the <code>root</code> user, configure the options you want below,
					then run:
				</p>
				<CopyToClipboardInput class="u-mb10" :value="installCommand" />
			</div>
			<h2 class="u-text-center">Configure options</h2>
			<ul class="option-list">
				<li
					v-for="option in options"
					:key="option.flag"
					:class="{
						'option-item': true,
						'is-active': selectedOptions[option.flag].enabled,
						'is-clickable': !option.type || !selectedOptions[option.flag].enabled,
					}"
					@click="toggleOption(option)"
				>
					<div class="option-header">
						<div class="form-check">
							<input
								type="checkbox"
								class="form-check-input"
								:id="option.flag"
								v-model="selectedOptions[option.flag].enabled"
							/>
							<label :for="option.flag" @click.stop>{{ option.label }}</label>
						</div>
						<div class="option-icon" v-tooltip="option.description">
							<i class="fa-solid fa-circle-info"></i>
						</div>
					</div>
					<div v-if="selectedOptions[option.flag].enabled && option.type" class="option-content">
						<label
							v-if="option.type && option.type !== 'checkbox'"
							class="form-label"
							:for="`${option.flag}-input`"
						>
							{{ option.description }}
						</label>
						<input
							v-if="option.type === 'text'"
							class="form-control"
							type="text"
							:id="`${option.flag}-input`"
							v-model="selectedOptions[option.flag].value"
						/>
						<select
							v-if="option.type === 'select'"
							class="form-select"
							:id="`${option.flag}-input`"
							v-model="selectedOptions[option.flag].value"
						>
							<option v-for="opt in option.options" :key="opt.value" :value="opt.value">
								{{ opt.label }}
							</option>
						</select>
					</div>
				</li>
			</ul>
		</div>
	</div>
</template>

<script setup>
import { ref, watchEffect } from "vue";
import CopyToClipboardInput from "./CopyToClipboardInput.vue";

const { options } = defineProps({
	options: {
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

// Handle clicking the entire option "card"
const toggleOption = (option) => {
	// Only toggle if option is a standard checkbox, or the option is unchecked
	if (!option.type || !selectedOptions.value[option.flag].enabled) {
		selectedOptions.value[option.flag].enabled = !selectedOptions.value[option.flag].enabled;
	}
};

// Build the install command
const installCommand = ref("bash hst-install.sh");
watchEffect(() => {
	let cmd = "bash hst-install.sh";
	const quoteshellarg = (str) => {
		if (!str) return "''";
		return `'${str.replace(/'/g, "'\\''")}'`;
	};
	for (const [key, { enabled, value }] of Object.entries(selectedOptions.value)) {
		const opt = options.find((o) => o.flag === key);
		if (opt.flag === "force" && enabled) {
			cmd += " --force";
		} else if (!opt.type || opt.type === "checkbox") {
			if (enabled !== (opt.default === "yes")) {
				cmd += ` --${key} ${enabled ? "yes" : "no"}`;
			}
		} else if (enabled && value !== opt.default) {
			const value_quoted = quoteshellarg(value);
			cmd += ` --${key} ${value_quoted}`;
		}
	}
	installCommand.value = cmd;
});
</script>

<style scoped>
.InstallBuilder {
	padding: 0 24px;

	@media (min-width: 640px) {
		padding: 0 48px;
	}

	@media (min-width: 960px) {
		padding: 0 72px;
	}
}
h2 {
	font-size: 24px;
	font-weight: 600;
	margin-bottom: 25px;
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
	padding: 30px;
	margin-top: 40px;
	margin-bottom: 40px;

	@media (min-width: 640px) {
		padding: 30px 50px;
	}
}
.option-list {
	display: grid;
	grid-gap: 23px;
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
	padding: 10px 20px;
	background-color: var(--vp-c-bg-alt);
	transition: border-color 0.2s;

	&:hover {
		border-color: var(--vp-button-brand-hover-bg);
	}

	&.is-active {
		border-color: var(--vp-button-brand-active-bg);
	}
}
.option-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
}
.option-icon {
	padding: 5px;
	margin-right: -5px;

	& i {
		opacity: 0.7;
	}

	&:hover i {
		opacity: 1;
	}
}
.option-content {
	margin-top: 5px;
	margin-bottom: 5px;
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
	padding: 6px;
	width: 100%;

	&:hover {
		border-color: var(--vp-c-border-hover);
	}

	&:focus {
		border-color: var(--vp-c-brand);
	}
}
.form-check {
	flex-grow: 1;
	position: relative;
	padding-left: 25px;

	& label {
		font-size: 16px;
		font-weight: 600;
		display: block;
		line-height: 1.6;

		&:hover {
			cursor: pointer;
		}
	}
}
.form-check-input {
	cursor: pointer;
	position: absolute;
	width: 15px;
	height: 15px;
	margin-top: 5px;
	margin-left: -25px;
}
.u-mb10 {
	margin-bottom: 10px !important;
}
.u-text-center {
	text-align: center !important;
}
.is-clickable {
	cursor: pointer;
}
</style>
