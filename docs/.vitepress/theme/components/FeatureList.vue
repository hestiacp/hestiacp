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
		<template v-for="item in items" :key="item.id">
			<li>
				<span v-html="item.text"></span>
				<FeatureList v-if="item.items" :items="item.items" />
			</li>
		</template>
	</ul>
</template>

<style scoped>
.FeatureList {
	margin: 0.55em 0;
	padding-left: 1em;
	list-style: disc;
	line-height: 1.5;

	& ul {
		padding-left: 1em;
		list-style: disc;
	}

	& li {
		margin-top: 0.5em;
	}

	@media (min-width: 640px) {
		font-size: 1.15rem;
	}
}
</style>
