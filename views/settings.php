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
	<input type="hidden" name="settings-from-server" id="settingsFromServer" value='<?php echo wp_json_encode(
		get_option(
			'
        annoto_settings'
		)
	); ?>' >
	<div class="col-sm-push-2 col-sm-8">
		<div class="panel panel-primary annoto-panel">
			<div class="panel-heading text-center">
				<img class="annoto-masthead_logo"
					src="<?php echo esc_url( plugins_url( '../src/img/logo_white.svg', __FILE__ ) ); ?>" alt="Annoto" />
			</div>
			<div class="panel-body text-center">
				<form class="form-horizontal" id="settingForm" method="post" action="">
					<div class="row">
						<div class="col-sm-6">
							<div class="panel panel-white panel-right-grey">
								<div class="panel-body panel-settings">

                                    <div class="form-group top-buffer">
                                        <label class="control-label col-lg-3 col-lg-push-1 col-xs-12 setting-label"
                                               for="locale">
                                            Locale
                                        </label>
                                        <div class="col-lg-5 col-xs-5 checkbox">
                                            <input type="checkbox" class="setting-data checkbox-inline" name="locale"
                                                   value="1" />
                                        </div>
                                    </div>

									<div class="form-group top-buffer">
										<label class="control-label col-lg-3 col-lg-push-1 col-xs-12 setting-label"
											for="deploymentDomain">
											Deployment domain
										</label>
										<div class="col-lg-9 col-xs-12">
											<div class="btn-group">
												<button type="button" id="deploymentDomain"
													class="btn btn-default annoto-dropdown-button">
													EU region
												</button>
												<button type="button" class="btn btn-default dropdown-toggle"
													data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
													<span class="caret"></span>
													<span class="sr-only">Toggle Dropdown</span>
												</button>
												<ul class="dropdown-menu" data-btn-id="deploymentDomain">
													<li><a href="#" name="euregion">EU region</a></li>
													<li><a href="#" name="usregion">US region</a></li>
													<li><a href="#" name="custom">Custom</a></li>
												</ul>
											</div>
											<input type="hidden" class="setting-data is-dropdown" name="deploymentDomain"
												value="euregion" />
										</div>
									</div>

								</div>
							</div>
						</div>
						<div class="col-sm-6 border-left" id="credentialBlock">
							<div class="panel panel-white api-key">
								<div class="panel-body">
                                    <p class = "heading">ClientID is provided by Annoto (keep in secret)</p>
									<div class="input-group">
										<span class="input-group-addon annoto-input-group">
											<img src="<?php echo ANNOTO_PLUGIN_URL . 'src/img/key.png'; ?>" alt=""
												class="annoto-icon-image" title="API key" />
										</span>
										<input type="text" class="form-control setting-data" name="api-key"
											placeholder="API Key..." id="api-key"/>
									</div>
								</div>
								<div class="panel-body">
                                    <p class = "heading">SSO secret is provided by Annoto (keep in secret)</p>
									<div class="input-group">
										<span class="input-group-addon annoto-input-group">
											<img src="<?php echo ANNOTO_PLUGIN_URL . 'src/img/lock.png'; ?>" alt=""
												class="annoto-icon-image" title="SSO Secret" />
										</span>
										<input type="text" class="form-control setting-data" name="sso-secret"
											placeholder="SSO Secret..." id="sso-secret"/>
									</div>
								</div>
                                <div class="panel-body">
                                    <p class = "heading">Provide Annoto's script URL here</p>
                                    <div class="input-group">
										<span class="input-group-addon annoto-input-group">
<!--											<img src="--><?php //echo ANNOTO_PLUGIN_URL . 'src/img/lock.png'; ?><!--" alt=""-->
<!--                                                 class="annoto-icon-image" title="Annoto script URL" />-->
										</span>
                                        <input type="text" class="form-control setting-data" name="scripturl"
                                               placeholder="Annoto script URL" id="scripturl"/>
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
