---
layout: page
title: Install
---

<script setup>
  import PageHeader from "./.vitepress/theme/components/PageHeader.vue";
  import InstallOptions from "./.vitepress/theme/components/InstallOptions.vue";
  import InstallOptionsSection from "./.vitepress/theme/components/InstallOptionsSection.vue";
  import { options } from "./_data/options";
  import { languages } from "./_data/languages";
</script>

<InstallPage>
  <PageHeader>
    <template #title>Install</template>
  </PageHeader>
  <InstallOptionsSection>
    <template #list>
      <InstallOptions :items="options" :languages="languages"></InstallOptions>
    </template>
  </InstallOptionsSection>
</InstallPage>
