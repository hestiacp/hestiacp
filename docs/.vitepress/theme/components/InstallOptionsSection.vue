<script lang="ts"></script>

<template>
	<form class="InstallForm" id="form">
		<div class="InstallOptionsSection">
			<slot name="list" />
		</div>
	</form>
</template>

<style scoped>
.InstallForm {
	margin: 0.55em 0;
	padding-left: 1em;
	list-style: disc;
	line-height: 1.5;
}

@media (min-width: 640px) {
	.InstallForm {
		font-size: 1.15rem;
	}
}
</style>
