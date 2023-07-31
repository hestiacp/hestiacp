---
layout: page
title: Install
---

<script setup>
  import InstallPageTitle from "./.vitepress/theme/components/InstallPageTitle.vue";
  import InstallOptions from "./.vitepress/theme/components/InstallOptions.vue";
  import InstallOptionsSection from "./.vitepress/theme/components/InstallOptionsSection.vue";
  import { options } from "./_data/options";
  import { languages } from "./_data/languages";
</script>

<InstallPage>
  <InstallPageTitle>
	<template #title>Install</template>
  </InstallPageTitle>
  <InstallOptionsSection>
  	<template #list>
	  <InstallOptions :items="options" :languages="languages"></InstallOptions>
	</template>
  </InstallOptionsSection>
</InstallPage>
