<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app-layout'
import CreateApiTokenForm from '@/components/TeamStream/API/CreateApiTokenForm.vue'
import ApiTokenList from '@/components/TeamStream/API/ApiTokenList.vue'

defineProps<{
  tokens: Array<{
    id: number
    name: string
    abilities: string[]
    last_used_ago: string | null
    created_at: string
  }>
  availablePermissions: string[]
  defaultPermissions: string[]
}>()
</script>

<template>
  <AppLayout>
    <Head title="API Tokens" />

    <div class="mx-auto max-w-3xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
      <div>
        <h1 class="text-2xl font-semibold tracking-tight">API Tokens</h1>
        <p class="text-muted-foreground text-sm">Manage API tokens that allow third-party services to access this application on your behalf.</p>
      </div>

      <CreateApiTokenForm
        :available-permissions="availablePermissions"
        :default-permissions="defaultPermissions"
      />

      <ApiTokenList
        v-if="tokens.length"
        :tokens="tokens"
        :available-permissions="availablePermissions"
      />
    </div>
  </AppLayout>
</template>
