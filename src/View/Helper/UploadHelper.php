<?php
namespace Dala00\Upload\View\Helper;

use Cake\View\Helper;
use Cake\View\View;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

/**
 * UploadConfirm helper
 */
class UploadHelper extends Helper
{
	public $helpers = ['Form', 'Url', 'Html'];

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];

	/**
     * Get uploaded image URL.
	 * Return wrong URL if path config is not under webroot.
	 * UrlHelper::build generates URL.
     *
	 * @param \Cake\ORM\Entity $entity The entity
     * @param string $field The image field name
     * @return string
     */
	public function url(Entity $entity, $field) {
		$table = TableRegistry::get($entity->source());
		$folder = $table->getUploadFolder($entity, $field);
		$path = str_replace(WWW_ROOT, '', $folder);
		$path .= $entity->get($field);
		return $this->Url->build("/$path");
	}

	/**
     * Get img tag for uploaded image URL.
	 * Return wrong URL if path config is not under webroot.
	 * HtmlHelper::image generates tag.
     *
	 * @param \Cake\ORM\Entity $entity The entity
     * @param string $field The image field name
	 * @param array $options The options for HtmlHelper::image
     * @return string
     */
	public function image(Entity $entity, $field, array $options = []) {
		$url = $this->url($entity, $field);
		return $this->Html->image($url, $options);
	}

	/**
     * Write hidden tag for confirm page.
     *
	 * @param \Cake\ORM\Entity $entity The entity that is patched
     * @param string $field The image field name
     * @return string
     */
	public function hidden(Entity $entity, $field) {
		$value = $entity->get($field);
		if (empty($value['tmp_name']) || $value === null) {
			return '';
		}
		if (!is_array($value)) {
			return $this->Form->hidden($field);
		}

		$tag = '';
		foreach ($value as $key => $subValue) {
			$tag .= $this->Form->hidden("$field.$key");
		}
		return $tag;
	}
}
