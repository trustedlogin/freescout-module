@section('content')
    <div class="section-heading">
        {{ $section_name }}
    </div>
    <div class="row-container form-container">
        <div class="row">
            <div class="col-xs-12">
                <form class="form-horizontal margin-top" method="POST" action="">
                    {{ csrf_field() }}

                    <div class="form-group">
                        <label for="callbackUrl" class="col-sm-2 control-label">{{ __('Callback URL') }}</label>

                        <div class="col-sm-6">
                            <input type="url" id="callbackUrl" class="form-control input-sized" name="settings[trustedlogin.callbackUrl]" value="{{ old('settings[trustedlogin.callbackUrl]', $settings['trustedlogin.callbackUrl']) }}">
                            <p class="help-block">
                                {{ __('Your application URL to provide license data.') }}
                            </p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="secretKey" class="col-sm-2 control-label">{{ __('Secret Key') }}</label>

                        <div class="col-sm-6">
                            <input type="text" id="secretKey" class="form-control input-sized" name="settings[trustedlogin.secretKey]" value="{{ old('settings[trustedlogin.secretKey]', $settings['trustedlogin.secretKey']) }}">
                            <p class="help-block">
                                {{ __('Key used to generate the hash that\'s validated.') }}
                            </p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="alert_fetch" class="col-sm-2 control-label">{{ __('Debug Mode') }}</label>

                        <div class="col-sm-6">
                            <div class="controls">
                                <div class="onoffswitch-wrap">
                                    <div class="onoffswitch">
                                        <input type="checkbox" name="settings[trustedlogin.debugMode]" value="1" id="debugMode" class="onoffswitch-checkbox" @if (old('settings[trustedlogin.debugMode]', $settings['trustedlogin.debugMode']))checked="checked"@endif >
                                        <label class="onoffswitch-label" for="debugMode"></label>
                                    </div>
                                </div>
                            </div>
                            <p class="help-block">
                                {{ __('Adds extra logging and a request analysis popup.') }}
                            </p>
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
