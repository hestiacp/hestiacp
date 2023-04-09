<script lang="ts">
import { InstallOptions } from "../../../_data/options";
import { LanguagesOptions } from "../../../_data/languages";

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
			installstr: "",
		};
	},
	mounted() {},
	methods: {
		generateString() {
			let installstr = [];
			this.$props.items.forEach((value) => {
				let item = JSON.parse(JSON.stringify(value));
				if (item.textField && item.selected) {
					if (item.text.length >= 2) {
						installstr.push(item.param + " '" + item.text + "'");
					}
				} else if (item.selectField) {
					installstr.push(item.param + " '" + item.text + "'");
				} else if (!item.textField) {
					if (item.param.includes("force")) {
						item.selected ? installstr.push(item.param) : "";
					} else {
						installstr.push(item.param + (item.selected ? " yes" : " no"));
					}
				}
			});

			this.$data.installstr = this.$data.hestia_install + " " + installstr.join(" ");
		},
	},
};
</script>

<template>
	<div class="container">
		<div class="grid">
			<div class="form-group" v-for="item in items">
				<div class="u-mb10">
					<input type="checkbox" :value="item.value" v-model="item.selected" :id="item.id" />
					<label :for="item.id">{{ item.id }}</label>
				</div>
				<p>{{ item.desc }}</p>
				<div class="" v-if="item.textField">
					<input type="text" class="input-from" v-model="item.text" />
				</div>
				<div class="" v-if="item.selectField">
					<select class="input-from" v-model="item.text">
						<option v-for="language in languages" :value="language.value">
							{{ language.text }}
						</option>
					</select>
				</div>
			</div>
		</div>
		<div class="u-text-center u-mb10">
			<button @click="generateString" class="form-submit" type="button">Submit</button>
		</div>
		<div v-if="installstr">
			<div class="form-group-info">
				<h1>Installation instruction</h1>
				<p>
					Log in to your server as root, either directly or via SSH:
					<strong>ssh root@your.server</strong> and download the installation script:
				</p>
				<textarea v-model="hestia_wget" />
				<p>And run then the following command</p>
				<textarea v-model="installstr" />
			</div>
		</div>
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
.input-from {
	font-size: 1em;
	border: 1px solid;
	padding: 2px;
}
.form-submit {
	background-color: green;
	font-weight: bold;
	border-radius: 10px;
	padding: 10px 20px;
	font-size: 25px;

	&:hover {
		background-color: #4caf50;
	}
}
textarea {
	width: 100%;
	font-size: 1em;
}
.u-mb10 {
	margin-bottom: 10px !important;
}
.u-text-center {
	text-align: center !important;
}
</style>
