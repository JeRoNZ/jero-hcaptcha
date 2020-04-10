<?php

namespace Concrete\Package\JeroHcaptcha;

use Package;
use Concrete\Core\Captcha\Library as CaptchaLibrary;


class Controller extends Package {
	protected $pkgHandle = 'jero_hcaptcha';
	protected $appVersionRequired = '8.5.2';
	protected $pkgVersion = '0.1';

	protected $logger;

	protected $pkgAutoloaderRegistries = [
		'src/Captcha' => '\Concrete\Package\JeroHcaptcha\Captcha'
	];

	public function getPackageName () {
		return t('JeRo hCaptcha');
	}

	public function getPackageDescription () {
		return t('Provides an hCaptcha field.');
	}

	public function install () {
		$pkg = parent::install();
		CaptchaLibrary::add('hcaptcha', t('hCaptcha'), $pkg);
	}
}