<script>
export default {
	props: {
		items: {
			required: true,
		},
	},
};
</script>

<template>
	<ul class="FeatureList">
		<li v-for="item in items">
			<span v-html="item.text"></span>
			<ul v-if="item.items">
				<li v-for="nested in item.items">
					<span v-html="nested.text"></span>
				</li>
			</ul>
		</li>
	</ul>
</template>

<style scoped>
.FeatureList {
	margin: 0.55em 0;
	padding-left: 1em;
	list-style: disc;
	line-height: 1.5;
}

.FeatureList ul {
	padding-left: 1em;
	list-style: disc;
}

.FeatureList li {
	margin-top: 0.5em;
}

@media (min-width: 640px) {
	.FeatureList {
		font-size: 1.15rem;
	}
}
</style>
