<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import {
  AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent,
  AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle,
} from '@/components/ui/alert-dialog'

const props = defineProps<{ team: { id: number; name: string } }>()
const open = ref(false)
const form = useForm({})

const submit = () => {
  form.delete(route('teams.destroy', props.team.id), {
    onSuccess: () => { open.value = false },
  })
}
</script>

<template>
  <Card class="border-destructive">
    <CardHeader>
      <CardTitle class="text-destructive">Delete Team</CardTitle>
      <CardDescription>Permanently delete this team. All team data will be removed.</CardDescription>
    </CardHeader>
    <CardContent>
      <Button variant="destructive" @click="open = true">Delete Team</Button>
    </CardContent>
  </Card>

  <AlertDialog v-model:open="open">
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>Are you sure?</AlertDialogTitle>
        <AlertDialogDescription>
          Once "{{ team.name }}" is deleted, all of its resources and data will be permanently removed.
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel>Cancel</AlertDialogCancel>
        <AlertDialogAction class="bg-destructive text-destructive-foreground" @click="submit" :disabled="form.processing">
          Delete Team
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
