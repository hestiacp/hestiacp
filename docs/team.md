---
layout: page

title: The Team
---

<script setup lang="ts">
  import { VPTeamPage, VPTeamPageTitle, VPTeamPageSection, VPTeamMembers } from "vitepress/theme";
  import { projectManagers, teamMembers, emeriti } from "./_data/team";
</script>

<VPTeamPage>
  <VPTeamPageTitle>
    <template #title>The Team</template>
    <template #lead>
      The development of Hestia is guided by an international team, some of whom have chosen to be featured below.
    </template>
  </VPTeamPageTitle>
  <VPTeamPageSection>
    <template #title>Project Managers</template>
    <template #members>
      <VPTeamMembers :members="projectManagers" />
    </template>
  </VPTeamPageSection>
  <VPTeamPageSection>
    <template #title>Team Members</template>
    <template #members>
      <VPTeamMembers :members="teamMembers" />
    </template>
  </VPTeamPageSection>
  <VPTeamPageSection>
    <template #title>Team Emeriti</template>
    <template #lead>
      Here we honor some no-longer-active team members who have made valuable contributions in the past.
    </template>
    <template #members>
      <VPTeamMembers :members="emeriti" />
    </template>
  </VPTeamPageSection>
  <!-- <VPTeamPageSection>
    <template #title>Contributors ❤️</template>
    <template #members>
      <VPTeamMembers size="small" :members="featuredContributors" />
    </template>
  </VPTeamPageSection> -->
</VPTeamPage>
