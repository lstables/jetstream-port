<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import InputError from '@/components/InputError.vue'

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.put(route('user-password.update'), {
    errorBag: 'updatePassword',
    preserveScroll: true,
    onSuccess: () => form.reset(),
  })
}
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Update Password</CardTitle>
      <CardDescription>Ensure your account is using a strong, unique password.</CardDescription>
    </CardHeader>

    <CardContent>
      <form @submit.prevent="submit" class="space-y-6">
        <div class="space-y-2">
          <Label for="current_password">Current Password</Label>
          <Input id="current_password" v-model="form.current_password" type="password" autocomplete="current-password" />
          <InputError :message="form.errors.current_password" />
        </div>

        <div class="space-y-2">
          <Label for="password">New Password</Label>
          <Input id="password" v-model="form.password" type="password" autocomplete="new-password" />
          <InputError :message="form.errors.password" />
        </div>

        <div class="space-y-2">
          <Label for="password_confirmation">Confirm Password</Label>
          <Input id="password_confirmation" v-model="form.password_confirmation" type="password" autocomplete="new-password" />
          <InputError :message="form.errors.password_confirmation" />
        </div>

        <div class="flex items-center gap-4">
          <Button type="submit" :disabled="form.processing">Update Password</Button>
          <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition ease-in-out">
            <p v-if="form.recentlySuccessful" class="text-sm text-green-600">Saved.</p>
          </Transition>
        </div>
      </form>
    </CardContent>
  </Card>
</template>
