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
    <input type="hidden" name="settings-from-server" id="settingsFromServer" value="{&quot;widget-position&quot;:&quot;right&quot;,&quot;rtl-support&quot;:1,&quot;player-type&quot;:&quot;vimeo&quot;,&quot;demo-mode&quot;:1,&quot;api-key&quot;:&quot;&quot;,&quot;sso-support&quot;:0,&quot;sso-secret&quot;:&quot;&quot;}">
    <div class="col-sm-push-2 col-sm-8">
        <div class="panel panel-primary annoto-panel">
            <div class="panel-heading text-center">
                <img class="annoto-masthead_logo" src="http://wordpressdev.com/wp-content/plugins/annoto-wp-plugin/views/../src/img/logo_white.svg" alt="Annoto">
            </div>
            <div class="panel-body text-center">
                <form class="form-horizontal" id="settingForm" method="post" action="">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="panel panel-white panel-right-grey">
                                <div class="panel-body panel-settings">
                                    <div class="form-group top-buffer">
                                        <label class="control-label col-lg-3 col-lg-push-1 col-xs-12 setting-label" for="widget-position">
                                            Widget Position
                                        </label>
                                        <div class="col-lg-9 col-xs-12">
                                            <div class="btn-group">
                                                <button type="button" id="widget-position" class="btn btn-default annoto-dropdown-button">Right</button>
                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <span class="caret"></span>
                                                    <span class="sr-only">Toggle Dropdown</span>
                                                </button>
                                                <ul class="dropdown-menu" data-btn-id="widget-position">
                                                    <li><a href="#" name="right">Right</a></li>
                                                    <li><a href="#" name="bottom">Bottom</a></li>
                                                    <li><a href="#" name="left">Left</a></li>
                                                </ul>
                                            </div>
                                            <input type="hidden" class="setting-data is-dropdown" name="widget-position" value="right">
                                        </div>
                                    </div>
                                    <div class="form-group top-buffer">
                                        <label class="control-label col-lg-3 col-lg-push-1 col-xs-12 setting-label" for="rtl-support">
                                            Language
                                        </label>
                                        <div class="col-lg-9 col-xs-12">
                                            <div class="btn-group">
                                                <button type="button" id="rtl-support" class="btn btn-default annoto-dropdown-button">He</button>
                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <span class="caret"></span>
                                                    <span class="sr-only">Toggle Dropdown</span>
                                                </button>
                                                <ul class="dropdown-menu" data-btn-id="rtl-support">
                                                    <li><a href="#" name="0">En</a></li>
                                                    <li><a href="#" name="1">He</a></li>
                                                </ul>
                                            </div>
                                            <input type="hidden" class="setting-data is-dropdown" name="rtl-support" value="1">
                                        </div>
                                    </div>
                                  
				    <div class="form-group top-buffer">
                                        <label class="control-label col-lg-3 col-lg-push-1 col-xs-12 setting-label" for="player-type">
                                            Player Type
                                        </label>
                                        <div class="col-lg-9 col-xs-12">
                                            <div class="btn-group">
                                                <button type="button" id="player-type" class="btn btn-default annoto-dropdown-button">Vimeo</button>
                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <span class="caret"></span>
                                                    <span class="sr-only">Toggle Dropdown</span>
                                                </button>
                                                <ul class="dropdown-menu" data-btn-id="player-type">
                                                    <li><a href="#" name="youtube">YouTube</a></li>
                                                    <li><a href="#" name="vimeo">Vimeo</a></li>
                                                </ul>
                                            </div>
                                            <input type="hidden" class="setting-data is-dropdown" name="player-type" value="vimeo">
                                        </div>
										
                                    </div>
					
									
									  <div class="checkbox annoto-advance-player-params" style="margin-left">
      <label class="col-sm-7"><input type="checkbox" id="vimeo-premium-player" class="setting-data" for="vimeo-premium-player">Premium player</label>
  </div>
<div class="form-group top-buffer" style="display: block;">
									     <div class="panel-body panel-settings">
                                    <div class="form-group top-buffer">
                                        <label class="control-label col-lg-3 col-lg-push-1 col-xs-12 setting-label" for="demo-mode">Advanced Setting</label>
                                        <div class="col-lg-9 col-xs-12">
                                            <label class="annoto-switch">
                                                <input type="checkbox" name="annoto-advanced-settings-switch" id="annoto-advanced-settings-switch">
                                                <div class="annoto-slider round"></div>
                                            </label>
                                        </div>									
									</div><div class="form-group top-buffer annoto-advanced-settings-container" style="display: block;">
                                        <label class="control-label col-lg-3 col-lg-push-1 col-xs-12 setting-label" for="widget-align-vertical">
                                            Vertical alignment
                                        </label>
                                        <div class="col-lg-9 col-xs-12">
                                            <div class="btn-group">
                                                <button type="button" id="widget-align-vertical" class="btn btn-default annoto-dropdown-button">Center</button>
                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <span class="caret"></span>
                                                    <span class="sr-only">Toggle Dropdown</span>
                                                </button>
                                                <ul class="dropdown-menu" data-btn-id="widget-align-vertical">
                                                    <li><a href="#" name="center">Center</a></li>
                                                    <li><a href="#" name="bottom">Bottom</a></li>
                                                    <li><a href="#" name="top">Top</a></li>
                                                </ul>
                                            </div>
                                            <input type="hidden" class="setting-data" name="widget-align-vertical" value="">
                                        </div>
                                    </div>
                                    <div class="form-group top-buffer annoto-advanced-settings-container" style="display: block;">
                                        <label class="control-label col-lg-3 col-lg-push-1 col-xs-12 setting-label" for="widget-align-horizontal">
                                            Horizontal alignment
                                        </label>
                                        <div class="col-lg-9 col-xs-12">
                                            <div class="btn-group">
                                                <button type="button" id="widget-align-horizontal" class="btn btn-default annoto-dropdown-button">Center</button>
                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <span class="caret"></span>
                                                    <span class="sr-only">Toggle Dropdown</span>
                                                </button>
                                                <ul class="dropdown-menu" data-btn-id="widget-align-horizontal">
                                                    <li><a href="#" name="screen_edge">Screen Edge</a></li>
                                                    <li><a href="#" name="element_edge">Element edge</a></li>
  						    <li><a href="#" name="center">Center</a></li>
                                                </ul>
                                            </div>
                                            <input type="hidden" class="setting-data" name="widget-align-horizontal" value="">
                                        </div>
                                    </div>
                                    <div class="form-group top-buffer annoto-advanced-settings-container" style="display: block;">
                                        <label class="control-label col-lg-3 col-lg-push-1 col-xs-12 setting-label" for="widget-width">
                                            Widget width
                                        </label>
                                        <div class="col-lg-9 col-xs-12">
					  <input type="number" name="widget-width" value="300">
					  <input type="hidden" class="setting-data" name="widget-width" value="">    
                                        </div>
                                    </div>
					
                                </div>
									</div>
									
								</div>
                            </div>
                        </div>
                        <div class="col-sm-6" id="credentialBlock">
                            <div class="panel panel-white api-key">
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label class="control-label col-sm-3" for="demo-mode">Demo Mode</label>
                                        <div class="col-sm-9 text-left">
                                            <label class="annoto-switch">
                                                <input type="checkbox" class="setting-data" name="demo-mode" id="demo-mode" value="1">
                                                <div class="annoto-slider round"></div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-addon annoto-input-group">
                                            <img src="http://wordpressdev.com/wp-content/plugins/annoto-wp-plugin/src/img/key.png" alt="" class="annoto-icon-image" title="API key">
                                        </span>
                                        <input type="text" class="form-control setting-data" name="api-key" placeholder="API Key..." id="api-key" disabled="">
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label class="control-label col-sm-3" for="sso-support">SSO Support</label>
                                        <div class="col-sm-9 text-left">
                                            <label class="annoto-switch">
                                                <input class="setting-data" type="checkbox" name="sso-support" id="sso-support" value="0">
                                                <div class="annoto-slider round"></div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-addon annoto-input-group">
                                            <img src="http://wordpressdev.com/wp-content/plugins/annoto-wp-plugin/src/img/lock.png" alt="" class="annoto-icon-image" title="SSO Secret">
                                        </span>
                                        <input type="text" class="form-control setting-data" name="sso-secret" placeholder="SSO Secret..." id="sso-secret" disabled="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group submit-block">
                        <div class="text-center">
                            <button class="btn btn-primary annoto-save" id="submitSettings" type="submit">
                                Save Settings
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
