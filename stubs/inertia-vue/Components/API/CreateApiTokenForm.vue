<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Checkbox } from '@/components/ui/checkbox'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import InputError from '@/components/InputError.vue'

const props = defineProps<{
  availablePermissions: string[]
  defaultPermissions: string[]
}>()

const page = usePage()
const displayToken = ref('')
const tokenDialogOpen = ref(false)

const form = useForm({
  name: '',
  permissions: [...props.defaultPermissions],
})

watch(() => page.props.flash, (flash: any) => {
  if (flash?.token) {
    displayToken.value = flash.token
    tokenDialogOpen.value = true
    form.reset()
  }
}, { deep: true })

const submit = () => {
  form.post(route('api-tokens.store'), { preserveScroll: true })
}

const togglePermission = (permission: string) => {
  const idx = form.permissions.indexOf(permission)
  if (idx === -1) {
    form.permissions.push(permission)
  } else {
    form.permissions.splice(idx, 1)
  }
}
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Create API Token</CardTitle>
      <CardDescription>API tokens allow third-party services to authenticate with this application.</CardDescription>
    </CardHeader>

    <CardContent>
      <form @submit.prevent="submit" class="space-y-6">
        <div class="space-y-2">
          <Label for="token-name">Token Name</Label>
          <Input id="token-name" v-model="form.name" type="text" placeholder="My App Token" />
          <InputError :message="form.errors.name" />
        </div>

        <div v-if="availablePermissions.length" class="space-y-2">
          <Label>Permissions</Label>
          <div class="grid grid-cols-2 gap-2">
            <div
              v-for="permission in availablePermissions"
              :key="permission"
              class="flex items-center space-x-2"
            >
              <Checkbox
                :id="permission"
                :checked="form.permissions.includes(permission)"
                @update:checked="togglePermission(permission)"
              />
              <Label :for="permission" class="font-normal capitalize cursor-pointer">{{ permission }}</Label>
            </div>
          </div>
          <InputError :message="form.errors.permissions" />
        </div>

        <Button type="submit" :disabled="form.processing">Create Token</Button>
      </form>
    </CardContent>
  </Card>

  <!-- Token display dialog -->
  <Dialog v-model:open="tokenDialogOpen">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>API Token Created</DialogTitle>
        <DialogDescription>
          Copy your new API token now. For your security, it will not be shown again.
        </DialogDescription>
      </DialogHeader>

      <div class="bg-muted my-4 rounded-md p-4">
        <p class="break-all font-mono text-sm select-all">{{ displayToken }}</p>
      </div>

      <Button @click="tokenDialogOpen = false">Close</Button>
    </DialogContent>
  </Dialog>
</template>
