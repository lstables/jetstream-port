<x-mail::message>
# You've Been Invited!

You have been invited to join the **{{ $teamName }}** team.

<x-mail::button :url="$acceptUrl">
Accept Invitation
</x-mail::button>

If you did not expect to receive an invitation to this team, you may discard this email.

Thanks,
{{ config('app.name') }}
</x-mail::message>
