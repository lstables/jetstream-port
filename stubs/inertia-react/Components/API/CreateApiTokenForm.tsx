import { useForm, usePage } from '@inertiajs/react'
import { useEffect, useState } from 'react'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Checkbox } from '@/components/ui/checkbox'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle,
} from '@/components/ui/dialog'
import InputError from '@/components/InputError'

interface Props {
  availablePermissions: string[]
  defaultPermissions: string[]
}

export default function CreateApiTokenForm({ availablePermissions, defaultPermissions }: Props) {
  const { flash } = usePage<{ flash: { token?: string } }>().props
  const [displayToken, setDisplayToken] = useState('')
  const [tokenDialogOpen, setTokenDialogOpen] = useState(false)

  const { data, setData, post, processing, errors, reset } = useForm({
    name: '',
    permissions: [...defaultPermissions],
  })

  useEffect(() => {
    if (flash?.token) {
      setDisplayToken(flash.token)
      setTokenDialogOpen(true)
      reset()
    }
  }, [flash])

  const togglePermission = (permission: string) => {
    setData('permissions', data.permissions.includes(permission)
      ? data.permissions.filter(p => p !== permission)
      : [...data.permissions, permission]
    )
  }

  const submit = (e: React.FormEvent) => {
    e.preventDefault()
    post(route('api-tokens.store'), { preserveScroll: true })
  }

  return (
    <>
      <Card>
        <CardHeader>
          <CardTitle>Create API Token</CardTitle>
          <CardDescription>API tokens allow third-party services to authenticate with this application.</CardDescription>
        </CardHeader>
        <CardContent>
          <form onSubmit={submit} className="space-y-6">
            <div className="space-y-2">
              <Label htmlFor="token-name">Token Name</Label>
              <Input
                id="token-name"
                value={data.name}
                onChange={(e) => setData('name', e.target.value)}
                placeholder="My App Token"
              />
              <InputError message={errors.name} />
            </div>

            {availablePermissions.length > 0 && (
              <div className="space-y-2">
                <Label>Permissions</Label>
                <div className="grid grid-cols-2 gap-2">
                  {availablePermissions.map((permission) => (
                    <div key={permission} className="flex items-center space-x-2">
                      <Checkbox
                        id={permission}
                        checked={data.permissions.includes(permission)}
                        onCheckedChange={() => togglePermission(permission)}
                      />
                      <Label htmlFor={permission} className="cursor-pointer font-normal capitalize">
                        {permission}
                      </Label>
                    </div>
                  ))}
                </div>
                <InputError message={errors.permissions} />
              </div>
            )}

            <Button type="submit" disabled={processing}>Create Token</Button>
          </form>
        </CardContent>
      </Card>

      <Dialog open={tokenDialogOpen} onOpenChange={setTokenDialogOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>API Token Created</DialogTitle>
            <DialogDescription>
              Copy your new API token now. For your security, it will not be shown again.
            </DialogDescription>
          </DialogHeader>
          <div className="bg-muted my-4 rounded-md p-4">
            <p className="break-all select-all font-mono text-sm">{displayToken}</p>
          </div>
          <Button onClick={() => setTokenDialogOpen(false)}>Close</Button>
        </DialogContent>
      </Dialog>
    </>
  )
}
