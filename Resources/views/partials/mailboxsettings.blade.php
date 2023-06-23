@if (Auth::user()->isAdmin() || Auth::user()->hasManageMailboxPermission($mailbox->id, App\Mailbox::ACCESS_PERM_PERMISSIONS))
    <li @if (Route::currentRouteName() == 'mailboxes.trustedlogin')class="active"@endif><a href="{{ route('mailboxes.trustedlogin', ['id'=>$mailbox->id]) }}"><i class="glyphicon glyphicon-lock"></i> {{ __('TrustedLogin') }}</a></li>
@endif
