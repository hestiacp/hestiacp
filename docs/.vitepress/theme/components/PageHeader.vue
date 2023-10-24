<template>
	<header class="PageHeader">
		<div class="container">
			<h1>
				<slot name="title"></slot>
			</h1>
			<div v-if="$slots.aside">
				<slot name="aside" />
			</div>
		</div>
	</header>
</template>

<style scoped>
.container {
	display: flex;
	margin: 0 auto;
	align-items: center;
	justify-content: space-between;
	max-width: 1152px;
}
.PageHeader {
	padding: 0 24px;
	background-color: var(--vp-c-bg-alt);

	& h1 {
		margin: 0.75em 0;
		font-size: 2rem;
		font-weight: 700;
		line-height: inherit;
	}

	@media (min-width: 640px) {
		padding: 0 48px;

		& h1 {
			font-size: 2.5rem;
		}
	}

	@media (min-width: 960px) {
		padding: 0 64px;
	}
}
</style>
