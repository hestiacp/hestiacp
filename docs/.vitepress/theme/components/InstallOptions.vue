<script>
export default {
	props: {
		languages: {
			required: true,
			selected: "en",
		},
		items: {
			required: true,
		},
	},
	data() {
		return {
			pageloader: false,
			hestia_wget:
				"wget https://raw.githubusercontent.com/hestiacp/hestiacp/release/install/hst-install.sh",
			hestia_install: "sudo bash hst-install.sh",
			installStr: "",
		};
	},
	methods: {
		getOptionString(item) {
			if (item.textField) {
				return item.selected ? `${item.param} '${item.text}'` : "";
			}

			if (item.selectField) {
				return item.selected ? `${item.param} '${item.text}'` : "";
			}

			return item.param.includes("force") && item.selected
				? item.param
				: `${item.param}${item.selected ? " yes" : " no"}`;
		},
		generateString() {
			const installStr = this.items.map(this.getOptionString).filter(Boolean);

			this.installStr = `${this.hestia_install} ${installStr.join(" ")}`;
			this.$refs.dialog.showModal();
		},
		closeDialog(e) {
			if (e.target === this.$refs.dialogClose || e.target === this.$refs.dialog) {
				this.$refs.dialog.close();
			}
		},
		checkNeedEnabled(e) {
			if (e.target.value != "") {
				let id = e.target.getAttribute("target");
				if (!document.getElementById(id).checked) {
					document.getElementById(id).click();
				}
			}
		},
		toggleOption(e) {
			if (e.target.checked) {
				let conflicts = e.target.getAttribute("conflicts");
				if (conflicts) {
					if (document.getElementById(conflicts).checked) {
						document.getElementById(conflicts).click();
					}
				}
				let depends = e.target.getAttribute("depends");
				if (depends) {
					if (!document.getElementById(depends).checked) {
						document.getElementById(depends).click();
					}
				}
			}
		},
		copyToClipboard(text, button) {
			navigator.clipboard.writeText(text).then(
				() => {
					button.textContent = "Copied!";
					setTimeout(() => {
						button.textContent = "Copy";
					}, 1000);
				},
				(err) => {
					console.error("Could not copy to clipboard:", err);
				}
			);
		},
	},
};
</script>

<template>
	<div class="container">
		<div class="grid">
			<div class="form-group" v-for="item in items">
				<div class="form-check u-mb10">
					<input
						@change="toggleOption"
						type="checkbox"
						class="form-check-input"
						v-model="item.selected"
						:value="item.value"
						:id="item.id"
						:conflicts="item.conflicts"
						:depends="item.depends"
					/>
					<label :for="item.id">{{ item.id }}</label>
				</div>
				<template v-if="item.textField || item.selectField">
					<label class="form-label" :for="'input-' + item.id">{{ item.desc }}</label>
				</template>
				<template v-else>
					<p>{{ item.desc }}</p>
				</template>
				<div v-if="item.textField">
					<input
						@change="checkNeedEnabled"
						type="text"
						class="form-control"
						v-model="item.text"
						:target="item.id"
						:id="'input-' + item.id"
						:type="'+item.type+'"
					/>
				</div>
				<div v-if="item.selectField">
					<select class="form-select" v-model="item.text" :id="'input-' + item.id">
						<option v-for="language in languages" :value="language.value" :key="language.value">
							{{ language.text }}
						</option>
					</select>
				</div>
			</div>
		</div>
		<div class="u-text-center u-mb10">
			<button @click="generateString" class="form-submit" type="button">Submit</button>
		</div>
		<dialog ref="dialog" class="modal" @click="closeDialog">
			<button class="modal-close" @click="closeDialog" type="button" ref="dialogClose">
				Close
			</button>
			<div ref="dialogContent" class="modal-content">
				<h1 class="modal-heading">Installation instructions</h1>
				<p class="u-mb10">
					Log in to your server as root, either directly or via SSH:
					<code>ssh root@your.server</code> and download the installation script:
				</p>
				<div class="u-pos-relative">
					<input
						type="text"
						class="form-control u-monospace u-mb10"
						v-model="hestia_wget"
						readonly
					/>
					<button
						class="button-positioned"
						@click="copyToClipboard(hestia_wget, $event.target)"
						type="button"
						title="Copy to Clipboard"
					>
						Copy
					</button>
				</div>
				<p class="u-mb10">Then run the following command:</p>
				<div class="u-pos-relative">
					<textarea class="form-control u-min-height100" v-model="installStr" readonly />
					<button
						class="button-positioned"
						@click="copyToClipboard(installStr, $event.target)"
						type="button"
						title="Copy to Clipboard"
					>
						Copy
					</button>
				</div>
			</div>
		</dialog>
	</div>
</template>

<style scoped>
.container {
	margin: 0px auto;
	max-width: 1152px;
}
.grid {
	display: grid;
	grid-gap: 20px;
	margin-top: 30px;
	margin-bottom: 30px;

	@media (min-width: 640px) {
		grid-template-columns: 1fr 1fr;
	}

	@media (min-width: 960px) {
		grid-template-columns: 1fr 1fr 1fr;
	}
}
.form-group {
	font-size: 0.9em;
	border-radius: 10px;
	padding: 15px 20px;
	background-color: var(--vp-c-bg-alt);
}
.form-label {
	display: inline-block;
	margin-left: 2px;
	padding-bottom: 5px;
	text-transform: capitalize;
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
	}
}
.form-check-input {
	position: absolute;
	margin-top: 5px;
	margin-left: -20px;
}
.form-submit {
	border: 1px solid transparent;
	display: inline-block;
	font-weight: 600;
	transition: color 0.25s, border-color 0.25s, background-color 0.25s;
	border-radius: 20px;
	font-size: 16px;
	padding: 10px 20px;
	background-color: var(--vp-button-brand-bg);
	border-color: var(--vp-button-brand-border);
	color: var(--vp-button-brand-text);

	&:hover {
		background-color: var(--vp-button-brand-hover-bg);
		border-color: var(--vp-button-brand-hover-border);
		color: var(--vp-button-brand-hover-text);
	}

	&:active {
		background-color: var(--vp-button-brand-active-bg);
		border-color: var(--vp-button-brand-active-border);
		color: var(--vp-button-brand-active-text);
	}
}
.button-positioned {
	position: absolute;
	right: 1px;
	top: 1px;
	border-top-right-radius: 3px;
	border-bottom-right-radius: 3px;
	color: var(--vp-c-brand);
	font-weight: 600;
	padding: 6px 10px;
	background-color: var(--vp-c-bg);
}
.modal {
	position: fixed;
	border-radius: 10px;
	border: 1px solid var(--vp-c-border);
	box-shadow: 0 8px 40px 0 rgb(0 0 0 / 35%);
	padding: 0;

	&::backdrop {
		background-color: rgb(0 0 0 / 50%);
	}
}
.modal-close {
	position: absolute;
	top: 10px;
	right: 15px;
	font-weight: 600;
	color: var(--vp-c-brand);
}
.modal-content {
	padding: 30px;
}
.modal-heading {
	font-weight: 600;
	font-size: 1.3em;
	text-align: center;
	margin-bottom: 15px;
}
code {
	background-color: var(--vp-c-bg-alt);
	border-radius: 3px;
	padding: 2px 5px;
}
.u-mb10 {
	margin-bottom: 10px !important;
}
.u-min-height100 {
	min-height: 100px;
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
</style>
