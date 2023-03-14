<template>
	<div class="InstallPage">
		<slot></slot>
	</div>
</template>

<style scoped>
.InstallPage {
	line-height: 1.5;
}

.InstallPage :deep(.container) {
	display: flex;
	flex-direction: column;
	margin: 0 auto;
	max-width: 1152px;
}

.InstallPage :deep(a) {
	font-weight: 500;
	color: var(--vp-c-brand);
	text-decoration-style: dotted;
	transition: color 0.25s;
}

.InstallPage :deep(a:hover) {
	color: var(--vp-c-brand-dark);
}
</style>
