<?php

namespace Concrete\Package\JeroHcaptcha\Captcha;

use Concrete\Core\Captcha\CaptchaInterface;
use Concrete\Core\Controller\AbstractController;
use Concrete\Core\Http\Client\Client as HttpClient;
use Concrete\Core\Logging\Channels;
use Concrete\Core\Logging\LoggerAwareInterface;
use Concrete\Core\Logging\LoggerAwareTrait;
use Concrete\Core\Package\PackageService;
use Config;
use Core;


class HcaptchaController extends AbstractController implements CaptchaInterface, LoggerAwareInterface
{
	use LoggerAwareTrait;

    public function saveOptions($data) {
		$pkg = Core::make(PackageService::class)->getByHandle('jero_hcaptcha');
		$config = $pkg->getConfig();
        $config->save('hcaptcha.site_key', $data['site_key']);
        $config->save('hcaptcha.secret', $data['secret']);
    }

    /**
     * Shows an input for a particular captcha library
     */
    function showInput() {
		$pkg = $this->app->make(PackageService::class)->getByHandle('jero_hcaptcha');
		$config = $pkg->getConfig();
		$assetJS = '<script type="text/javascript" src="https://www.hcaptcha.com/1/api.js" async defer></script>';
		$this->addHeaderItem($assetJS);

		?>
        <div class="h-captcha" data-sitekey="<?= $config->get('hcaptcha.site_key', '')?>"></div>
		<?php

	}

    /**
     * Displays the graphical portion of the captcha
     */
    function display() {
        return '';
    }

    /**
     * Displays the label for this captcha library
     */
    function label() {
        return '';
    }

    /**
     * Verifies the captcha submission
     * @return bool
     */
    public function check() {
		$pkg = Core::make(PackageService::class)->getByHandle('jero_hcaptcha');
        $config = $pkg->getConfig();

        $response = $this->request->get('h-captcha-response');
		/** @var \Concrete\Core\Permission\IPService $iph */
		$iph = Core::make('helper/validation/ip');

		$ip = $iph->getRequestIPAddress();

		if (!empty($response)) {
			$secret = $config->get('hcaptcha.secret', '');

			$queryString = http_build_query(
				[
					'secret' => $secret,
					'remoteip' => $ip,
					'response' => $response,
				]
			);

			$verifyUrl = 'https://hcaptcha.com/siteverify?'.$queryString;

			$httpClient = $this->app->make(HttpClient::class);
			/* @var $httpClient \Concrete\Core\Http\Client\Client */
			$httpClient->setUri($verifyUrl);
			$httpClient->setMethod('GET');

			try {
				$response = $httpClient->send();
			} catch (\Exception $x) {
				$this->logger->alert(t('Error loading hCaptcha: %s', $x->getMessage()));

				return false;
			}
			/** @var \Zend\Http\Response $response */
			if (!$response->isOk()) {
				$this->logger->alert(t('Error loading hCaptcha: %s', sprintf('%s (%s)', $response->getStatusCode(), $response->getReasonPhrase())));

				return false;
			}

			$body = $response->getBody();

// Old school, but as documented
//			$body = @file_get_contents('https://hcaptcha.com/siteverify?secret='.$secret.'&response='.$_POST['h-captcha-response'].'&remoteip='.$_SERVER['REMOTE_ADDR']);


			$responseData = @json_decode($body);
			if (!$responseData) {
				$this->logger->alert(t('Error decoding hCaptcha response: %s', $body));

				return false;
            }

			if ($responseData->success === true) {
				return true;

			}

			$this->logger->notice(t('User failed hCaptcha from IP %s, response was %s', $ip, $body));

			return false;
		}

		$this->logger->alert(t('Empty hCaptcha response received'));

		return false;

    }

	/**
	 * {@inheritdoc}
	 *
	 * @see \Concrete\Core\Logging\LoggerAwareInterface::getLoggerChannel()
	 */
	public function getLoggerChannel()
	{
		return Channels::CHANNEL_SPAM;
	}
}