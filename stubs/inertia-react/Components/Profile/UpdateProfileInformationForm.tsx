import { useForm, usePage } from '@inertiajs/react'
import { ChangeEvent, useRef, useState } from 'react'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import InputError from '@/components/InputError'

interface Props {
  hasProfilePhoto: boolean
}

type ProfileForm = {
  _method: string
  name: string
  email: string
  photo: File | null
}

export default function UpdateProfileInformationForm({ hasProfilePhoto }: Props) {
  const { auth } = usePage<{ auth: { user: { name: string; email: string; profile_photo_url?: string } } }>().props
  const user = auth.user

  const photoInputRef = useRef<HTMLInputElement>(null)
  const [photoPreview, setPhotoPreview] = useState<string | null>(null)

  const { data, setData, post, processing, errors, recentlySuccessful } = useForm<ProfileForm>({
    _method: 'PUT',
    name: user.name,
    email: user.email,
    photo: null,
  })

  const handlePhotoChange = (e: ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0]
    if (!file) return
    setData('photo', file)
    const reader = new FileReader()
    reader.onload = (ev) => setPhotoPreview(ev.target?.result as string)
    reader.readAsDataURL(file)
  }

  const submit = (e: React.FormEvent) => {
    e.preventDefault()
    post(route('user-profile-information.update'), {
      errorBag: 'updateProfileInformation',
      preserveScroll: true,
    })
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle>Profile Information</CardTitle>
        <CardDescription>Update your name, email address, and profile photo.</CardDescription>
      </CardHeader>

      <CardContent>
        <form onSubmit={submit} className="space-y-6">
          {hasProfilePhoto && (
            <div className="space-y-2">
              <Label>Profile Photo</Label>
              <div className="flex items-center gap-4">
                <img
                  src={photoPreview ?? user.profile_photo_url}
                  alt={user.name}
                  className="h-16 w-16 rounded-full object-cover"
                />
                <div className="flex gap-2">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => photoInputRef.current?.click()}
                  >
                    Change Photo
                  </Button>
                </div>
                <input
                  ref={photoInputRef}
                  type="file"
                  accept="image/*"
                  className="hidden"
                  onChange={handlePhotoChange}
                />
              </div>
              <InputError message={errors.photo} />
            </div>
          )}

          <div className="space-y-2">
            <Label htmlFor="name">Name</Label>
            <Input
              id="name"
              value={data.name}
              onChange={(e) => setData('name', e.target.value)}
              autoComplete="name"
            />
            <InputError message={errors.name} />
          </div>

          <div className="space-y-2">
            <Label htmlFor="email">Email</Label>
            <Input
              id="email"
              type="email"
              value={data.email}
              onChange={(e) => setData('email', e.target.value)}
              autoComplete="email"
            />
            <InputError message={errors.email} />
          </div>

          <div className="flex items-center gap-4">
            <Button type="submit" disabled={processing}>Save</Button>
            {recentlySuccessful && (
              <p className="text-sm text-green-600 transition-opacity">Saved.</p>
            )}
          </div>
        </form>
      </CardContent>
    </Card>
  )
}
