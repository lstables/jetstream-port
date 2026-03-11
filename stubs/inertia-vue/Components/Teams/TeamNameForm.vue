<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import InputError from '@/components/InputError.vue'

const props = defineProps<{ team: { id: number; name: string } }>()

const form = useForm({ name: props.team.name })

const submit = () => {
  form.put(route('teams.update', props.team.id), {
    errorBag: 'updateTeamName',
    preserveScroll: true,
  })
}
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Team Name</CardTitle>
      <CardDescription>The team's name and owner information.</CardDescription>
    </CardHeader>
    <CardContent>
      <form @submit.prevent="submit" class="space-y-4">
        <div class="space-y-2">
          <Label for="team-name">Team Name</Label>
          <Input id="team-name" v-model="form.name" type="text" />
          <InputError :message="form.errors.name" />
        </div>
        <div class="flex items-center gap-4">
          <Button type="submit" :disabled="form.processing">Save</Button>
          <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition ease-in-out">
            <p v-if="form.recentlySuccessful" class="text-sm text-green-600">Saved.</p>
          </Transition>
        </div>
      </form>
    </CardContent>
  </Card>
</template>
