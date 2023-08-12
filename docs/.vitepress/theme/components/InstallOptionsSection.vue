<template>
	<form class="InstallForm" id="form">
		<div class="InstallOptionsSection">
			<slot name="list" />
		</div>
		<cite
			>Based on: <a href="https://github.com/gabizz/hestiacp-scriptline-generator">@gabizz</a> and
			<a href="https://github.com/turbopixel/HestiaCP-Command-Creator">@turbopixel</a></cite
		>
	</form>
</template>

<style scoped>
.InstallForm {
	margin: 0.55em 0;
	padding: 0 1em;
	line-height: 1.5;
}
cite {
	font-size: small;
	margin: 0.55em 0;
	display: block;
	text-align: center;

	& a {
		color: var(--vp-c-txt-1) !important;
	}
}
</style>
