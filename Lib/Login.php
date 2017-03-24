<?php
namespace Lib;

class Login {
	private function __construct() {
	}

	public static function login($login_url, $cgf) {
		if (empty($cgf)) {
			return false;
		}

		if (!isset($cfg['username']) && !$cgf['username']) {
			return false;
		}

		return httpsPost($login_url, $cgf);
	}

	public static function doLogin($do_logion_url, $data = []) {
		/**
		 * password:"mOyHLimq1U5cPN6/hf1L3wZEJHCpcSUL6W1I7DvuU6by3CoR/MQLvzDw/4QuEoeP+R1A9euYNAajZmBCGE1R1oBoB4bR1OgdNVrct5qZNAIDIyMTRF8rOTZPQxBCGN9QU/75A/hz8SFYgeh2L6uABQS6B7qJsAPaVTxHyeqbykKdoQHh+m3qHOTWQ3kKwUD12GHvBfwbcXQwP5gfdHQrs6NFt3D/tvmEp9/qzSqk/LQQDodelH/VNrCLKu8x03zoj2MBeoPtVa4yGAFOfYZe0np03OniEU3XgnaHVJBlbXlDMheBKZPgYz2c7EMrYb40HgI7nn20ii3l3KqCDqKxsQ=="
		 * username:"ding2210"
		 * twofactorcode:
		 * emailauth:
		 * loginfriendlyname:
		 * captchagid:"-1"
		 * captcha_text:
		 * emailsteamid:
		 * rsatimestamp:"21696900000"
		 * remember_login:"false"
		 */

	}

	//steam的发件人noreply@steampowered.com

	static function is_login() {

	}
}