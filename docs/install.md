---
layout: page
title: Install
---

<script setup lang="ts">
  import InstallPageTitle from "./.vitepress/theme/components/InstallPageTitle.vue";
  import InstallOptions from "./.vitepress/theme/components/InstallOptions.vue";
  import InstallOptionsSection from "./.vitepress/theme/components/InstallOptionsSection.vue";
  import { options } from "./_data/options";
</script>

<InstallPage>
  <InstallPageTitle>
	<template #title>Install</template>
  </InstallPageTitle>
  <InstallOptionsSection>
  	<template #list>
	  <InstallOptions :items="options"></InstallOptions>
	</template>
  </InstallOptionsSection>
</InstallPage>
