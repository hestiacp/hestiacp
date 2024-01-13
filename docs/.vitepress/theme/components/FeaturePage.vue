<template>
	<div class="FeaturePage">
		<slot></slot>
	</div>
</template>

<style scoped>
.FeaturePage {
	line-height: 1.5;
}

.FeaturePage :deep(.container) {
	display: flex;
	margin: 0 auto;
	max-width: 1152px;
}

.FeaturePage :deep(a) {
	font-weight: 500;
	color: var(--vp-c-brand);
	text-decoration-style: dotted;
	transition: color 0.25s;
}

.FeaturePage :deep(a:hover) {
	color: var(--vp-c-brand-dark);
}
</style>
