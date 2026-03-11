import { Head } from '@inertiajs/react'
import AppLayout from '@/layouts/app-layout'
import UpdateProfileInformationForm from '@/components/LaravelStream/Profile/UpdateProfileInformationForm'
import UpdatePasswordForm from '@/components/LaravelStream/Profile/UpdatePasswordForm'
import TwoFactorAuthenticationForm from '@/components/LaravelStream/Profile/TwoFactorAuthenticationForm'
import DeleteUserForm from '@/components/LaravelStream/Profile/DeleteUserForm'

interface Props {
  sessions: object[]
  hasProfilePhoto: boolean
  hasTwoFactor: boolean
  hasAccountDeletion: boolean
  twoFactorEnabled: boolean
  twoFactorPending: boolean
}

export default function Show({
  hasProfilePhoto,
  hasTwoFactor,
  hasAccountDeletion,
  twoFactorEnabled,
  twoFactorPending,
}: Props) {
  return (
    <AppLayout>
      <Head title="Profile" />

      <div className="mx-auto max-w-3xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div>
          <h1 className="text-2xl font-semibold tracking-tight">Profile</h1>
          <p className="text-muted-foreground text-sm">
            Manage your account settings and profile information.
          </p>
        </div>

        <UpdateProfileInformationForm hasProfilePhoto={hasProfilePhoto} />

        <UpdatePasswordForm />

        {hasTwoFactor && (
          <TwoFactorAuthenticationForm
            enabled={twoFactorEnabled}
            pending={twoFactorPending}
          />
        )}

        {hasAccountDeletion && <DeleteUserForm />}
      </div>
    </AppLayout>
  )
}
