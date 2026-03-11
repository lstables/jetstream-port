import { useForm } from '@inertiajs/react'
import { useState } from 'react'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Checkbox } from '@/components/ui/checkbox'
import { Label } from '@/components/ui/label'
import {
  AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent,
  AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle } from '@/components/ui/sheet'

interface Token {
  id: number
  name: string
  abilities: string[]
  last_used_ago: string | null
}

interface Props {
  tokens: Token[]
  availablePermissions: string[]
}

export default function ApiTokenList({ tokens, availablePermissions }: Props) {
  const [deleteTarget, setDeleteTarget] = useState<Token | null>(null)
  const [editTarget, setEditTarget] = useState<Token | null>(null)
  const [editPerms, setEditPerms] = useState<string[]>([])

  const { delete: destroy, processing: deleting } = useForm({})
  const { put, processing: saving } = useForm({ permissions: [] as string[] })

  const openEdit = (token: Token) => {
    setEditTarget(token)
    setEditPerms([...token.abilities])
  }

  const toggleEditPerm = (p: string) =>
    setEditPerms(prev => prev.includes(p) ? prev.filter(x => x !== p) : [...prev, p])

  const saveEdit = () => {
    put(route('api-tokens.update', editTarget!.id), {
      data: { permissions: editPerms } as any,
      preserveScroll: true,
      onSuccess: () => setEditTarget(null),
    })
  }

  const deleteToken = () => {
    destroy(route('api-tokens.destroy', deleteTarget!.id), {
      preserveScroll: true,
      onSuccess: () => setDeleteTarget(null),
    })
  }

  return (
    <>
      <Card>
        <CardHeader><CardTitle>Manage Tokens</CardTitle></CardHeader>
        <CardContent className="divide-y">
          {tokens.map((token) => (
            <div key={token.id} className="flex items-center justify-between py-4">
              <div>
                <p className="font-medium">{token.name}</p>
                <p className="text-muted-foreground text-sm">
                  {token.last_used_ago ? `Last used ${token.last_used_ago}` : 'Never used'}
                </p>
              </div>
              <div className="flex gap-2">
                {availablePermissions.length > 0 && (
                  <Button variant="outline" size="sm" onClick={() => openEdit(token)}>
                    Permissions
                  </Button>
                )}
                <Button
                  variant="ghost"
                  size="sm"
                  className="text-destructive"
                  onClick={() => setDeleteTarget(token)}
                >
                  Delete
                </Button>
              </div>
            </div>
          ))}
        </CardContent>
      </Card>

      <Sheet open={!!editTarget} onOpenChange={(v) => !v && setEditTarget(null)}>
        <SheetContent>
          <SheetHeader>
            <SheetTitle>Token Permissions</SheetTitle>
            <SheetDescription>Update permissions for "{editTarget?.name}"</SheetDescription>
          </SheetHeader>
          <div className="my-6 space-y-3">
            {availablePermissions.map((p) => (
              <div key={p} className="flex items-center space-x-2">
                <Checkbox
                  id={`edit-${p}`}
                  checked={editPerms.includes(p)}
                  onCheckedChange={() => toggleEditPerm(p)}
                />
                <Label htmlFor={`edit-${p}`} className="cursor-pointer font-normal capitalize">{p}</Label>
              </div>
            ))}
          </div>
          <Button onClick={saveEdit} disabled={saving}>Save</Button>
        </SheetContent>
      </Sheet>

      <AlertDialog open={!!deleteTarget} onOpenChange={(v) => !v && setDeleteTarget(null)}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Delete Token</AlertDialogTitle>
            <AlertDialogDescription>
              Are you sure you want to delete "{deleteTarget?.name}"? This cannot be undone.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel onClick={() => setDeleteTarget(null)}>Cancel</AlertDialogCancel>
            <AlertDialogAction
              className="bg-destructive text-destructive-foreground"
              onClick={deleteToken}
              disabled={deleting}
            >
              Delete
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </>
  )
}
