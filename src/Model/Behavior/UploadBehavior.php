<?php
namespace Dala00\Upload\Model\Behavior;

use ArrayObject;
use Cake\Database\Type;
use Cake\ORM\Behavior;
use Cake\ORM\Table;
use Cake\Utility\Hash;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Dala00\Upload\File\FileSystemInterface;
use Dala00\Upload\File\DefaultFileSystem;

/**
 * Upload behavior
 */
class UploadBehavior extends Behavior
{
	/**
     * Save posted $_FILES parameters.
	 *
	 * @var array
     */
	protected $_cache = [];

	/**
     * Save $config on initialize function.
	 * Used for reset.
	 *
	 * @var array
     */
	protected $_initializedConfig = [];

	/**
     * FileSystemInterface.
	 *
	 * @var array
     */
	protected $_fileSystem;

	/**
     * Initialize hook
     *
     * @param array $config The config for this behavior
     * @return void
     */
	public function initialize(array $config)
    {
		$this->_initializedConfig = $config;
        $this->config($config);

		Type::map('upload.file', 'Upload\Database\Type\FileType');
		$schema = $this->_table->schema();
		foreach ($config['fields'] as $field => $settings) {
			$schema->columnType($field, 'upload.file');
		}
		$this->_table->schema($schema);
		$this->fileSystem(new DefaultFileSystem);
    }
	/**
     * Initialize hook
     *
     * @param FileSystemInterface $fileSystem
     * @return void|FileSystemInterface
     */
	public function fileSystem(FileSystemInterface $fileSystem = null) {
		if ($fileSystem === null) {
			return $this->_fileSystem;
		}
		$this->_fileSystem = $fileSystem;
	}

	/**
     * Modifies the data being marshalled to ensure invalid upload data is not inserted
     *
     * @param \Cake\Event\Event $event an event instance
     * @param \ArrayObject $data data being marshalled
     * @param \ArrayObject $options options for the current event
     * @return void
     */
    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
    {
        $validator = $this->_table->validator();
        $dataArray = $data->getArrayCopy();
		$config = $this->config();
        foreach ($config['fields'] as $field => $settings) {
            if (!$validator->isEmptyAllowed($field, false)) {
                continue;
            }
            if (Hash::get($dataArray, $field . '.error') !== UPLOAD_ERR_NO_FILE) {
                continue;
            }
            unset($data[$field]);
        }
    }

	/**
     * Modifies the entity before it is saved so that uploaded file data is persisted
     * in the database too.
     *
     * @param \Cake\Event\Event $event The beforeSave event that was fired
     * @param \Cake\ORM\Entity $entity The entity that is going to be saved
     * @param \ArrayObject $options the options passed to the save method
     * @return void|false
     */
    public function beforeSave(Event $event, Entity $entity, ArrayObject $options)
    {
		$config = $this->config();
        foreach ($config['fields'] as $field => $settings) {
			$data = $entity->get($field);
			if (is_string($data)) {
				continue;
			}

			if (empty($data['tmp_name'])) {
				$entity->set($field, null);
			} else if (is_numeric($data['error']) && !is_int($data['error'])) {
				// Because hidden tag makes error string.
				$data['error'] = (int)$data['error'];
				$entity->set($field, $data);
			}

            if (Hash::get((array)$entity->get($field), 'error') !== UPLOAD_ERR_OK) {
                continue;
            }

            $data = $entity->get($field);
			$this->_cache[$field] = $data;
            $entity->set($field, $data['name']);
        }
    }

	/**
     * Upload file to cache folder if you use confirm page before last submit.
	 * Use UploadHelper->hidden to write hidden field on confirm page.
     *
     * @param \Cake\ORM\Entity $entity The entity that is going to be saved
     * @return void
     */
	public function uploadTmpFile(Entity $entity) {
		$config = $this->config();
		$fileSystem = $this->fileSystem();
		foreach ($config['fields'] as $field => $settings) {
			if (Hash::get((array)$entity->get($field), 'error') !== UPLOAD_ERR_OK) {
				$entity->set($field, null);
                continue;
            }
			$data = $entity->get($field);
			$uniq = session_id() . uniqid();
			$path = $this->getTmpFilePath($uniq);
			$fileSystem->copy($data['tmp_name'], $path);
			$data['cache'] = $uniq;
			$entity->set($field, $data);
		}
	}

	/**
     * Get save path for tmp file.
     *
     * @param string $uniq Unique name for save
     * @return string
     */
	private function getTmpFilePath($uniq) {
		$folder = $this->config('tmpFileFolder');
		if (!$folder) {
			$folder = CACHE . 'upload' . DS;
		}
		if (substr($folder, -1) != DS) {
			$folder .= DS;
		}
		$fileSystem = $this->fileSystem();
		if (!$fileSystem->fileExists($folder)) {
			$fileSystem->mkdir($folder);
		}

		return $folder . 'cake_upload_behavior_' . $uniq;
	}


	/**
     * Save uploaded (or tmp saved) images.
     *
     * @param \Cake\Event\Event $event The beforeSave event that was fired
     * @param \Cake\ORM\Entity $entity The entity that is saved (has primaryKey)
     * @param \ArrayObject $options the options passed to the save method
     * @return void
     */
    public function afterSave(Event $event, Entity $entity, ArrayObject $options)
    {
		$config = $this->config();
		$fileSystem = $this->fileSystem();
        foreach ($config['fields'] as $field => $settings) {
            if (empty($this->_cache[$field]) || Hash::get((array)$this->_cache[$field], 'error') !== UPLOAD_ERR_OK) {
                continue;
            }

            $data = $this->_cache[$field];
			$path = $this->getUploadFolder($entity, $field);
			$fileSystem->mkdir($path, 0777, true);
			$path .= $data['name'];

			if (empty($data['cache'])) {
				// Just upload
				$fileSystem->copy($data['tmp_name'], $path);
			} else {
				// Copy from cache when confirm page is used.
				$src = $this->getTmpFilePath($data['cache']);
				$fileSystem->rename($src, $path);
			}
			$fileSystem->chmod($path, 0666);
        }
    }

	/**
     * Get upload folder.
     *
     * @param \Cake\Event\Event $event The beforeSave event that was fired
     * @param string $field image field name
     * @return string
     */
	public function getUploadFolder(Entity $entity, $field) {
		$config = $this->config();
		$settings = $config['fields'][$field];
		$from = [
			'{DS}',
			'{model}',
			'{primaryKey}',
			'{field}',
		];
		$to = [
			DS,
			$this->_table->alias(),
			$entity->get($this->_table->primaryKey()),
			$field,
		];
		$path = str_replace($from, $to, $settings['path']);
		return ROOT . DS . $path;
	}

	/**
     * Reset parameters to the one initialized.
	 * Use for test.
     *
     * @return void
     */
	public function reset() {
		$this->_cache = [];
		$this->config($this->_initializedConfig);
	}
}
