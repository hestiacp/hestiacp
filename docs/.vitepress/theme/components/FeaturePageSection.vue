<script>
export default {
	props: ["image"],
};
</script>

<template>
	<div class="FeaturePageSection">
		<div class="container">
			<figure v-if="image">
				<img :src="image" alt="" />
			</figure>
			<div>
				<h2>
					<slot name="title" />
				</h2>
				<p v-if="$slots.lead" class="lead">
					<slot name="lead" />
				</p>
				<slot name="list" />
			</div>
		</div>
	</div>
</template>

<style scoped>
.FeaturePageSection {
	margin: 0 0 0.5em;
	padding: 0 24px;
}

.FeaturePageTitle + .FeaturePageSection {
	padding: 48px 24px 0;
}

.FeaturePageSection:last-child {
	padding: 0 24px 48px;
}

.FeaturePageSection + .FeaturePageSection {
	margin: 2em 0 0.5em;
}

.FeaturePageSection h2 {
	margin: 0 0 0.5em;
	font-size: 1.5rem;
	font-weight: 700;
	line-height: inherit;
}

.FeaturePageSection .lead {
	margin: 0.5em 0;
	font-style: italic;
}

@media (min-width: 640px) {
	.FeaturePageSection {
		padding: 0 48px;
	}

	.FeaturePageTitle + .FeaturePageSection {
		padding: 48px 48px 0;
	}

	.FeaturePageSection:last-child {
		padding: 0 48px 48px;
	}

	.FeaturePageSection + .FeaturePageSection {
		margin: 4em 0 0.5em;
	}

	.FeaturePageSection h2 {
		font-size: 1.85rem;
	}

	.FeaturePageSection .lead {
		font-size: 1.15rem;
	}
}

@media (min-width: 960px) {
	.FeaturePageSection {
		padding: 0 64px;
	}

	.FeaturePageTitle + .FeaturePageSection {
		padding: 64px 64px 0;
	}

	.FeaturePageSection:last-child {
		padding: 0 64px 64px;
	}
}

.FeaturePageSection .container {
	flex-direction: column;
	gap: 2rem;
}

@media (min-width: 640px) {
	.FeaturePageSection .container {
		flex-direction: row;
		align-items: flex-start;
	}

	.FeaturePageSection:nth-child(2n + 1) .container {
		flex-direction: row-reverse;
	}

	.FeaturePageSection .container > * {
		width: 50%;
	}
}

figure {
	padding: 2rem;
	border-radius: 8px;
	background-color: var(--vp-c-bg-alt);
}
</style>
