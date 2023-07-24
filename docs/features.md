---
layout: page
title: Features
---

<script setup>
  import FeaturePageTitle from "./.vitepress/theme/components/FeaturePageTitle.vue";
  import FeaturePageSection from "./.vitepress/theme/components/FeaturePageSection.vue";
  import FeatureList from "./.vitepress/theme/components/FeatureList.vue";
  import { users, webDomains, mail, dns, databases, serverAdmin } from "./_data/features";
</script>

<FeaturePage>
  <FeaturePageTitle>
    <template #title>Features</template>
  </FeaturePageTitle>
  <FeaturePageSection image="/images/undraw_two_factor_authentication_namy.svg">
    <template #title>Users</template>
    <template #lead>Share access to your server with other users and restrict their resources.</template>
    <template #list>
      <FeatureList :items="users"></FeatureList>
    </template>
  </FeaturePageSection>
  <FeaturePageSection image="/images/undraw_web_developer_re_h7ie.svg">
    <template #title>Web domains</template>
    <template #lead>Add multiple domains and quickly install apps on them.</template>
    <template #list>
      <FeatureList :items="webDomains"></FeatureList>
    </template>
  </FeaturePageSection>
  <FeaturePageSection image="/images/undraw_domain_names_re_0uun.svg">
    <template #title>DNS</template>
    <template #lead>Manage your own DNS server!</template>
    <template #list>
      <FeatureList :items="dns"></FeatureList>
    </template>
  </FeaturePageSection>
  <FeaturePageSection image="/images/undraw_personal_email_re_4lx7.svg">
    <template #title>Mail</template>
    <template #lead>Host your own emails, no need to pay a business mail provider!</template>
    <template #list>
      <FeatureList :items="mail"></FeatureList>
    </template>
  </FeaturePageSection>
  <FeaturePageSection image="/images/undraw_maintenance_re_59vn.svg">
    <template #title>Databases</template>
    <template #lead>From e-commerce to blogs, databases are always useful and you can choose between MySQL and PostgreSQL.</template>
    <template #list>
      <FeatureList :items="databases"></FeatureList>
    </template>
  </FeaturePageSection>
  <FeaturePageSection image="/images/undraw_server_status_re_n8ln.svg">
    <template #title>Server admin</template>
    <template #lead>Ultra-configurable and user-friendly, Hestia is as powerful as you could want.</template>
    <template #list>
      <FeatureList :items="serverAdmin"></FeatureList>
    </template>
  </FeaturePageSection>
</FeaturePage>
