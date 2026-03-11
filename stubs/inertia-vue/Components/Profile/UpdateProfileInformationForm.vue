<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import InputError from '@/components/InputError.vue'

const props = defineProps<{
  hasProfilePhoto: boolean
}>()

const page = usePage()
const user = page.props.auth.user as {
  name: string
  email: string
  profile_photo_url?: string
}

const photoInput = ref<HTMLInputElement | null>(null)
const photoPreview = ref<string | null>(null)

const form = useForm({
  _method: 'PUT',
  name: user.name,
  email: user.email,
  photo: null as File | null,
})

const selectNewPhoto = () => photoInput.value?.click()

const updatePhotoPreview = () => {
  const photo = photoInput.value?.files?.[0]
  if (!photo) return

  form.photo = photo
  const reader = new FileReader()
  reader.onload = (e) => { photoPreview.value = e.target?.result as string }
  reader.readAsDataURL(photo)
}

const submit = () => {
  form.post(route('user-profile-information.update'), {
    errorBag: 'updateProfileInformation',
    preserveScroll: true,
  })
}

const deletePhoto = () => {
  form.delete(route('current-user-photo.destroy'), {
    preserveScroll: true,
    onSuccess: () => { photoPreview.value = null },
  })
}
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Profile Information</CardTitle>
      <CardDescription>Update your name, email address, and profile photo.</CardDescription>
    </CardHeader>

    <CardContent>
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Profile Photo -->
        <div v-if="hasProfilePhoto" class="space-y-2">
          <Label>Profile Photo</Label>
          <div class="flex items-center gap-4">
            <img
              :src="photoPreview ?? user.profile_photo_url"
              :alt="user.name"
              class="h-16 w-16 rounded-full object-cover"
            />
            <div class="flex gap-2">
              <Button type="button" variant="outline" size="sm" @click="selectNewPhoto">
                Change Photo
              </Button>
              <Button
                v-if="user.profile_photo_url"
                type="button"
                variant="ghost"
                size="sm"
                @click="deletePhoto"
              >
                Remove
              </Button>
            </div>
            <input
              ref="photoInput"
              type="file"
              accept="image/*"
              class="hidden"
              @change="updatePhotoPreview"
            />
          </div>
          <InputError :message="form.errors.photo" />
        </div>

        <!-- Name -->
        <div class="space-y-2">
          <Label for="name">Name</Label>
          <Input id="name" v-model="form.name" type="text" autocomplete="name" />
          <InputError :message="form.errors.name" />
        </div>

        <!-- Email -->
        <div class="space-y-2">
          <Label for="email">Email</Label>
          <Input id="email" v-model="form.email" type="email" autocomplete="email" />
          <InputError :message="form.errors.email" />
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
