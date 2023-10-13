---
layout: page
title: Install
---

<script setup>
  import PageHeader from "./.vitepress/theme/components/PageHeader.vue";
  import InstallScriptGenerator from "./.vitepress/theme/components/InstallScriptGenerator.vue";
  import { options } from "./_data/options";
</script>

<InstallPage>
  <PageHeader>
    <template #title>Install</template>
  </PageHeader>
  <InstallScriptGenerator :options="options"></InstallScriptGenerator>
</InstallPage>
