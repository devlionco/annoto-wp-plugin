<div id="annoto-bootstrap" class="annoto-bootstrap">
    <div class="text-center annoto-notification">
        <div class="alert alert-success text-left display-none" id="successMessage" role="alert">
            <div class="notification-header"><strong>Success!</strong></div>
            <span> Settings was saved successfully!</span>
        </div>
        <div class="alert alert-danger text-left display-none" id="failMessage" role="alert">
            <div class="notification-header"><strong>Failed!</strong></div>
            <span> During saving Settings!</span>
        </div>
        <div class="alert alert-warning text-left display-none" id="errorMessage" role="alert">
            <div class="notification-header"><strong>Warning!</strong></div>
            <span> Error message </span>
        </div>
    </div>
    <input
        type="hidden"
        name="settings-from-server"
        id="settingsFromServer"
        value='<?php echo json_encode(get_option('annoto_settings')); ?>'
    >
    <div class="col-sm-push-2 col-sm-8">
        <div class="panel panel-primary annoto-panel">
            <div class="panel-heading text-center">
                <img
                    class="annoto-masthead_logo"
                    src="<?php echo esc_url( plugins_url( '../src/img/logo_white.svg', __FILE__ ) ); ?>"
                    alt="Annoto"
                />
            </div>
            <div class="panel-body text-center">
                <form class="form-horizontal" id="settingForm" method="post" action="">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="panel panel-white panel-right-grey">
                                <div class="panel-body panel-settings">
                                    <div class="form-group top-buffer">
                                        <label
                                            class="control-label col-lg-3 col-lg-push-1 col-xs-12 setting-label"
                                            for="widget-position"
                                        >
                                            Widget Position
                                        </label>
                                        <div class="col-lg-9 col-xs-12">
                                            <div class="btn-group">
                                                <button
                                                    type="button"
                                                    id="widget-position"
                                                    class="btn btn-default annoto-dropdown-button"
                                                >
                                                    Choose position
                                                </button>
                                                <button
                                                    type="button"
                                                    class="btn btn-default dropdown-toggle"
                                                    data-toggle="dropdown"
                                                    aria-haspopup="true"
                                                    aria-expanded="false"
                                                >
                                                    <span class="caret"></span>
                                                    <span class="sr-only">Toggle Dropdown</span>
                                                </button>
                                                <ul class="dropdown-menu" data-btn-id="widget-position">
                                                    <li><a href="#" name="right">Right</a></li>
                                                    <li><a href="#" name="bottom">Bottom</a></li>
                                                    <li><a href="#" name="left">Left</a></li>
                                                </ul>
                                            </div>
                                            <input
                                                type="hidden"
                                                class="setting-data is-dropdown"
                                                name="widget-position"
                                                value=""
                                            >
                                        </div>
                                    </div>
                                    <div class="form-group top-buffer">
                                        <label
                                            class="control-label col-lg-3 col-lg-push-1 col-xs-12 setting-label"
                                            for="rtl-support"
                                        >
                                            Language
                                        </label>
                                        <div class="col-lg-9 col-xs-12">
                                            <div class="btn-group">
                                                <button
                                                    type="button"
                                                    id="rtl-support"
                                                    class="btn btn-default annoto-dropdown-button"
                                                >
                                                    Choose locale
                                                </button>
                                                <button
                                                    type="button"
                                                    class="btn btn-default dropdown-toggle"
                                                    data-toggle="dropdown"
                                                    aria-haspopup="true"
                                                    aria-expanded="false"
                                                >
                                                    <span class="caret"></span>
                                                    <span class="sr-only">Toggle Dropdown</span>
                                                </button>
                                                <ul class="dropdown-menu" data-btn-id="rtl-support">
                                                    <li><a href="#" name="0">En</a></li>
                                                    <li><a href="#" name="1">He</a></li>
                                                </ul>
                                            </div>
                                            <input
                                                type="hidden"
                                                class="setting-data is-dropdown"
                                                name="rtl-support"
                                                value=""
                                            >
                                        </div>
                                    </div>
                                    <div class="form-group top-buffer">
                                        <label
                                            class="control-label col-lg-3 col-lg-push-1 col-xs-12 setting-label"
                                            for="player-type"
                                        >
                                            Player Type
                                        </label>
                                        <div class="col-lg-9 col-xs-12">
                                            <div class="btn-group">
                                                <button
                                                    type="button"
                                                    id="player-type"
                                                    class="btn btn-default annoto-dropdown-button"
                                                >
                                                    Choose media player
                                                </button>
                                                <button
                                                    type="button"
                                                    class="btn btn-default dropdown-toggle"
                                                    data-toggle="dropdown"
                                                    aria-haspopup="true"
                                                    aria-expanded="false"
                                                >
                                                    <span class="caret"></span>
                                                    <span class="sr-only">Toggle Dropdown</span>
                                                </button>
                                                <ul class="dropdown-menu" data-btn-id="player-type">
                                                    <li><a href="#" name="youtube">YouTube</a></li>
                                                    <li><a href="#" name="vimeo">Vimeo</a></li>
                                                </ul>
                                            </div>
                                            <input
                                                type="hidden"
                                                class="setting-data is-dropdown"
                                                name="player-type"
                                                value=""
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6" id="credentialBlock">
                            <div class="panel panel-white api-key">
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label class="control-label col-sm-3" for="sso-support">SSO Support</label>
                                        <div class="col-sm-9 text-left">
                                            <label class="annoto-switch">
                                                <input
                                                    class="setting-data"
                                                    type="checkbox"
                                                    name="sso-support"
                                                    id="sso-support"
                                                >
                                                <div class="annoto-slider round"></div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-addon annoto-input-group">
                                            <img
                                                src="<?php echo ANNOTO_PLUGIN_URL . 'src/img/lock.png'?>"
                                                alt=""
                                                class="annoto-icon-image"
                                                title="SSO Secret"
                                            >
                                        </span>
                                        <input
                                            type="text"
                                            class="form-control setting-data"
                                            name="sso-secret"
                                            placeholder="SSO Secret..."
                                            id="sso-secret"
                                        />
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label class="control-label col-sm-3" for="demo-mode">Demo Mode</label>
                                        <div class="col-sm-9 text-left">
                                            <label class="annoto-switch">
                                                <input
                                                    type="checkbox"
                                                    class="setting-data"
                                                    name="demo-mode"
                                                    id="demo-mode"
                                                >
                                                <div class="annoto-slider round"></div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-addon annoto-input-group">
                                            <img
                                                src="<?php echo ANNOTO_PLUGIN_URL . 'src/img/key.png'?>"
                                                alt=""
                                                class="annoto-icon-image"
                                                title="API key"
                                            >
                                        </span>
                                        <input
                                            type="text"
                                            class="form-control setting-data"
                                            name="api-key"
                                            placeholder="API Key..."
                                            id="api-key"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group submit-block">
                        <div class="text-center">
                            <button
                                class="btn btn-primary annoto-save"
                                id="submitSettings"
                                type="submit"
                            >
                                Save Settings
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
