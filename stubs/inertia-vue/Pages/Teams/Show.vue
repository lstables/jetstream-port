<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app-layout'
import TeamNameForm from '@/components/TeamStream/Teams/TeamNameForm.vue'
import TeamMemberManager from '@/components/TeamStream/Teams/TeamMemberManager.vue'
import DeleteTeamForm from '@/components/TeamStream/Teams/DeleteTeamForm.vue'

interface TeamMember {
  id: number
  name: string
  email: string
  profile_photo_url?: string
  membership: { role: string | null }
}

interface Team {
  id: number
  name: string
  personal_team: boolean
  owner: { id: number; name: string; email: string }
  users: TeamMember[]
  team_invitations?: Array<{ id: number; email: string; role: string | null }>
}

interface Role {
  key: string
  name: string
  description: string
  permissions: string[]
}

const props = defineProps<{
  team: Team
  availableRoles: Role[]
  userPermissions: {
    canAddTeamMembers: boolean
    canDeleteTeam: boolean
    canRemoveTeamMembers: boolean
    canUpdateTeam: boolean
  }
}>()
</script>

<template>
  <AppLayout>
    <Head :title="`Team: ${team.name}`" />

    <div class="mx-auto max-w-3xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
      <div>
        <h1 class="text-2xl font-semibold tracking-tight">{{ team.name }}</h1>
        <p class="text-muted-foreground text-sm">Manage your team settings and members.</p>
      </div>

      <TeamNameForm
        v-if="userPermissions.canUpdateTeam"
        :team="team"
      />

      <TeamMemberManager
        :team="team"
        :available-roles="availableRoles"
        :user-permissions="userPermissions"
      />

      <DeleteTeamForm
        v-if="!team.personal_team && userPermissions.canDeleteTeam"
        :team="team"
      />
    </div>
  </AppLayout>
</template>
