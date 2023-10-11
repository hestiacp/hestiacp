---
layout: page
title: Install
---

<script setup>
  import PageHeader from "./.vitepress/theme/components/PageHeader.vue";
  import InstallScriptGenerator from "./.vitepress/theme/components/InstallScriptGenerator.vue";
  import { options } from "./_data/options";
  import { languages } from "./_data/languages";
</script>

<InstallPage>
  <PageHeader>
    <template #title>Install</template>
  </PageHeader>
  <InstallScriptGenerator :options="options" :languages="languages"></InstallScriptGenerator>
</InstallPage>
