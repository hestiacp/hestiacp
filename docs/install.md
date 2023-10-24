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
    <template #aside>
      <a class="header-button" href="./docs/introduction/getting-started.html#requirements">
        View requirements
      </a>
    </template>
  </PageHeader>
  <InstallBuilder :options="options"></InstallBuilder>
</InstallPage>

<style>
.header-button {
  display: inline-block;
  border: 1px solid transparent;
  font-weight: 600;
  transition: color 0.25s, border-color 0.25s, background-color 0.25s;
  border-radius: 20px;
  padding: 0 20px;
  line-height: 38px;
  font-size: 14px;
  border-color: var(--vp-button-alt-border);
  color: var(--vp-button-alt-text);
  background-color: var(--vp-button-alt-bg);

  &:hover {
    border-color: var(--vp-button-alt-hover-border);
    color: var(--vp-button-alt-hover-text);
    background-color: var(--vp-button-alt-hover-bg);
  }
}
</style>
