<?php

namespace TeamStream\Actions\Auth;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use TeamStream\Contracts\UpdatesUserProfileInformation;
use TeamStream\TeamStream;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    public function update(mixed $user, array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => TeamStream::hasProfilePhotoFeature()
                ? ['nullable', 'mimes:jpg,jpeg,png', 'max:1024']
                : [],
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo']) && TeamStream::hasProfilePhotoFeature()) {
            $user->updateProfilePhoto($input['photo']);
        }

        if ($input['email'] !== $user->email && $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'name' => $input['name'],
                'email' => $input['email'],
            ])->save();
        }
    }

    protected function updateVerifiedUser(mixed $user, array $input): void
    {
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
