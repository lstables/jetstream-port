import { Head } from '@inertiajs/react'
import AppLayout from '@/layouts/app-layout'
import TeamNameForm from '@/components/LaravelStream/Teams/TeamNameForm'
import TeamMemberManager from '@/components/LaravelStream/Teams/TeamMemberManager'
import DeleteTeamForm from '@/components/LaravelStream/Teams/DeleteTeamForm'

interface Member {
  id: number; name: string; email: string
  profile_photo_url?: string
  membership: { role: string | null }
}

interface Role { key: string; name: string; description: string; permissions: string[] }

interface Props {
  team: {
    id: number; name: string; personal_team: boolean
    owner: { id: number; name: string; email: string }
    users: Member[]
    team_invitations?: Array<{ id: number; email: string; role: string | null }>
  }
  availableRoles: Role[]
  userPermissions: {
    canAddTeamMembers: boolean; canDeleteTeam: boolean
    canRemoveTeamMembers: boolean; canUpdateTeam: boolean
  }
}

export default function Show({ team, availableRoles, userPermissions }: Props) {
  return (
    <AppLayout>
      <Head title={`Team: ${team.name}`} />

      <div className="mx-auto max-w-3xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div>
          <h1 className="text-2xl font-semibold tracking-tight">{team.name}</h1>
          <p className="text-muted-foreground text-sm">Manage your team settings and members.</p>
        </div>

        {userPermissions.canUpdateTeam && <TeamNameForm team={team} />}

        <TeamMemberManager team={team} availableRoles={availableRoles} userPermissions={userPermissions} />

        {!team.personal_team && userPermissions.canDeleteTeam && <DeleteTeamForm team={team} />}
      </div>
    </AppLayout>
  )
}
