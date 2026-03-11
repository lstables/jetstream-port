<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app-layout'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import InputError from '@/components/InputError.vue'

const form = useForm({ name: '' })

const submit = () => form.post(route('teams.store'))
</script>

<template>
  <AppLayout>
    <Head title="Create Team" />
    <div class="mx-auto max-w-lg px-4 py-8">
      <Card>
        <CardHeader>
          <CardTitle>Create Team</CardTitle>
          <CardDescription>Create a new team to collaborate with others.</CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="submit" class="space-y-4">
            <div class="space-y-2">
              <Label for="name">Team Name</Label>
              <Input id="name" v-model="form.name" type="text" placeholder="My Awesome Team" />
              <InputError :message="form.errors.name" />
            </div>
            <Button type="submit" :disabled="form.processing">Create Team</Button>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
