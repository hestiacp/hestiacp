---
layout: page

title: The Team
---

<script setup>
  import { VPTeamPage, VPTeamPageTitle, VPTeamPageSection, VPTeamMembers } from "vitepress/theme";
  import { projectManagers, teamMembers } from "./_data/team";
</script>

<VPTeamPage>
  <VPTeamPageTitle>
    <template #title>开源团队介绍</template>
    <template #lead>
      Hestia 的开发由国际团队指导，选择性的在下面进行介绍。
    </template>
  </VPTeamPageTitle>
  <VPTeamPageSection>
    <template #title>项目经理</template>
    <template #members>
      <VPTeamMembers :members="projectManagers" />
    </template>
  </VPTeamPageSection>
  <VPTeamPageSection>
    <template #title>团队成员</template>
    <template #members>
      <VPTeamMembers :members="teamMembers" />
    </template>
  </VPTeamPageSection>
  <!-- <VPTeamPageSection>
    <template #title>Contributors ❤️</template>
    <template #members>
      <VPTeamMembers size="small" :members="featuredContributors" />
    </template>
  </VPTeamPageSection> -->
</VPTeamPage>
