@extends('layouts.app')

@section('title_full', __('TrustedLogin Mailbox Settings').' - '.$mailbox->name)

@section('sidebar')
    @include('partials/sidebar_menu_toggle')
    @include('mailboxes/sidebar_menu')
@endsection

@section('content')

    <div class="section-heading">
        {{ __('TrustedLogin') }}
    </div>

    <div class="row-container">
        <div class="row">
            <div class="col-xs-12">
                <form class="form-horizontal margin-top" method="POST" action="">
                    {{ csrf_field() }}

                    <div class="form-group">
                        <label for="trustedlogin_enabled" class="col-sm-2 control-label">{{ __('Enable TrustedLogin') }}</label>

                        <div class="col-sm-6">
                            <div class="controls">
                                <div class="onoffswitch-wrap">
                                    <div class="onoffswitch">
                                        <input type="checkbox" name="trustedlogin_enabled" value="1" id="trustedlogin_enabled" class="onoffswitch-checkbox" @if (old('trustedloginMailboxEnabled', $trustedloginMailboxEnabled))checked="checked"@endif >
                                        <label class="onoffswitch-label" for="trustedlogin_enabled"></label>
                                    </div>

                                    <i class="glyphicon glyphicon-info-sign icon-info icon-info-inline" data-toggle="popover" data-trigger="hover" data-html="true" data-placement="left" data-title="{{ __('TrustedLogin Enabled') }}" data-content="{{ __('If TrustedLogin is Enabled on this mailbox customer data will be pulled from the TrustedLogin service.') }}"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-6 col-sm-offset-2">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Save') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
