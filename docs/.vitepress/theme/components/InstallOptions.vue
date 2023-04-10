<script lang="ts">
import { InstallOptions } from "../../../_data/options";
import { LanguagesOptions } from "../../../_data/languages";
import { ref } from "vue";
const slot = ref(null);

export default {
	props: {
		languages: {
			type: Array<LanguagesOptions>,
			required: true,
			selected: "en",
		},
		items: {
			type: Array<InstallOptions>,
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
		getOptionString(item: InstallOptions): string {
			if (item.textField && item.selected) {
				return item.text.length >= 2 ? `${item.param} '${item.text}'` : "";
			} else if (item.selectField) {
				return `${item.param} '${item.text}'`;
			} else if (!item.textField) {
				return item.param.includes("force") && item.selected
					? item.param
					: `${item.param}${item.selected ? " yes" : " no"}`;
			}
			return "";
		},
		generateString() {
			const installStr = this.items.map(this.getOptionString).filter(Boolean);

			this.installStr = `${this.hestia_install} ${installStr.join(" ")}`;
			(this.$refs.dialog as HTMLDialogElement).showModal();
		},
		closeDialog(e) {
			if (e.target === this.$refs.dialog) {
				(this.$refs.dialog as HTMLDialogElement).close();
			}
		},
		checkDependencies(e) {
			if (e.target.checked) {
				let conflicts = e.target.getAttribute("conflicts");
				if (conflicts) {
					document.getElementById(conflicts).checked = false;
				}
				let depends = e.target.getAttribute("depends");
				if (depends) {
					document.getElementById(depends).checked = true;
				}
			}
		},
		enableOption(e) {
			let checked = e.target.getElementsByTagName("input")[0];
			if (checked) {
				if (checked.checked) {
					checked.checked = false;
				} else {
					checked.checked = true;
					var event = new Event("change");
					checked.dispatchEvent(event);
				}
			}
		},
	},
};
</script>

<template>
	<div class="container">
		<div class="grid">
			<div class="form-group" v-for="item in items" @click="enableOption">
				<div class="u-mb10">
					<input
						@change="checkDependencies"
						type="checkbox"
						:value="item.value"
						v-model="item.selected"
						:id="item.id"
						:conflicts="item.conflicts"
						:depends="item.depends"
					/>
					<label :for="item.id">{{ item.id }}</label>
				</div>
				<p>{{ item.desc }}</p>
				<div v-if="item.textField">
					<input type="text" class="input-from" v-model="item.text" />
				</div>
				<div v-if="item.selectField">
					<select class="input-from" v-model="item.text">
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
			<div class="form-group-info">
				<h1>Installation instruction</h1>
				<p>
					Log in to your server as root, either directly or via SSH:
					<strong>ssh root@your.server</strong> and download the installation script:
				</p>
				<textarea v-model="hestia_wget" readonly />
				<p>And run then the following command</p>
				<textarea v-model="installStr" readonly />
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

	@media (min-width: 640px) {
		grid-template-columns: 1fr 1fr;
	}

	@media (min-width: 960px) {
		grid-template-columns: 1fr 1fr 1fr;
	}
}
.form-group {
	border-radius: 10px;
	padding: 20px;
	background-color: var(--vp-c-bg-alt);
}
.form-group-info {
	clear: both;
	margin: 1em 2em 1em 2em;
	padding: 10px;
	border: 1px solid;
}
label {
	margin-left: 2px;
	text-transform: capitalize;
}
.input-from {
	font-size: 1em;
	border: 1px solid;
	padding: 2px;
}
.form-submit {
	background-color: green;
	font-weight: bold;
	border-radius: 10px;
	padding: 10px 30px;
	font-size: 25px;

	&:hover {
		background-color: #4caf50;
	}
}
textarea {
	width: 100%;
	font-size: 1em;
}
.modal {
	position: fixed;
	padding: 0;

	&::backdrop {
		background-color: rgb(0 0 0 / 60%);
	}
}
.u-mb10 {
	margin-bottom: 10px !important;
}
.u-text-center {
	text-align: center !important;
}
</style>
