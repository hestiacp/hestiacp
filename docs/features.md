---
layout: page
title: Features
---

<script setup>
  import PageHeader from "./.vitepress/theme/components/PageHeader.vue";
  import FeaturePageSection from "./.vitepress/theme/components/FeaturePageSection.vue";
  import FeatureList from "./.vitepress/theme/components/FeatureList.vue";
  import { users, webDomains, mail, dns, databases, serverAdmin } from "./_data/features";
</script>

<FeaturePage>
  <PageHeader>
    <template #title>面板特点介绍</template>
  </PageHeader>
  <FeaturePageSection image="/images/undraw_two_factor_authentication_namy.svg">
    <template #title>用户</template>
    <template #lead>与其他用户共享对您服务器的访问权限并限制他们的资源。</template>
    <template #list>
      <FeatureList :items="users"></FeatureList>
    </template>
  </FeaturePageSection>
  <FeaturePageSection image="/images/undraw_web_developer_re_h7ie.svg">
    <template #title>Web 网站部署系统</template>
    <template #lead>添加多个域并在其上快速安装应用程序。</template>
    <template #list>
      <FeatureList :items="webDomains"></FeatureList>
    </template>
  </FeaturePageSection>
  <FeaturePageSection image="/images/undraw_domain_names_re_0uun.svg">
    <template #title>DNS集群 & DNS安全扩展</template>
    <template #lead>随着版本 1.7.0 的发布，我们实现了对 DNSSEC 的支持。DNSSEC 需要主从>设置。如果现有实现是主<>主设置，则不支持。DNSSEC 还需要至少 Ubuntu 22.04 或 Debian 11！</template>
    <template #list>
      <FeatureList :items="dns"></FeatureList>
    </template>
  </FeaturePageSection>
  <FeaturePageSection image="/images/undraw_personal_email_re_4lx7.svg">
    <template #title>自建服务器邮件系统</template>
    <template #lead>托管您自己的电子邮件，无需向商业邮件提供商付费！</template>
    <template #list>
      <FeatureList :items="mail"></FeatureList>
    </template>
  </FeaturePageSection>
  <FeaturePageSection image="/images/undraw_maintenance_re_59vn.svg">
    <template #title>数据库集群设置 & 数据库集群管理</template>
    <template #lead>从电子商务到博客，数据库总是有用的，您可以在 MySQL 和 PostgreSQL 之间进行选择。</template>
    <template #list>
      <FeatureList :items="databases"></FeatureList>
    </template>
  </FeaturePageSection>
  <FeaturePageSection image="/images/undraw_server_status_re_n8ln.svg">
    <template #title>服务器管理员</template>
    <template #lead>Hestia 具有超可配置性和用户友好性，功能强大，如您所愿。</template>
    <template #list>
      <FeatureList :items="serverAdmin"></FeatureList>
    </template>
  </FeaturePageSection>
</FeaturePage>
