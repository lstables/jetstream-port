<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
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
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog'
import InputError from '@/components/InputError.vue'

const open = ref(false)

const form = useForm({
  password: '',
})

const submit = () => {
  form.delete(route('current-user.destroy'), {
    preserveScroll: true,
    onSuccess: () => { open.value = false },
    onError: () => { form.reset('password') },
  })
}
</script>

<template>
  <Card class="border-destructive">
    <CardHeader>
      <CardTitle class="text-destructive">Delete Account</CardTitle>
      <CardDescription>Permanently delete your account and all associated data. This cannot be undone.</CardDescription>
    </CardHeader>

    <CardContent>
      <AlertDialog v-model:open="open">
        <AlertDialogTrigger as-child>
          <Button variant="destructive">Delete Account</Button>
        </AlertDialogTrigger>

        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
            <AlertDialogDescription>
              Once your account is deleted, all of its resources and data will be permanently removed.
              Please enter your password to confirm you would like to permanently delete your account.
            </AlertDialogDescription>
          </AlertDialogHeader>

          <div class="my-4 space-y-2">
            <Label for="delete-password">Password</Label>
            <Input
              id="delete-password"
              v-model="form.password"
              type="password"
              placeholder="Your password"
              @keyup.enter="submit"
            />
            <InputError :message="form.errors.password" />
          </div>

          <AlertDialogFooter>
            <AlertDialogCancel>Cancel</AlertDialogCancel>
            <Button variant="destructive" @click="submit" :disabled="form.processing">
              Delete Account
            </Button>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </CardContent>
  </Card>
</template>
