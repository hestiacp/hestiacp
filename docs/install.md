---
layout: page
title: Install
---

<script setup>
  import PageHeader from "./.vitepress/theme/components/PageHeader.vue";
  import InstallBuilder from "./.vitepress/theme/components/InstallBuilder.vue";
  import { options } from "./_data/options";
</script>

<InstallPage>
  <PageHeader>
    <template #title>Install</template>
  </PageHeader>
  <InstallBuilder :options="options"></InstallBuilder>
</InstallPage>
