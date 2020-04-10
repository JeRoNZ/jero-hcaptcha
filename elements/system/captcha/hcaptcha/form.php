<?php use Concrete\Core\Package\PackageService;

defined('C5_EXECUTE') or die('Access denied.');

/** @var \Concrete\Core\Form\Service\Form $form */
$form = Core::make('helper/form');
$pkg = Core::make(PackageService::class)->getByHandle('jero_hcaptcha');
$config = $pkg->getConfig();
?>

<p><?php echo t('A site key and secret are required. They can be obtained from the <a href="%s" target="_blank">hCaptcha website</a>.', 'https://hCaptcha.com/?r=5a1005903b93') ?></p>

<div class="form-group">
	<?php echo $form->label('site_key', t('Site Key')) ?>
	<?php echo $form->text('site_key', $config->get('hcaptcha.site_key', '')) ?>
</div>

<div class="form-group">
	<?php echo $form->label('secret', t('Secret')) ?>
	<?php echo $form->text('secret', $config->get('hcaptcha.secret', '')) ?>
</div>