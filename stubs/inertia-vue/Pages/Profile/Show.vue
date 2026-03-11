<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app-layout'
import UpdateProfileInformationForm from '@/components/TeamStream/Profile/UpdateProfileInformationForm.vue'
import UpdatePasswordForm from '@/components/TeamStream/Profile/UpdatePasswordForm.vue'
import TwoFactorAuthenticationForm from '@/components/TeamStream/Profile/TwoFactorAuthenticationForm.vue'
import DeleteUserForm from '@/components/TeamStream/Profile/DeleteUserForm.vue'

defineProps<{
  sessions: object[]
  hasProfilePhoto: boolean
  hasTwoFactor: boolean
  hasAccountDeletion: boolean
  twoFactorEnabled: boolean
  twoFactorPending: boolean
}>()
</script>

<template>
  <AppLayout>
    <Head title="Profile" />

    <div class="mx-auto max-w-3xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
      <div>
        <h1 class="text-2xl font-semibold tracking-tight">Profile</h1>
        <p class="text-muted-foreground text-sm">Manage your account settings and profile information.</p>
      </div>

      <UpdateProfileInformationForm :has-profile-photo="hasProfilePhoto" />

      <UpdatePasswordForm />

      <TwoFactorAuthenticationForm
        v-if="hasTwoFactor"
        :enabled="twoFactorEnabled"
        :pending="twoFactorPending"
      />

      <DeleteUserForm v-if="hasAccountDeletion" />
    </div>
  </AppLayout>
</template>
