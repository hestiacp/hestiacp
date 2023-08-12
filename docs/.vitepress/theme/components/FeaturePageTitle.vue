<template>
	<header class="FeaturePageTitle">
		<div class="container">
			<h1>
				<slot name="title"></slot>
			</h1>
			<p v-if="$slots.lead" class="lead">
				<slot name="lead" />
			</p>
		</div>
	</header>
</template>

<style scoped>
.FeaturePageTitle {
	padding: 0 24px;
	background-color: var(--vp-c-bg-alt);
}

.FeaturePageTitle h1 {
	margin: 0.75em 0;
	font-size: 2rem;
	font-weight: 700;
	line-height: inherit;
}

@media (min-width: 640px) {
	.FeaturePageTitle {
		padding: 0 48px;
	}

	.FeaturePageTitle h1 {
		font-size: 2.5rem;
	}
}

@media (min-width: 960px) {
	.FeaturePageTitle {
		padding: 0 64px;
	}
}
</style>
