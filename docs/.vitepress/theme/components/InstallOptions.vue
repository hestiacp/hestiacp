<script lang="ts">
import { InstallOptions } from "../../../_data/options";

export default {
	props: {
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
	<div class="form-group" v-for="item in items">
		<input type="checkbox" :value="item.value" v-model="item.selected" /><label>{{
			item.id
		}}</label>
		<p>{{ item.desc }}</p>
		<div class="" v-if="item.textField">
			<input type="text" class="input-from" v-model="item.text" />
		</div>
	</div>
	<div class="form-group">
		<input value="Submit" @click="generateString" />
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
</template>

<style scoped>
.form-group {
	margin: 1em 2em 1em 2em;
	padding: 10px;
	border: 1px solid;
	width: 15em;
	float: left;
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
textarea {
	width: 100%;
	font-size: 1em;
}
.form-group p {
	margin: 0em 0em 1em 0em;
}
</style>
