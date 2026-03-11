<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Checkbox } from '@/components/ui/checkbox'
import { Label } from '@/components/ui/label'
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
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
} from '@/components/ui/sheet'

interface Token {
  id: number
  name: string
  abilities: string[]
  last_used_ago: string | null
  created_at: string
}

const props = defineProps<{
  tokens: Token[]
  availablePermissions: string[]
}>()

const deleteTarget = ref<Token | null>(null)
const editTarget = ref<Token | null>(null)
const editPermissions = ref<string[]>([])

const deleteForm = useForm({})
const editForm = useForm({ permissions: [] as string[] })

const openEdit = (token: Token) => {
  editTarget.value = token
  editPermissions.value = [...token.abilities]
  editForm.permissions = [...token.abilities]
}

const saveEdit = () => {
  editForm.permissions = editPermissions.value
  editForm.put(route('api-tokens.update', editTarget.value!.id), {
    preserveScroll: true,
    onSuccess: () => { editTarget.value = null },
  })
}

const deleteToken = () => {
  if (!deleteTarget.value) return
  deleteForm.delete(route('api-tokens.destroy', deleteTarget.value.id), {
    preserveScroll: true,
    onSuccess: () => { deleteTarget.value = null },
  })
}

const toggleEditPermission = (permission: string) => {
  const idx = editPermissions.value.indexOf(permission)
  if (idx === -1) editPermissions.value.push(permission)
  else editPermissions.value.splice(idx, 1)
}
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Manage Tokens</CardTitle>
    </CardHeader>

    <CardContent class="divide-y">
      <div
        v-for="token in tokens"
        :key="token.id"
        class="flex items-center justify-between py-4"
      >
        <div>
          <p class="font-medium">{{ token.name }}</p>
          <p class="text-muted-foreground text-sm">
            {{ token.last_used_ago ? `Last used ${token.last_used_ago}` : 'Never used' }}
          </p>
        </div>

        <div class="flex gap-2">
          <Button v-if="availablePermissions.length" variant="outline" size="sm" @click="openEdit(token)">
            Permissions
          </Button>
          <Button variant="ghost" size="sm" class="text-destructive" @click="deleteTarget = token">
            Delete
          </Button>
        </div>
      </div>
    </CardContent>
  </Card>

  <!-- Edit permissions sheet -->
  <Sheet :open="!!editTarget" @update:open="(v) => !v && (editTarget = null)">
    <SheetContent>
      <SheetHeader>
        <SheetTitle>Token Permissions</SheetTitle>
        <SheetDescription>Update permissions for "{{ editTarget?.name }}"</SheetDescription>
      </SheetHeader>

      <div class="my-6 space-y-3">
        <div
          v-for="permission in availablePermissions"
          :key="permission"
          class="flex items-center space-x-2"
        >
          <Checkbox
            :id="`edit-${permission}`"
            :checked="editPermissions.includes(permission)"
            @update:checked="toggleEditPermission(permission)"
          />
          <Label :for="`edit-${permission}`" class="font-normal capitalize cursor-pointer">{{ permission }}</Label>
        </div>
      </div>

      <Button @click="saveEdit" :disabled="editForm.processing">Save</Button>
    </SheetContent>
  </Sheet>

  <!-- Delete confirmation -->
  <AlertDialog :open="!!deleteTarget" @update:open="(v) => !v && (deleteTarget = null)">
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>Delete Token</AlertDialogTitle>
        <AlertDialogDescription>
          Are you sure you want to delete the "{{ deleteTarget?.name }}" token? This action cannot be undone.
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel @click="deleteTarget = null">Cancel</AlertDialogCancel>
        <AlertDialogAction class="bg-destructive text-destructive-foreground" @click="deleteToken">
          Delete
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
