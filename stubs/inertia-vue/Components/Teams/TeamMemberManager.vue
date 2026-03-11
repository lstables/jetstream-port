<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import InputError from '@/components/InputError.vue'

interface Member {
  id: number
  name: string
  email: string
  profile_photo_url?: string
  membership: { role: string | null }
}

interface Role { key: string; name: string; description: string }

const props = defineProps<{
  team: {
    id: number
    owner: { id: number; name: string; email: string }
    users: Member[]
    team_invitations?: Array<{ id: number; email: string; role: string | null }>
  }
  availableRoles: Role[]
  userPermissions: {
    canAddTeamMembers: boolean
    canRemoveTeamMembers: boolean
  }
}>()

const page = usePage()
const currentUser = page.props.auth.user as { id: number }

const addForm = useForm({ email: '', role: '' })
const removeTarget = ref<Member | null>(null)
const removeForm = useForm({})

const addMember = () => {
  addForm.post(route('team-members.store', props.team.id), {
    errorBag: 'addTeamMember',
    preserveScroll: true,
    onSuccess: () => addForm.reset(),
  })
}

const removeMember = () => {
  if (!removeTarget.value) return
  removeForm.delete(route('team-members.destroy', [props.team.id, removeTarget.value.id]), {
    errorBag: 'removeTeamMember',
    preserveScroll: true,
    onSuccess: () => { removeTarget.value = null },
  })
}

const cancelInvitation = (id: number) => {
  useForm({}).delete(route('team-invitations.destroy', id), { preserveScroll: true })
}

const initials = (name: string) =>
  name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Team Members</CardTitle>
      <CardDescription>Manage who has access to this team.</CardDescription>
    </CardHeader>

    <CardContent class="space-y-6">
      <!-- Add member form -->
      <form v-if="userPermissions.canAddTeamMembers" @submit.prevent="addMember" class="space-y-4">
        <h3 class="font-medium">Add Team Member</h3>

        <div class="space-y-2">
          <Label for="add-email">Email</Label>
          <Input id="add-email" v-model="addForm.email" type="email" placeholder="member@example.com" />
          <InputError :message="addForm.errors.email" />
        </div>

        <div v-if="availableRoles.length" class="space-y-2">
          <Label for="add-role">Role</Label>
          <Select v-model="addForm.role">
            <SelectTrigger>
              <SelectValue placeholder="Select a role" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="role in availableRoles" :key="role.key" :value="role.key">
                {{ role.name }}
              </SelectItem>
            </SelectContent>
          </Select>
          <InputError :message="addForm.errors.role" />
        </div>

        <Button type="submit" :disabled="addForm.processing">Add Member</Button>
      </form>

      <!-- Pending invitations -->
      <div v-if="team.team_invitations?.length" class="space-y-2">
        <h3 class="font-medium">Pending Invitations</h3>
        <div v-for="invite in team.team_invitations" :key="invite.id" class="flex items-center justify-between py-2">
          <span class="text-sm text-muted-foreground">{{ invite.email }}</span>
          <Button variant="ghost" size="sm" class="text-destructive" @click="cancelInvitation(invite.id)">
            Cancel
          </Button>
        </div>
      </div>

      <!-- Current members -->
      <div class="space-y-2">
        <h3 class="font-medium">Members</h3>

        <!-- Owner -->
        <div class="flex items-center justify-between py-2">
          <div class="flex items-center gap-3">
            <Avatar class="h-9 w-9">
              <AvatarFallback>{{ initials(team.owner.name) }}</AvatarFallback>
            </Avatar>
            <div>
              <p class="text-sm font-medium">{{ team.owner.name }}</p>
              <p class="text-muted-foreground text-xs">{{ team.owner.email }}</p>
            </div>
          </div>
          <Badge variant="secondary">Owner</Badge>
        </div>

        <!-- Other members -->
        <div
          v-for="member in team.users"
          :key="member.id"
          class="flex items-center justify-between py-2"
        >
          <div class="flex items-center gap-3">
            <Avatar class="h-9 w-9">
              <AvatarImage v-if="member.profile_photo_url" :src="member.profile_photo_url" />
              <AvatarFallback>{{ initials(member.name) }}</AvatarFallback>
            </Avatar>
            <div>
              <p class="text-sm font-medium">{{ member.name }}</p>
              <p class="text-muted-foreground text-xs">{{ member.email }}</p>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <Badge v-if="member.membership.role" variant="outline" class="capitalize">
              {{ member.membership.role }}
            </Badge>
            <Button
              v-if="userPermissions.canRemoveTeamMembers || currentUser.id === member.id"
              variant="ghost"
              size="sm"
              class="text-destructive"
              @click="removeTarget = member"
            >
              Remove
            </Button>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>

  <!-- Remove confirmation -->
  <AlertDialog :open="!!removeTarget" @update:open="(v) => !v && (removeTarget = null)">
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>Remove Team Member</AlertDialogTitle>
        <AlertDialogDescription>
          Are you sure you want to remove {{ removeTarget?.name }} from the team?
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel @click="removeTarget = null">Cancel</AlertDialogCancel>
        <AlertDialogAction class="bg-destructive text-destructive-foreground" @click="removeMember">
          Remove
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
