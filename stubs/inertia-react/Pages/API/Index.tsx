import { Head } from '@inertiajs/react'
import AppLayout from '@/layouts/app-layout'
import CreateApiTokenForm from '@/components/LaravelStream/API/CreateApiTokenForm'
import ApiTokenList from '@/components/LaravelStream/API/ApiTokenList'

interface Token {
  id: number
  name: string
  abilities: string[]
  last_used_ago: string | null
  created_at: string
}

interface Props {
  tokens: Token[]
  availablePermissions: string[]
  defaultPermissions: string[]
}

export default function Index({ tokens, availablePermissions, defaultPermissions }: Props) {
  return (
    <AppLayout>
      <Head title="API Tokens" />

      <div className="mx-auto max-w-3xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div>
          <h1 className="text-2xl font-semibold tracking-tight">API Tokens</h1>
          <p className="text-muted-foreground text-sm">
            Manage API tokens that allow third-party services to access this application on your behalf.
          </p>
        </div>

        <CreateApiTokenForm
          availablePermissions={availablePermissions}
          defaultPermissions={defaultPermissions}
        />

        {tokens.length > 0 && (
          <ApiTokenList tokens={tokens} availablePermissions={availablePermissions} />
        )}
      </div>
    </AppLayout>
  )
}
