<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import InputError from '@/components/InputError.vue'

const props = defineProps<{
  enabled: boolean
  pending: boolean
}>()

const qrCode = ref<string | null>(null)
const setupKey = ref<string | null>(null)
const recoveryCodes = ref<string[]>([])
const showingRecoveryCodes = ref(false)
const confirmingDisable = ref(false)

const enableForm = useForm({})
const confirmForm = useForm({ code: '' })
const disableForm = useForm({})

const enableTwoFactor = async () => {
  enableForm.post(route('two-factor.enable'), {
    preserveScroll: true,
    onSuccess: async () => {
      const res = await window.axios.get(route('two-factor.recovery-codes'))
      recoveryCodes.value = res.data.codes
      // Fetch QR code from page props or a dedicated endpoint if you expose one
    },
  })
}

const confirmTwoFactor = () => {
  confirmForm.post(route('two-factor.confirm'), {
    preserveScroll: true,
    onSuccess: () => { confirmForm.reset() },
  })
}

const disableTwoFactor = () => {
  disableForm.delete(route('two-factor.disable'), { preserveScroll: true })
}

const regenerateCodes = async () => {
  await window.axios.post(route('two-factor.recovery-codes.regenerate'))
  const res = await window.axios.get(route('two-factor.recovery-codes'))
  recoveryCodes.value = res.data.codes
}
</script>

<template>
  <Card>
    <CardHeader>
      <div class="flex items-center justify-between">
        <div>
          <CardTitle>Two Factor Authentication</CardTitle>
          <CardDescription>Add extra security to your account via TOTP.</CardDescription>
        </div>
        <Badge :variant="enabled ? 'default' : pending ? 'secondary' : 'outline'">
          {{ enabled ? 'Enabled' : pending ? 'Pending' : 'Disabled' }}
        </Badge>
      </div>
    </CardHeader>

    <CardContent class="space-y-4">
      <!-- Not yet started -->
      <template v-if="!enabled && !pending">
        <p class="text-muted-foreground text-sm">
          When enabled, you will be prompted for a secure, random token during authentication.
          You may retrieve this token from your phone's authenticator application (e.g. Google Authenticator).
        </p>
        <Button @click="enableTwoFactor" :disabled="enableForm.processing">Enable</Button>
      </template>

      <!-- Pending confirmation -->
      <template v-if="pending">
        <p class="text-muted-foreground text-sm">
          Finish enabling two-factor authentication by scanning the QR code using your authenticator app,
          then enter the generated code below.
        </p>

        <div class="space-y-2">
          <Label for="2fa-code">Authentication Code</Label>
          <Input id="2fa-code" v-model="confirmForm.code" type="text" inputmode="numeric" placeholder="000000" />
          <InputError :message="confirmForm.errors.code" />
        </div>

        <div class="flex gap-2">
          <Button @click="confirmTwoFactor" :disabled="confirmForm.processing">Confirm</Button>
          <Button variant="outline" @click="disableTwoFactor" :disabled="disableForm.processing">Cancel</Button>
        </div>
      </template>

      <!-- Enabled -->
      <template v-if="enabled">
        <p class="text-muted-foreground text-sm">
          Two-factor authentication is now active. Store your recovery codes in a secure password manager.
        </p>

        <div class="flex gap-2">
          <Button variant="outline" @click="showingRecoveryCodes = !showingRecoveryCodes">
            {{ showingRecoveryCodes ? 'Hide' : 'Show' }} Recovery Codes
          </Button>
          <Button variant="outline" @click="regenerateCodes">Regenerate Codes</Button>
          <Button variant="destructive" @click="disableTwoFactor" :disabled="disableForm.processing">Disable</Button>
        </div>

        <div v-if="showingRecoveryCodes && recoveryCodes.length" class="bg-muted rounded-md p-4">
          <ul class="grid grid-cols-2 gap-1 font-mono text-sm">
            <li v-for="code in recoveryCodes" :key="code">{{ code }}</li>
          </ul>
        </div>
      </template>
    </CardContent>
  </Card>
</template>
