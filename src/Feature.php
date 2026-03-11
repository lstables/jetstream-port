<?php

namespace TeamStream;

enum Feature: string
{
    case Teams = 'teams';
    case Api = 'api';
    case ProfilePhotos = 'profile-photos';
    case AccountDeletion = 'account-deletion';
    case TwoFactorAuthentication = 'two-factor-authentication';
    case EmailVerification = 'email-verification';
    case TeamInvitations = 'team-invitations';
}
